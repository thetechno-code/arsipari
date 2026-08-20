<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('ARSIPARI');
        $response->assertSee('Masuk');
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'         => 'admin@arsipari.local',
            'password'      => bcrypt('password'),
            'role'          => 'admin',
            'is_active'     => true,
            'last_login_at' => null,
        ]);

        $response = $this->post('/login', [
            'email'    => 'admin@arsipari.local',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/');

        // Verify last_login_at updated
        $user->refresh();
        $this->assertNotNull($user->last_login_at);

        // Verify login audit log recorded
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'login',
        ]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email'     => 'inactive@arsipari.local',
            'password'  => bcrypt('password'),
            'role'      => 'operator',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email'    => 'inactive@arsipari.local',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.']);
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        User::factory()->create([
            'email'    => 'admin@arsipari.local',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'admin@arsipari.local',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_logout_and_audit_is_recorded(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');

        // Verify logout audit log recorded
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action'  => 'logout',
        ]);
    }
}
