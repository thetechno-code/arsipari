<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Archive;
use App\Models\ArchiveVersion;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveVersioningTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;
    private User $viewer;
    private Category $category;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('archives');

        $this->department = Department::create(['name' => 'TU', 'code' => 'TU']);
        $this->category   = Category::create(['name' => 'ADM', 'code' => 'ADM']);

        $this->admin    = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR, 'is_active' => true]);
        $this->viewer   = User::factory()->create(['role' => UserRole::VIEWER, 'is_active' => true]);
    }

    public function test_creating_archive_automatically_creates_version_1(): void
    {
        $file = UploadedFile::fake()->create('SK_Awal.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->admin)->post('/archives', [
            'title'         => 'SK Kepala Versi 1',
            'category_id'   => $this->category->id,
            'department_id' => $this->department->id,
            'document_date' => '2026-08-20',
            'year'          => 2026,
            'document_type' => 'pdf',
            'file'          => $file,
        ]);

        $archive = Archive::where('title', 'SK Kepala Versi 1')->first();
        $this->assertNotNull($archive);

        $this->assertDatabaseHas('archive_versions', [
            'archive_id'     => $archive->id,
            'version_number' => 1,
            'original_filename' => 'SK_Awal.pdf',
        ]);

        $this->assertEquals(1, $archive->versions()->count());
        $this->assertEquals('v1', $archive->currentVersion->version_label);
    }

    public function test_uploading_new_file_creates_version_2_and_preserves_version_1(): void
    {
        $file1 = UploadedFile::fake()->create('SK_v1.pdf', 300);
        $this->actingAs($this->admin)->post('/archives', [
            'title'         => 'SK Multi Versi',
            'category_id'   => $this->category->id,
            'department_id' => $this->department->id,
            'document_date' => '2026-08-20',
            'year'          => 2026,
            'document_type' => 'pdf',
            'file'          => $file1,
        ]);

        $archive = Archive::where('title', 'SK Multi Versi')->first();
        $v1Path  = $archive->currentVersion->file_path;

        // Upload version 2
        $file2 = UploadedFile::fake()->create('SK_v2_Revisi.pdf', 500);
        $response = $this->actingAs($this->operator)->post("/archives/{$archive->id}/versions", [
            'file'        => $file2,
            'change_note' => 'Revisi halaman 2',
        ]);

        $response->assertRedirect("/archives/{$archive->id}");

        $archive->refresh();
        $this->assertEquals(2, $archive->versions()->count());
        $this->assertEquals('v2', $archive->currentVersion->version_label);
        $this->assertEquals('SK_v2_Revisi.pdf', $archive->currentVersion->original_filename);

        // Both physical files must exist in private storage
        Storage::disk('archives')->assertExists($v1Path);
        Storage::disk('archives')->assertExists($archive->currentVersion->file_path);
    }

    public function test_admin_can_restore_older_version_as_new_version(): void
    {
        $file1 = UploadedFile::fake()->create('v1.pdf', 100);
        $file2 = UploadedFile::fake()->create('v2.pdf', 200);

        $this->actingAs($this->admin)->post('/archives', [
            'title'         => 'SK Restore Test',
            'category_id'   => $this->category->id,
            'department_id' => $this->department->id,
            'document_date' => '2026-08-20',
            'year'          => 2026,
            'document_type' => 'pdf',
            'file'          => $file1,
        ]);

        $archive = Archive::where('title', 'SK Restore Test')->first();

        // Upload v2
        $this->actingAs($this->admin)->post("/archives/{$archive->id}/versions", ['file' => $file2]);

        $v1 = ArchiveVersion::where('archive_id', $archive->id)->where('version_number', 1)->first();

        // Restore v1 -> creates v3
        $response = $this->actingAs($this->admin)->put("/archives/{$archive->id}/versions/{$v1->id}/restore");

        $response->assertRedirect("/archives/{$archive->id}");

        $archive->refresh();
        $this->assertEquals(3, $archive->versions()->count());
        $this->assertEquals('v3', $archive->currentVersion->version_label);
        $this->assertEquals('v1.pdf', $archive->currentVersion->original_filename);
        $this->assertStringContainsString('Dipulihkan dari versi 1', $archive->currentVersion->change_note);
    }

    public function test_version_download_checks_idor_protection(): void
    {
        $file1 = UploadedFile::fake()->create('v1.pdf', 100);
        $this->actingAs($this->admin)->post('/archives', [
            'title'         => 'Doc A',
            'category_id'   => $this->category->id,
            'department_id' => $this->department->id,
            'document_date' => '2026-08-20',
            'year'          => 2026,
            'document_type' => 'pdf',
            'file'          => $file1,
        ]);

        $archiveA = Archive::where('title', 'Doc A')->first();
        $v1A = $archiveA->currentVersion;

        $file2 = UploadedFile::fake()->create('v2.pdf', 100);
        $this->actingAs($this->admin)->post('/archives', [
            'title'         => 'Doc B',
            'category_id'   => $this->category->id,
            'department_id' => $this->department->id,
            'document_date' => '2026-08-20',
            'year'          => 2026,
            'document_type' => 'pdf',
            'file'          => $file2,
        ]);

        $archiveB = Archive::where('title', 'Doc B')->first();

        // Trying to download v1A using archiveB's URL must return 404
        $response = $this->actingAs($this->viewer)->get("/archives/{$archiveB->id}/versions/{$v1A->id}/download");
        $response->assertStatus(404);
    }
}
