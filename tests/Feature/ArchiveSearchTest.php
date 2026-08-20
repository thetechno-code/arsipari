<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Archive;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => UserRole::VIEWER, 'is_active' => true]);

        $dept = Department::create(['name' => 'Tata Usaha', 'code' => 'TU']);
        $cat  = Category::create(['name' => 'Administrasi', 'code' => 'ADM']);

        Archive::create([
            'archive_number'    => 'ARSIP-2026-000001',
            'document_number'   => '800/SK/2026',
            'title'             => 'Surat Keputusan Pembagian Tugas Guru',
            'category_id'       => $cat->id,
            'department_id'     => $dept->id,
            'year'              => 2026,
            'document_date'     => '2026-01-15',
            'document_type'     => 'pdf',
            'keywords'          => 'pembagian tugas, mengajar, kurikulum',
            'original_filename' => 'sk.pdf',
            'stored_filename'   => 'sk.pdf',
            'file_path'         => '2026/sk/sk.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 100,
            'uploaded_by'       => $this->user->id,
        ]);

        Archive::create([
            'archive_number'    => 'ARSIP-2026-000002',
            'document_number'   => '900/LAP/2026',
            'title'             => 'Laporan Keuangan Madrasah',
            'category_id'       => $cat->id,
            'department_id'     => $dept->id,
            'year'              => 2026,
            'document_date'     => '2026-02-10',
            'document_type'     => 'xlsx',
            'keywords'          => 'spj, keuangan, anggaran',
            'original_filename' => 'keu.xlsx',
            'stored_filename'   => 'keu.xlsx',
            'file_path'         => '2026/keu/keu.xlsx',
            'mime_type'         => 'application/xlsx',
            'file_size'         => 200,
            'uploaded_by'       => $this->user->id,
        ]);
    }

    public function test_search_by_archive_number(): void
    {
        $response = $this->actingAs($this->user)->get('/archives?search=ARSIP-2026-000001');

        $response->assertStatus(200);
        $response->assertSee('Surat Keputusan Pembagian Tugas Guru');
        $response->assertDontSee('Laporan Keuangan Madrasah');
    }

    public function test_search_by_document_number(): void
    {
        $response = $this->actingAs($this->user)->get('/archives?search=900/LAP/2026');

        $response->assertStatus(200);
        $response->assertSee('Laporan Keuangan Madrasah');
        $response->assertDontSee('Surat Keputusan Pembagian Tugas Guru');
    }

    public function test_search_by_title_substring(): void
    {
        $response = $this->actingAs($this->user)->get('/archives?search=Pembagian');

        $response->assertStatus(200);
        $response->assertSee('Surat Keputusan Pembagian Tugas Guru');
        $response->assertDontSee('Laporan Keuangan Madrasah');
    }

    public function test_search_by_keywords(): void
    {
        $response = $this->actingAs($this->user)->get('/archives?search=anggaran');

        $response->assertStatus(200);
        $response->assertSee('Laporan Keuangan Madrasah');
        $response->assertDontSee('Surat Keputusan Pembagian Tugas Guru');
    }

    public function test_case_insensitive_search(): void
    {
        $response = $this->actingAs($this->user)->get('/archives?search=LAPORAN');

        $response->assertStatus(200);
        $response->assertSee('Laporan Keuangan Madrasah');
    }
}
