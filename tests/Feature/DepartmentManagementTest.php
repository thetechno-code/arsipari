<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Archive;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR, 'is_active' => true]);
    }

    public function test_admin_can_view_department_list(): void
    {
        Department::create(['name' => 'Tata Usaha', 'code' => 'TU']);

        $response = $this->actingAs($this->admin)->get('/departments');

        $response->assertStatus(200);
        $response->assertSee('Tata Usaha');
        $response->assertSee('TU');
    }

    public function test_admin_can_create_department(): void
    {
        $response = $this->actingAs($this->admin)->post('/departments', [
            'name'        => 'Keuangan',
            'code'        => 'keu',
            'description' => 'Bidang Keuangan',
            'is_active'   => '1',
        ]);

        $response->assertRedirect('/departments');
        $response->assertSessionHas('success', 'Unit/bidang berhasil dibuat.');

        $this->assertDatabaseHas('departments', [
            'name' => 'Keuangan',
            'code' => 'KEU',
        ]);
    }

    public function test_department_code_must_be_unique(): void
    {
        Department::create(['name' => 'Tata Usaha', 'code' => 'TU']);

        $response = $this->actingAs($this->admin)->post('/departments', [
            'name' => 'Tata Usaha II',
            'code' => 'TU',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_department_deletion_rejected_if_has_users(): void
    {
        $dept = Department::create(['name' => 'Tata Usaha', 'code' => 'TU']);
        User::factory()->create(['department_id' => $dept->id]);

        $response = $this->actingAs($this->admin)->delete("/departments/{$dept->id}");

        $response->assertSessionHas('error', 'Unit/bidang tidak dapat dihapus karena masih memiliki pengguna terdaftar.');
        $this->assertDatabaseHas('departments', ['id' => $dept->id]);
    }

    public function test_department_deletion_rejected_if_has_archives(): void
    {
        $dept = Department::create(['name' => 'Kurikulum', 'code' => 'KUR']);
        Archive::create([
            'archive_number'    => 'ARSIP-002',
            'title'             => 'Test Doc Dept',
            'department_id'     => $dept->id,
            'year'              => 2026,
            'original_filename' => 'doc.pdf',
            'stored_filename'   => 'doc.pdf',
            'file_path'         => 'archives/doc.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 100,
            'uploaded_by'       => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/departments/{$dept->id}");

        $response->assertSessionHas('error', 'Unit/bidang tidak dapat dihapus karena masih digunakan oleh arsip.');
        $this->assertDatabaseHas('departments', ['id' => $dept->id]);
    }

    public function test_unused_department_can_be_deleted(): void
    {
        $dept = Department::create(['name' => 'Temp Dept', 'code' => 'TMP']);

        $response = $this->actingAs($this->admin)->delete("/departments/{$dept->id}");

        $response->assertRedirect('/departments');
        $response->assertSessionHas('success', 'Unit/bidang berhasil dihapus.');
        $this->assertDatabaseMissing('departments', ['id' => $dept->id]);
    }

    public function test_operator_cannot_mutate_departments(): void
    {
        $dept = Department::create(['name' => 'Tata Usaha', 'code' => 'TU']);

        $response = $this->actingAs($this->operator)->post('/departments', ['name' => 'X', 'code' => 'X']);
        $response->assertStatus(403);

        $response = $this->actingAs($this->operator)->delete("/departments/{$dept->id}");
        $response->assertStatus(403);
    }
}
