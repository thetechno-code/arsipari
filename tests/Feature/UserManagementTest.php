<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;
    private User $viewer;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create([
            'name' => 'Tata Usaha',
            'code' => 'TU',
        ]);

        $this->admin = User::factory()->create([
            'name'          => 'Admin User',
            'email'         => 'admin@arsipari.local',
            'role'          => UserRole::ADMIN,
            'department_id' => $this->department->id,
            'is_active'     => true,
        ]);

        $this->operator = User::factory()->create([
            'name'      => 'Operator User',
            'role'      => UserRole::OPERATOR,
            'is_active' => true,
        ]);

        $this->viewer = User::factory()->create([
            'name'      => 'Viewer User',
            'role'      => UserRole::VIEWER,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/users');

        $response->assertStatus(200);
        $response->assertSee('Daftar Pengguna');
        $response->assertSee('Admin User');
    }

    public function test_admin_can_search_and_filter_users(): void
    {
        $response = $this->actingAs($this->admin)->get('/users?search=Operator');

        $response->assertStatus(200);
        $response->assertSee('Operator User');
        $response->assertDontSee('Viewer User');
    }

    public function test_admin_can_create_new_user(): void
    {
        $userData = [
            'name'                  => 'Budi Santoso',
            'email'                 => 'budi@arsipari.local',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'operator',
            'department_id'         => $this->department->id,
            'is_active'             => '1',
        ];

        $response = $this->actingAs($this->admin)->post('/users', $userData);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'User berhasil dibuat.');

        $this->assertDatabaseHas('users', [
            'email' => 'budi@arsipari.local',
            'name'  => 'Budi Santoso',
            'role'  => 'operator',
        ]);

        // Audit log check
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user_create',
        ]);
    }

    public function test_admin_can_edit_user(): void
    {
        $response = $this->actingAs($this->admin)->put("/users/{$this->operator->id}", [
            'name'          => 'Operator Updated',
            'email'         => $this->operator->email,
            'role'          => 'operator',
            'department_id' => $this->department->id,
            'is_active'     => '1',
        ]);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'User berhasil diperbarui.');

        $this->assertDatabaseHas('users', [
            'id'            => $this->operator->id,
            'name'          => 'Operator Updated',
            'department_id' => $this->department->id,
        ]);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $response = $this->actingAs($this->admin)->put("/users/{$this->operator->id}/status");

        $response->assertSessionHas('success', 'User berhasil dinonaktifkan.');

        $this->operator->refresh();
        $this->assertFalse($this->operator->is_active);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $response = $this->actingAs($this->admin)->put("/users/{$this->admin->id}/status");

        $response->assertSessionHas('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');

        $this->admin->refresh();
        $this->assertTrue($this->admin->is_active);
    }

    public function test_admin_can_reset_user_password(): void
    {
        $response = $this->actingAs($this->admin)->put("/users/{$this->operator->id}/password", [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHas('success', 'Password berhasil diubah.');

        // Verify login works with new password
        $loginResponse = $this->post('/login', [
            'email'    => $this->operator->email,
            'password' => 'newpassword123',
        ]);

        $loginResponse->assertRedirect('/');
    }

    public function test_operator_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->operator)->get('/users');
        $response->assertStatus(403);
    }

    public function test_viewer_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->viewer)->get('/users');
        $response->assertStatus(403);
    }
}
