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

class ArchiveDownloadTest extends TestCase
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

        $file = UploadedFile::fake()->create('Laporan_2026.pdf', 300, 'application/pdf');
        $storedPath = '2026/test_ulid/test_ulid.pdf';

        Storage::disk('archives')->putFileAs('2026/test_ulid', $file, 'test_ulid.pdf');

        $this->archive = Archive::create([
            'archive_number'    => 'ARSIP-2026-000001',
            'title'             => 'Laporan Tahunan 2026',
            'category_id'       => $cat->id,
            'department_id'     => $dept->id,
            'year'              => 2026,
            'document_date'     => '2026-08-20',
            'document_type'     => 'pdf',
            'original_filename' => 'Laporan_2026.pdf',
            'stored_filename'   => 'test_ulid.pdf',
            'file_path'         => $storedPath,
            'mime_type'         => 'application/pdf',
            'file_size'         => 300 * 1024,
            'uploaded_by'       => $this->admin->id,
        ]);
    }

    public function test_authenticated_users_can_download_archive_file(): void
    {
        $response = $this->actingAs($this->admin)->get("/archives/{$this->archive->id}/download");
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');

        $response = $this->actingAs($this->operator)->get("/archives/{$this->archive->id}/download");
        $response->assertStatus(200);

        $response = $this->actingAs($this->viewer)->get("/archives/{$this->archive->id}/download");
        $response->assertStatus(200);

        // Audit log recorded
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'archive_download',
            'entity_id' => $this->archive->id,
        ]);
    }

    public function test_guest_cannot_download_archive_file(): void
    {
        $response = $this->get("/archives/{$this->archive->id}/download");
        $response->assertRedirect('/login');
    }

    public function test_inactive_user_cannot_download_archive_file(): void
    {
        $inactiveUser = User::factory()->create(['role' => UserRole::VIEWER, 'is_active' => false]);

        $response = $this->actingAs($inactiveUser)->get("/archives/{$this->archive->id}/download");
        $response->assertStatus(403);
    }

    public function test_missing_physical_file_shows_friendly_error(): void
    {
        // Delete physical file from fake storage
        Storage::disk('archives')->delete($this->archive->file_path);

        $response = $this->actingAs($this->viewer)->get("/archives/{$this->archive->id}/download");

        $response->assertRedirect();
        $response->assertSessionHas('error', 'File arsip tidak ditemukan pada penyimpanan fisik. Silakan hubungi administrator.');
    }
}
