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

class ArchiveDeleteRestoreTest extends TestCase
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

        $file = UploadedFile::fake()->create('Test_Doc.pdf', 200);
        $storedPath = '2026/test_del/test_del.pdf';
        Storage::disk('archives')->putFileAs('2026/test_del', $file, 'test_del.pdf');

        $this->archive = Archive::create([
            'archive_number'    => 'ARSIP-2026-000001',
            'title'             => 'Dokumen untuk Dihapus',
            'category_id'       => $cat->id,
            'department_id'     => $dept->id,
            'year'              => 2026,
            'document_date'     => '2026-08-20',
            'document_type'     => 'pdf',
            'original_filename' => 'Test_Doc.pdf',
            'stored_filename'   => 'test_del.pdf',
            'file_path'         => $storedPath,
            'mime_type'         => 'application/pdf',
            'file_size'         => 200 * 1024,
            'uploaded_by'       => $this->admin->id,
        ]);
    }

    public function test_admin_can_soft_delete_archive(): void
    {
        $response = $this->actingAs($this->admin)->delete("/archives/{$this->archive->id}");

        $response->assertRedirect('/archives');
        $response->assertSessionHas('success');

        // Verify soft-deleted
        $this->assertSoftDeleted('archives', ['id' => $this->archive->id]);

        // Physical file must REMAIN in storage for recovery
        Storage::disk('archives')->assertExists($this->archive->file_path);

        // Audit log recorded
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'archive_delete',
            'entity_id' => $this->archive->id,
        ]);
    }

    public function test_operator_and_viewer_cannot_delete_archive(): void
    {
        $response = $this->actingAs($this->operator)->delete("/archives/{$this->archive->id}");
        $response->assertStatus(403);

        $response = $this->actingAs($this->viewer)->delete("/archives/{$this->archive->id}");
        $response->assertStatus(403);

        $this->assertDatabaseHas('archives', ['id' => $this->archive->id, 'deleted_at' => null]);
    }

    public function test_deleted_archive_not_visible_in_regular_list(): void
    {
        $this->archive->delete();

        $response = $this->actingAs($this->admin)->get('/archives');
        $response->assertStatus(200);
        $response->assertDontSee('Dokumen untuk Dihapus');
    }

    public function test_admin_can_restore_soft_deleted_archive(): void
    {
        $this->archive->delete();

        $response = $this->actingAs($this->admin)->put("/archives/{$this->archive->id}/restore");

        $response->assertRedirect("/archives/{$this->archive->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('archives', [
            'id'         => $this->archive->id,
            'deleted_at' => null,
        ]);

        // Audit log recorded
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'archive_restore',
            'entity_id' => $this->archive->id,
        ]);
    }
}
