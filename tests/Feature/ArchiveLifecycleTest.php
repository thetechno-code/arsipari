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

class ArchiveLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;
    private Archive $archive;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('archives');

        $dept = Department::create(['name' => 'TU', 'code' => 'TU']);
        $cat  = Category::create(['name' => 'ADM', 'code' => 'ADM']);

        $this->admin    = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR, 'is_active' => true]);

        $file = UploadedFile::fake()->create('doc.pdf', 100);
        Storage::disk('archives')->putFileAs('2026/lc', $file, 'lc.pdf');

        $this->archive = Archive::create([
            'archive_number'    => 'ARSIP-2026-000001',
            'title'             => 'Doc Lifecycle Test',
            'category_id'       => $cat->id,
            'department_id'     => $dept->id,
            'status'            => 'active',
            'year'              => 2026,
            'original_filename' => 'doc.pdf',
            'stored_filename'   => 'lc.pdf',
            'file_path'         => '2026/lc/lc.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 100,
            'uploaded_by'       => $this->admin->id,
        ]);
    }

    public function test_admin_can_toggle_archive_operational_status(): void
    {
        $response = $this->actingAs($this->admin)->put("/archives/{$this->archive->id}/status");
        $response->assertRedirect();

        $this->archive->refresh();
        $this->assertEquals('inactive', $this->archive->status);

        // Toggle back
        $this->actingAs($this->admin)->put("/archives/{$this->archive->id}/status");
        $this->archive->refresh();
        $this->assertEquals('active', $this->archive->status);
    }

    public function test_operator_cannot_toggle_status_or_view_trash(): void
    {
        $response = $this->actingAs($this->operator)->put("/archives/{$this->archive->id}/status");
        $response->assertStatus(403);

        $response = $this->actingAs($this->operator)->get('/trash');
        $response->assertStatus(403);
    }

    public function test_admin_can_soft_delete_and_restore_from_trash(): void
    {
        $this->actingAs($this->admin)->delete("/archives/{$this->archive->id}");
        $this->assertSoftDeleted('archives', ['id' => $this->archive->id]);

        $response = $this->actingAs($this->admin)->get('/trash');
        $response->assertStatus(200);
        $response->assertSee('Doc Lifecycle Test');

        $response = $this->actingAs($this->admin)->put("/archives/{$this->archive->id}/restore");
        $response->assertRedirect("/archives/{$this->archive->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('archives', ['id' => $this->archive->id, 'deleted_at' => null]);
    }
}
