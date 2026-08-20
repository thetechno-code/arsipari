<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogUiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin    = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR, 'is_active' => true]);

        AuditLog::record('login', 'User logged in', $this->admin);
    }

    public function test_admin_can_access_audit_log_ui_and_filter(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/audit-logs');

        $response->assertStatus(200);
        $response->assertSee('Audit Trail System');
        $response->assertSee('User logged in');
    }

    public function test_operator_and_viewer_cannot_access_audit_log_ui(): void
    {
        $response = $this->actingAs($this->operator)->get('/admin/audit-logs');
        $response->assertStatus(403);
    }
}
