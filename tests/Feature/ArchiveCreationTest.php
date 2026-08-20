<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Archive;
use App\Models\Category;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;
    private User $viewer;
    private Category $category;
    private Department $department;
    private DocumentType $docType;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('archives');

        $this->department = Department::create(['name' => 'Tata Usaha', 'code' => 'TU', 'is_active' => true]);
        $this->category   = Category::create(['name' => 'Administrasi', 'code' => 'ADM', 'is_active' => true]);
        $this->docType    = DocumentType::create(['name' => 'PDF', 'code' => 'PDF', 'is_active' => true]);

        $this->admin    = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR, 'is_active' => true]);
        $this->viewer   = User::factory()->create(['role' => UserRole::VIEWER, 'is_active' => true]);
    }

    public function test_admin_and_operator_can_view_create_archive_form(): void
    {
        $response = $this->actingAs($this->admin)->get('/archives/create');
        $response->assertStatus(200);

        $response = $this->actingAs($this->operator)->get('/archives/create');
        $response->assertStatus(200);
    }

    public function test_viewer_cannot_access_create_archive_form(): void
    {
        $response = $this->actingAs($this->viewer)->get('/archives/create');
        $response->assertStatus(403);
    }

    public function test_admin_can_create_archive_with_valid_file_and_metadata(): void
    {
        $file = UploadedFile::fake()->create('SK_Kepala.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->post('/archives', [
            'document_number' => '800/01/2026',
            'title'           => 'SK Pembagian Tugas Guru',
            'category_id'     => $this->category->id,
            'department_id'   => $this->department->id,
            'document_date'   => '2026-08-20',
            'year'            => 2026,
            'document_type'   => 'pdf',
            'keywords'        => 'sk, pembagian tugas, guru',
            'description'     => 'SK Kepala tentang pembagian tugas mengajar.',
            'file'            => $file,
        ]);

        $archive = Archive::where('title', 'SK Pembagian Tugas Guru')->first();
        $this->assertNotNull($archive);

        $response->assertRedirect('/archives/' . $archive->id);
        $response->assertSessionHas('success');

        $this->assertEquals('ARSIP-2026-000001', $archive->archive_number);
        $this->assertEquals('SK_Kepala.pdf', $archive->original_filename);
        $this->assertEquals($this->admin->id, $archive->uploaded_by);

        // Verify stored in fake archives disk
        Storage::disk('archives')->assertExists($archive->file_path);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'archive_create',
            'entity_id' => $archive->id,
        ]);
    }

    public function test_archive_number_increments_sequentially(): void
    {
        $file1 = UploadedFile::fake()->create('doc1.pdf', 100);
        $file2 = UploadedFile::fake()->create('doc2.pdf', 100);

        $this->actingAs($this->admin)->post('/archives', [
            'title'         => 'Doc 1',
            'category_id'   => $this->category->id,
            'department_id' => $this->department->id,
            'document_date' => '2026-08-20',
            'year'          => 2026,
            'document_type' => 'pdf',
            'file'          => $file1,
        ]);

        $this->actingAs($this->admin)->post('/archives', [
            'title'         => 'Doc 2',
            'category_id'   => $this->category->id,
            'department_id' => $this->department->id,
            'document_date' => '2026-08-20',
            'year'          => 2026,
            'document_type' => 'pdf',
            'file'          => $file2,
        ]);

        $arc1 = Archive::where('title', 'Doc 1')->first();
        $arc2 = Archive::where('title', 'Doc 2')->first();

        $this->assertEquals('ARSIP-2026-000001', $arc1->archive_number);
        $this->assertEquals('ARSIP-2026-000002', $arc2->archive_number);
    }

    public function test_creation_fails_with_inactive_category(): void
    {
        $inactiveCat = Category::create(['name' => 'Inaktif', 'code' => 'INA', 'is_active' => false]);
        $file = UploadedFile::fake()->create('doc.pdf', 100);

        $response = $this->actingAs($this->admin)->post('/archives', [
            'title'         => 'Doc Inactive Cat',
            'category_id'   => $inactiveCat->id,
            'department_id' => $this->department->id,
            'document_date' => '2026-08-20',
            'year'          => 2026,
            'document_type' => 'pdf',
            'file'          => $file,
        ]);

        $response->assertSessionHas('error', 'Gagal menyimpan arsip: Kategori yang dipilih tidak aktif.');
    }

    public function test_creation_fails_for_executable_file(): void
    {
        $dangerousFile = UploadedFile::fake()->create('script.php', 100, 'text/x-php');

        $response = $this->actingAs($this->admin)->post('/archives', [
            'title'         => 'Malicious File',
            'category_id'   => $this->category->id,
            'department_id' => $this->department->id,
            'document_date' => '2026-08-20',
            'year'          => 2026,
            'document_type' => 'pdf',
            'file'          => $dangerousFile,
        ]);

        $response->assertSessionHasErrors('file');
    }
}
