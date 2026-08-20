<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Archive;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveSearchFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Department $deptTU;
    private Department $deptKur;
    private Category $catAdm;
    private Category $catKeu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => UserRole::VIEWER, 'is_active' => true]);

        $this->deptTU  = Department::create(['name' => 'Tata Usaha', 'code' => 'TU']);
        $this->deptKur = Department::create(['name' => 'Kurikulum', 'code' => 'KUR']);
        $this->catAdm  = Category::create(['name' => 'Administrasi', 'code' => 'ADM']);
        $this->catKeu  = Category::create(['name' => 'Keuangan', 'code' => 'KEU']);

        Archive::create([
            'archive_number'    => 'ARSIP-2026-000001',
            'document_number'   => '800/SK/2026',
            'title'             => 'SK Kepala tentang Pembagian Tugas',
            'category_id'       => $this->catAdm->id,
            'department_id'     => $this->deptTU->id,
            'year'              => 2026,
            'document_date'     => '2026-01-15',
            'document_type'     => 'pdf',
            'keywords'          => 'sk, guru, pembagian tugas',
            'original_filename' => 'sk.pdf',
            'stored_filename'   => 'sk.pdf',
            'file_path'         => '2026/sk/sk.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 100,
            'uploaded_by'       => $this->user->id,
        ]);

        Archive::create([
            'archive_number'    => 'ARSIP-2025-000002',
            'document_number'   => '900/LAP/2025',
            'title'             => 'Laporan Pertanggungjawaban Keuangan',
            'category_id'       => $this->catKeu->id,
            'department_id'     => $this->deptKur->id,
            'year'              => 2025,
            'document_date'     => '2025-12-20',
            'document_type'     => 'xlsx',
            'keywords'          => 'spj, keuangan, anggaran',
            'original_filename' => 'spj.xlsx',
            'stored_filename'   => 'spj.xlsx',
            'file_path'         => '2025/spj/spj.xlsx',
            'mime_type'         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'file_size'         => 200,
            'uploaded_by'       => $this->user->id,
        ]);
    }

    public function test_search_by_title_and_keywords(): void
    {
        $response = $this->actingAs($this->user)->get('/archives?search=Pembagian');

        $response->assertStatus(200);
        $response->assertSee('ARSIP-2026-000001');
        $response->assertDontSee('ARSIP-2025-000002');
    }

    public function test_search_by_archive_number(): void
    {
        $response = $this->actingAs($this->user)->get('/archives?search=ARSIP-2025-000002');

        $response->assertStatus(200);
        $response->assertSee('Laporan Pertanggungjawaban Keuangan');
        $response->assertDontSee('SK Kepala tentang Pembagian Tugas');
    }

    public function test_filter_by_category(): void
    {
        $response = $this->actingAs($this->user)->get('/archives?category_id=' . $this->catKeu->id);

        $response->assertStatus(200);
        $response->assertSee('Laporan Pertanggungjawaban Keuangan');
        $response->assertDontSee('SK Kepala tentang Pembagian Tugas');
    }

    public function test_filter_by_year_and_department(): void
    {
        $response = $this->actingAs($this->user)->get('/archives?year=2026&department_id=' . $this->deptTU->id);

        $response->assertStatus(200);
        $response->assertSee('SK Kepala tentang Pembagian Tugas');
        $response->assertDontSee('Laporan Pertanggungjawaban Keuangan');
    }
}
