<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Archive;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::create(['name' => 'Tata Usaha', 'code' => 'TU', 'is_active' => true]);
        $cat  = Category::create(['name' => 'Administrasi', 'code' => 'ADM', 'is_active' => true]);

        $this->admin = User::factory()->create([
            'role'          => UserRole::ADMIN,
            'is_active'     => true,
            'department_id' => $dept->id,
        ]);

        // Active Archive
        Archive::create([
            'archive_number'    => 'ARSIP-2026-000001',
            'title'             => 'Aktif 2026',
            'category_id'       => $cat->id,
            'department_id'     => $dept->id,
            'year'              => (int) date('Y'),
            'document_date'     => date('Y-m-d'),
            'document_type'     => 'pdf',
            'original_filename' => 'active.pdf',
            'stored_filename'   => 'active.pdf',
            'file_path'         => '2026/active.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 100,
            'uploaded_by'       => $this->admin->id,
        ]);

        // Soft Deleted Archive (must be excluded from stats)
        $deleted = Archive::create([
            'archive_number'    => 'ARSIP-2026-000002',
            'title'             => 'Terhapus 2026',
            'category_id'       => $cat->id,
            'department_id'     => $dept->id,
            'year'              => (int) date('Y'),
            'document_date'     => date('Y-m-d'),
            'document_type'     => 'pdf',
            'original_filename' => 'deleted.pdf',
            'stored_filename'   => 'deleted.pdf',
            'file_path'         => '2026/deleted.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 100,
            'uploaded_by'       => $this->admin->id,
        ]);
        $deleted->delete();
    }

    public function test_dashboard_renders_with_real_kpi_and_recent_archives(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Aktif 2026');
        $response->assertDontSee('Terhapus 2026');
        $response->assertSee('Tata Usaha');
        $response->assertSee('Administrasi');
    }
}
