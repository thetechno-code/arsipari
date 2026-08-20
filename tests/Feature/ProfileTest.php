<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create([
            'name'      => 'Ahmad Profil',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Ahmad Profil');
        $response->assertSee('Profil Saya');
    }

    public function test_user_can_update_own_name(): void
    {
        $user = User::factory()->create(['name' => 'Nama Lama', 'is_active' => true]);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Nama Baru',
        ]);

        $response->assertSessionHas('success', 'Profil berhasil diperbarui.');

        $user->refresh();
        $this->assertEquals('Nama Baru', $user->name);
    }

    public function test_user_can_change_own_password(): void
    {
        $user = User::factory()->create([
            'password'  => bcrypt('oldpassword'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password'      => 'oldpassword',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHas('success', 'Password berhasil diubah.');

        // Verify login works with new password
        $this->post('/logout');
        $loginResponse = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'newpassword123',
        ]);

        $loginResponse->assertRedirect('/');
    }

    public function test_password_change_fails_with_invalid_current_password(): void
    {
        $user = User::factory()->create([
            'password'  => bcrypt('correctpassword'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password'      => 'wrongpassword',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }
}
