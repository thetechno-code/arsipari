<?php

namespace Tests\Unit;

use App\Models\Archive;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_relationships(): void
    {
        $user = User::factory()->create();
        $dept = Department::create(['name' => 'Tata Usaha', 'code' => 'TU']);
        $cat  = Category::create(['name' => 'SK', 'code' => 'SK']);

        $archive = Archive::create([
            'archive_number'    => 'ARSIP-001',
            'title'             => 'SK Pengangkatan',
            'category_id'       => $cat->id,
            'department_id'     => $dept->id,
            'year'              => 2026,
            'original_filename' => 'sk.pdf',
            'stored_filename'   => 'sk_stored.pdf',
            'file_path'         => 'archives/sk_stored.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 2048576,
            'uploaded_by'       => $user->id,
        ]);

        $this->assertEquals($cat->id, $archive->category->id);
        $this->assertEquals($dept->id, $archive->department->id);
        $this->assertEquals($user->id, $archive->uploader->id);
        $this->assertEquals('1.95 MB', $archive->file_size_formatted);
        $this->assertEquals('pdf', $archive->file_extension);
    }

    public function test_soft_delete_behavior(): void
    {
        $user = User::factory()->create();
        $archive = Archive::create([
            'archive_number'    => 'ARSIP-002',
            'title'             => 'Test Soft Delete',
            'year'              => 2026,
            'original_filename' => 'test.pdf',
            'stored_filename'   => 'test.pdf',
            'file_path'         => 'archives/test.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 1000,
            'uploaded_by'       => $user->id,
        ]);

        $archive->delete();

        $this->assertSoftDeleted($archive);
        $this->assertCount(0, Archive::all());
        $this->assertCount(1, Archive::withTrashed()->get());
    }

    public function test_search_and_filter_scopes(): void
    {
        $user = User::factory()->create();
        $dept = Department::create(['name' => 'Keuangan', 'code' => 'KEU']);

        Archive::create([
            'archive_number'    => 'ARSIP-100',
            'title'             => 'Laporan SPJ Tahunan',
            'department_id'     => $dept->id,
            'year'              => 2026,
            'keywords'          => 'spj, keuangan, 2026',
            'original_filename' => 'spj.pdf',
            'stored_filename'   => 'spj.pdf',
            'file_path'         => 'archives/spj.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 5000,
            'uploaded_by'       => $user->id,
        ]);

        $results = Archive::search('SPJ')->get();
        $this->assertCount(1, $results);

        $resultsByYear = Archive::filterYear(2026)->get();
        $this->assertCount(1, $resultsByYear);

        $resultsByDept = Archive::filterDepartment($dept->id)->get();
        $this->assertCount(1, $resultsByDept);
    }
}
