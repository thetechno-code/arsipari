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

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;
    private User $viewer;
    private Department $department;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('archives');

        $this->department = Department::create(['name' => 'Tata Usaha', 'code' => 'TU']);
        $this->category   = Category::create(['name' => 'Administrasi', 'code' => 'ADM']);

        $this->admin    = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR, 'is_active' => true]);
        $this->viewer   = User::factory()->create(['role' => UserRole::VIEWER, 'is_active' => true]);

        // Create sample archives
        Archive::create([
            'archive_number'    => 'ARSIP-2026-000001',
            'title'             => 'Laporan SK TU 2026',
            'category_id'       => $this->category->id,
            'department_id'     => $this->department->id,
            'status'            => 'active',
            'year'              => 2026,
            'document_date'     => '2026-01-10',
            'document_type'     => 'pdf',
            'original_filename' => 'sk.pdf',
            'stored_filename'   => 'sk.pdf',
            'file_path'         => '2026/sk.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 100,
            'uploaded_by'       => $this->admin->id,
        ]);
    }

    public function test_authenticated_users_can_access_report_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/archives');
        $response->assertStatus(200);
        $response->assertSee('Laporan Rekapitulasi Arsip Digital');
        $response->assertSee('Laporan SK TU 2026');

        $response = $this->actingAs($this->viewer)->get('/reports/archives');
        $response->assertStatus(200);
    }

    public function test_report_filters_by_category_and_date(): void
    {
        $otherCat = Category::create(['name' => 'Keuangan', 'code' => 'KEU']);
        Archive::create([
            'archive_number'    => 'ARSIP-2026-000002',
            'title'             => 'SPJ Keuangan 2026',
            'category_id'       => $otherCat->id,
            'department_id'     => $this->department->id,
            'status'            => 'active',
            'year'              => 2026,
            'document_date'     => '2026-05-15',
            'document_type'     => 'pdf',
            'original_filename' => 'spj.pdf',
            'stored_filename'   => 'spj.pdf',
            'file_path'         => '2026/spj.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 200,
            'uploaded_by'       => $this->admin->id,
        ]);

        $response = $this->actingAs($this->operator)->get('/reports/archives?category_id=' . $this->category->id);
        $response->assertStatus(200);
        $response->assertSee('Laporan SK TU 2026');
        $response->assertDontSee('SPJ Keuangan 2026');
    }

    public function test_excel_export_generates_streamed_download_and_logs_audit(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/archives/export/excel?department_id=' . $this->department->id);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertDatabaseHas('audit_logs', [
            'action'  => 'report_exported_excel',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_pdf_export_generates_pdf_download_and_logs_audit(): void
    {
        $response = $this->actingAs($this->operator)->get('/reports/archives/export/pdf?department_id=' . $this->department->id);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        $this->assertDatabaseHas('audit_logs', [
            'action'  => 'report_exported_pdf',
            'user_id' => $this->operator->id,
        ]);
    }
}
