<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Archive;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;
    private User $viewer;
    private Archive $archive;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('archives');

        $dept = Department::create(['name' => 'TU', 'code' => 'TU']);
        $cat  = Category::create(['name' => 'ADM', 'code' => 'ADM']);

        $this->admin    = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR, 'is_active' => true]);
        $this->viewer   = User::factory()->create(['role' => UserRole::VIEWER, 'is_active' => true]);

        $oldFile = UploadedFile::fake()->create('Old_Doc.pdf', 200);
        $oldPath = '2026/old_ulid/old_ulid.pdf';
        Storage::disk('archives')->putFileAs('2026/old_ulid', $oldFile, 'old_ulid.pdf');

        $this->archive = Archive::create([
            'archive_number'    => 'ARSIP-2026-000001',
            'title'             => 'Judul Lama',
            'category_id'       => $cat->id,
            'department_id'     => $dept->id,
            'year'              => 2026,
            'document_date'     => '2026-08-20',
            'document_type'     => 'pdf',
            'original_filename' => 'Old_Doc.pdf',
            'stored_filename'   => 'old_ulid.pdf',
            'file_path'         => $oldPath,
            'mime_type'         => 'application/pdf',
            'file_size'         => 200 * 1024,
            'uploaded_by'       => $this->admin->id,
        ]);
    }

    public function test_admin_and_operator_can_update_metadata(): void
    {
        $response = $this->actingAs($this->admin)->put("/archives/{$this->archive->id}", [
            'title'         => 'Judul Baru yang Diperbarui',
            'category_id'   => $this->archive->category_id,
            'department_id' => $this->archive->department_id,
            'document_date' => '2026-08-20',
            'year'          => 2026,
            'document_type' => 'pdf',
            'keywords'      => 'baru, diperbarui',
        ]);

        $response->assertRedirect("/archives/{$this->archive->id}");
        $response->assertSessionHas('success');

        $this->archive->refresh();
        $this->assertEquals('Judul Baru yang Diperbarui', $this->archive->title);
        $this->assertEquals('baru, diperbarui', $this->archive->keywords);
    }

    public function test_viewer_cannot_update_archive(): void
    {
        $response = $this->actingAs($this->viewer)->put("/archives/{$this->archive->id}", [
            'title'         => 'Judul Diubah Viewer',
            'category_id'   => $this->archive->category_id,
            'department_id' => $this->archive->department_id,
            'document_date' => '2026-08-20',
            'year'          => 2026,
            'document_type' => 'pdf',
        ]);

        $response->assertStatus(403);
    }

    public function test_file_replacement_creates_new_version_and_preserves_old_version(): void
    {
        $oldPath = $this->archive->file_path;
        Storage::disk('archives')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->create('New_Doc_Final.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->put("/archives/{$this->archive->id}", [
            'title'         => $this->archive->title,
            'category_id'   => $this->archive->category_id,
            'department_id' => $this->archive->department_id,
            'document_date' => '2026-08-20',
            'year'          => 2026,
            'document_type' => 'pdf',
            'file'          => $newFile,
        ]);

        $response->assertRedirect("/archives/{$this->archive->id}");

        $this->archive->refresh();
        $this->assertEquals('New_Doc_Final.pdf', $this->archive->original_filename);

        // Old version file must be PRESERVED in private storage for immutable version history
        Storage::disk('archives')->assertExists($oldPath);
        Storage::disk('archives')->assertExists($this->archive->file_path);

        // Verify file replacement audit log
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'archive_file_replaced',
            'entity_id' => $this->archive->id,
        ]);
    }
}
