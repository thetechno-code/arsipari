<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Archive;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;
    private User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR, 'is_active' => true]);
        $this->viewer = User::factory()->create(['role' => UserRole::VIEWER, 'is_active' => true]);
    }

    public function test_admin_can_view_categories_list(): void
    {
        Category::create(['name' => 'Administrasi', 'code' => 'ADM']);

        $response = $this->actingAs($this->admin)->get('/categories');

        $response->assertStatus(200);
        $response->assertSee('Administrasi');
        $response->assertSee('ADM');
    }

    public function test_admin_can_create_root_category(): void
    {
        $response = $this->actingAs($this->admin)->post('/categories', [
            'name'        => 'Keuangan',
            'code'        => 'keu', // should be uppercase
            'description' => 'Kategori Keuangan',
            'parent_id'   => null,
            'is_active'   => '1',
        ]);

        $response->assertRedirect('/categories');
        $response->assertSessionHas('success', 'Kategori berhasil dibuat.');

        $this->assertDatabaseHas('categories', [
            'name'      => 'Keuangan',
            'code'      => 'KEU',
            'parent_id' => null,
        ]);
    }

    public function test_admin_can_create_subcategory_under_root(): void
    {
        $root = Category::create(['name' => 'Administrasi', 'code' => 'ADM']);

        $response = $this->actingAs($this->admin)->post('/categories', [
            'name'      => 'Surat Keputusan',
            'code'      => 'ADM-SK',
            'parent_id' => $root->id,
            'is_active' => '1',
        ]);

        $response->assertRedirect('/categories');
        $response->assertSessionHas('success', 'Kategori berhasil dibuat.');

        $this->assertDatabaseHas('categories', [
            'name'      => 'Surat Keputusan',
            'code'      => 'ADM-SK',
            'parent_id' => $root->id,
        ]);
    }

    public function test_level_3_subcategory_creation_is_rejected(): void
    {
        $root = Category::create(['name' => 'Administrasi', 'code' => 'ADM']);
        $sub  = Category::create(['name' => 'Surat', 'code' => 'ADM-SRT', 'parent_id' => $root->id]);

        $response = $this->actingAs($this->admin)->post('/categories', [
            'name'      => 'SK Kepala',
            'code'      => 'ADM-SRT-SK',
            'parent_id' => $sub->id, // Subcategory trying to be a parent
        ]);

        $response->assertSessionHas('error', 'Subkategori tidak boleh dijadikan induk (Maksimal 2 level hirarki).');
    }

    public function test_circular_parent_assignment_is_rejected(): void
    {
        $root = Category::create(['name' => 'Administrasi', 'code' => 'ADM']);

        $response = $this->actingAs($this->admin)->put("/categories/{$root->id}", [
            'name'      => 'Administrasi',
            'code'      => 'ADM',
            'parent_id' => $root->id, // Self parent
        ]);

        $response->assertSessionHas('error', 'Kategori tidak dapat menjadi induk bagi dirinya sendiri.');
    }

    public function test_duplicate_code_is_rejected(): void
    {
        Category::create(['name' => 'Administrasi', 'code' => 'ADM']);

        $response = $this->actingAs($this->admin)->post('/categories', [
            'name' => 'Arsip Umum',
            'code' => 'ADM', // duplicate code
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_same_name_under_different_parent_is_allowed(): void
    {
        $cat1 = Category::create(['name' => 'Administrasi', 'code' => 'ADM']);
        $cat2 = Category::create(['name' => 'Keuangan', 'code' => 'KEU']);

        Category::create(['name' => 'Laporan', 'code' => 'ADM-LAP', 'parent_id' => $cat1->id]);

        $response = $this->actingAs($this->admin)->post('/categories', [
            'name'      => 'Laporan', // same name, different parent (KEU)
            'code'      => 'KEU-LAP',
            'parent_id' => $cat2->id,
        ]);

        $response->assertRedirect('/categories');
        $response->assertSessionHas('success', 'Kategori berhasil dibuat.');
    }

    public function test_parent_deactivation_rejected_if_active_child_exists(): void
    {
        $root = Category::create(['name' => 'Administrasi', 'code' => 'ADM', 'is_active' => true]);
        Category::create(['name' => 'SK', 'code' => 'ADM-SK', 'parent_id' => $root->id, 'is_active' => true]);

        $response = $this->actingAs($this->admin)->put("/categories/{$root->id}/status");

        $response->assertSessionHas('error', 'Kategori induk tidak dapat dinonaktifkan karena masih memiliki subkategori aktif.');

        $root->refresh();
        $this->assertTrue($root->is_active);
    }

    public function test_category_deletion_rejected_if_has_children(): void
    {
        $root = Category::create(['name' => 'Administrasi', 'code' => 'ADM']);
        Category::create(['name' => 'SK', 'code' => 'ADM-SK', 'parent_id' => $root->id]);

        $response = $this->actingAs($this->admin)->delete("/categories/{$root->id}");

        $response->assertSessionHas('error', 'Kategori tidak dapat dihapus karena masih memiliki subkategori.');
        $this->assertDatabaseHas('categories', ['id' => $root->id]);
    }

    public function test_category_deletion_rejected_if_used_by_archives(): void
    {
        $cat = Category::create(['name' => 'SK', 'code' => 'SK']);
        Archive::create([
            'archive_number'    => 'ARSIP-001',
            'title'             => 'Test Doc',
            'category_id'       => $cat->id,
            'year'              => 2026,
            'original_filename' => 'doc.pdf',
            'stored_filename'   => 'doc.pdf',
            'file_path'         => 'archives/doc.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 100,
            'uploaded_by'       => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/categories/{$cat->id}");

        $response->assertSessionHas('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh arsip.');
        $this->assertDatabaseHas('categories', ['id' => $cat->id]);
    }

    public function test_unused_category_can_be_deleted(): void
    {
        $cat = Category::create(['name' => 'Temporer', 'code' => 'TMP']);

        $response = $this->actingAs($this->admin)->delete("/categories/{$cat->id}");

        $response->assertRedirect('/categories');
        $response->assertSessionHas('success', 'Kategori berhasil dihapus.');
        $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
    }

    public function test_operator_and_viewer_cannot_mutate_categories(): void
    {
        $cat = Category::create(['name' => 'Administrasi', 'code' => 'ADM']);

        $response = $this->actingAs($this->operator)->post('/categories', ['name' => 'Test', 'code' => 'TST']);
        $response->assertStatus(403);

        $response = $this->actingAs($this->viewer)->delete("/categories/{$cat->id}");
        $response->assertStatus(403);
    }
}
