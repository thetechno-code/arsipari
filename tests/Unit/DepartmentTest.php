<?php

namespace Tests\Unit;

use App\Models\Archive;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_has_many_users(): void
    {
        $dept = Department::create([
            'name' => 'Tata Usaha',
            'code' => 'TU',
        ]);

        $user = User::factory()->create([
            'department_id' => $dept->id,
        ]);

        $this->assertTrue($dept->users->contains($user));
        $this->assertEquals($dept->id, $user->department->id);
    }

    public function test_department_has_many_archives(): void
    {
        $dept = Department::create(['name' => 'Kurikulum', 'code' => 'KUR']);
        $user = User::factory()->create(['department_id' => $dept->id]);

        $archive = Archive::create([
            'archive_number'    => 'ARSIP-TEST-001',
            'title'             => 'Dokumen Kurikulum Test',
            'department_id'     => $dept->id,
            'year'              => 2026,
            'original_filename' => 'doc.pdf',
            'stored_filename'   => 'doc_stored.pdf',
            'file_path'         => 'archives/doc_stored.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 1024,
            'uploaded_by'       => $user->id,
        ]);

        $this->assertTrue($dept->archives->contains($archive));
    }

    public function test_active_scope_filters_inactive_departments(): void
    {
        Department::create(['name' => 'Active Dept', 'code' => 'ACT', 'is_active' => true]);
        Department::create(['name' => 'Inactive Dept', 'code' => 'INACT', 'is_active' => false]);

        $activeDepts = Department::active()->get();

        $this->assertCount(1, $activeDepts);
        $this->assertEquals('ACT', $activeDepts->first()->code);
    }
}
