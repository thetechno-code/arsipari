<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_belongs_to_department(): void
    {
        $dept = Department::create(['name' => 'Keuangan', 'code' => 'KEU']);
        $user = User::factory()->create(['department_id' => $dept->id]);

        $this->assertNotNull($user->department);
        $this->assertEquals('Keuangan', $user->department->name);
    }

    public function test_role_helpers_work_correctly(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $operator = User::factory()->create(['role' => UserRole::OPERATOR]);
        $viewer = User::factory()->create(['role' => UserRole::VIEWER]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isOperator());
        $this->assertFalse($admin->isViewer());

        $this->assertTrue($operator->isOperator());
        $this->assertFalse($operator->isAdmin());

        $this->assertTrue($viewer->isViewer());
        $this->assertFalse($viewer->isAdmin());

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->hasRole(UserRole::ADMIN));
        $this->assertTrue($operator->hasRole(['admin', 'operator']));
        $this->assertFalse($viewer->hasRole(['admin', 'operator']));
    }

    public function test_role_label_accessor(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->assertEquals('Administrator', $admin->role_label);
    }
}
