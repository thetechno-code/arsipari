<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Archive;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $cat1;
    private Category $cat2;
    private Department $dept1;
    private Department $dept2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user  = User::factory()->create(['role' => UserRole::VIEWER, 'is_active' => true]);

        $this->dept1 = Department::create(['name' => 'Tata Usaha', 'code' => 'TU']);
        $this->dept2 = Department::create(['name' => 'Kurikulum', 'code' => 'KUR']);

        $this->cat1  = Category::create(['name' => 'Administrasi', 'code' => 'ADM']);
        $this->cat2  = Category::create(['name' => 'Keuangan', 'code' => 'KEU']);

        Archive::create([
            'archive_number'    => 'ARSIP-2026-000001',
            'title'             => 'Doc TU ADM 2026 PDF',
            'category_id'       => $this->cat1->id,
            'department_id'     => $this->dept1->id,
            'year'              => 2026,
            'document_date'     => '2026-03-10',
            'document_type'     => 'pdf',
            'original_filename' => 'doc1.pdf',
            'stored_filename'   => 'doc1.pdf',
            'file_path'         => '2026/1/doc1.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 100,
            'uploaded_by'       => $this->user->id,
        ]);

        Archive::create([
            'archive_number'    => 'ARSIP-2025-000002',
            'title'             => 'Doc KUR KEU 2025 XLSX',
            'category_id'       => $this->cat2->id,
            'department_id'     => $this->dept2->id,
            'year'              => 2025,
            'document_date'     => '2025-11-20',
            'document_type'     => 'xlsx',
            'original_filename' => 'doc2.xlsx',
            'stored_filename'   => 'doc2.xlsx',
            'file_path'         => '2025/2/doc2.xlsx',
            'mime_type'         => 'application/xlsx',
            'file_size'         => 200,
            'uploaded_by'       => $this->user->id,
        ]);
    }

    public function test_filter_by_category_and_department(): void
    {
        $response = $this->actingAs($this->user)->get("/archives?category_id={$this->cat1->id}&department_id={$this->dept1->id}");

        $response->assertStatus(200);
        $response->assertSee('Doc TU ADM 2026 PDF');
        $response->assertDontSee('Doc KUR KEU 2025 XLSX');
    }

    public function test_filter_by_date_range(): void
    {
        $response = $this->actingAs($this->user)->get('/archives?date_from=2026-01-01&date_to=2026-12-31');

        $response->assertStatus(200);
        $response->assertSee('Doc TU ADM 2026 PDF');
        $response->assertDontSee('Doc KUR KEU 2025 XLSX');
    }

    public function test_invalid_date_range_fails_validation(): void
    {
        $response = $this->actingAs($this->user)->get('/archives?date_from=2026-12-31&date_to=2026-01-01');

        $response->assertSessionHasErrors('date_to');
    }

    public function test_whitelisted_sorting(): void
    {
        $response = $this->actingAs($this->user)->get('/archives?sort=title&direction=asc');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            'Doc KUR KEU 2025 XLSX',
            'Doc TU ADM 2026 PDF',
        ]);
    }
}
