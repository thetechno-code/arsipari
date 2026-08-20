<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Archive;
use App\Models\Category;
use App\Models\Department;
use App\Models\RetentionPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveRetentionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private RetentionPolicy $permanentPolicy;
    private RetentionPolicy $fiveYearPolicy;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('archives');

        $this->admin = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);

        $this->permanentPolicy = RetentionPolicy::create([
            'name'         => 'Permanen',
            'is_permanent' => true,
        ]);

        $this->fiveYearPolicy = RetentionPolicy::create([
            'name'           => '5 Tahun',
            'duration_years' => 5,
            'is_permanent'   => false,
        ]);
    }

    public function test_retention_until_date_calculation(): void
    {
        $dept = Department::create(['name' => 'TU', 'code' => 'TU']);
        $cat  = Category::create(['name' => 'ADM', 'code' => 'ADM']);

        $file = UploadedFile::fake()->create('doc.pdf', 100);

        $this->actingAs($this->admin)->post('/archives', [
            'title'               => 'Doc Retensi 5 Tahun',
            'category_id'         => $cat->id,
            'department_id'       => $dept->id,
            'retention_policy_id' => $this->fiveYearPolicy->id,
            'document_date'       => '2026-05-10',
            'year'                => 2026,
            'document_type'       => 'pdf',
            'file'                => $file,
        ]);

        $archive = Archive::where('title', 'Doc Retensi 5 Tahun')->first();
        $this->assertNotNull($archive);

        $this->assertEquals('2031-05-10', $archive->retention_until->format('Y-m-d'));
        $this->assertEquals('not_due', $archive->retention_status);
    }

    public function test_admin_can_manage_retention_policies(): void
    {
        $response = $this->actingAs($this->admin)->post('/retention-policies', [
            'name'           => '10 Tahun Kebijakan Baru',
            'duration_years' => 10,
            'is_permanent'   => '0',
            'description'    => 'Masa simpan 10 tahun',
        ]);

        $response->assertRedirect('/retention-policies');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('retention_policies', [
            'name'           => '10 Tahun Kebijakan Baru',
            'duration_years' => 10,
        ]);
    }

    public function test_retention_policy_deletion_rejected_if_used_by_archives(): void
    {
        $dept = Department::create(['name' => 'TU', 'code' => 'TU']);
        $cat  = Category::create(['name' => 'ADM', 'code' => 'ADM']);

        Archive::create([
            'archive_number'      => 'ARSIP-2026-000001',
            'title'               => 'Doc Terikat',
            'category_id'         => $cat->id,
            'department_id'       => $dept->id,
            'retention_policy_id' => $this->fiveYearPolicy->id,
            'year'                => 2026,
            'original_filename'   => 'doc.pdf',
            'stored_filename'     => 'doc.pdf',
            'file_path'           => '2026/doc.pdf',
            'mime_type'           => 'application/pdf',
            'file_size'           => 100,
            'uploaded_by'         => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/retention-policies/{$this->fiveYearPolicy->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('retention_policies', ['id' => $this->fiveYearPolicy->id]);
    }
}
