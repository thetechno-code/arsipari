<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemHealthTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin    = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR, 'is_active' => true]);
    }

    public function test_admin_can_access_system_health_info(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/system');

        $response->assertStatus(200);
        $response->assertSee('Informasi');
        $response->assertSee('Kesehatan Server ARSIPARI');
        $response->assertSee('PHP ' . PHP_VERSION);
    }

    public function test_operator_cannot_access_system_health_info(): void
    {
        $response = $this->actingAs($this->operator)->get('/admin/system');
        $response->assertStatus(403);
    }
}
