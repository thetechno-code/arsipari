<?php

namespace Tests\Unit;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_child_relationship(): void
    {
        $parent = Category::create([
            'name' => 'Administrasi',
            'code' => 'ADM',
        ]);

        $child = Category::create([
            'name'      => 'Surat Keputusan',
            'code'      => 'ADM-SK',
            'parent_id' => $parent->id,
        ]);

        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertTrue($parent->children->contains($child));
        $this->assertEquals('Administrasi › Surat Keputusan', $child->full_name);
    }

    public function test_scopes_root_and_active(): void
    {
        $root = Category::create(['name' => 'Root Cat', 'code' => 'ROOT', 'is_active' => true]);
        Category::create(['name' => 'Sub Cat', 'code' => 'SUB', 'parent_id' => $root->id, 'is_active' => true]);
        Category::create(['name' => 'Inactive Root', 'code' => 'INACT', 'is_active' => false]);

        $roots = Category::roots()->get();
        $this->assertCount(2, $roots);

        $activeRoots = Category::active()->roots()->get();
        $this->assertCount(1, $activeRoots);
        $this->assertEquals('ROOT', $activeRoots->first()->code);
    }
}
