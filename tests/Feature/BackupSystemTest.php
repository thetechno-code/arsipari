<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;
    private BackupService $backupService;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('archives');

        $this->admin    = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR, 'is_active' => true]);

        $this->backupService = app(BackupService::class);
    }

    public function test_admin_can_create_backup_and_generate_valid_zip_package(): void
    {
        $result = $this->backupService->createBackup();

        $this->assertTrue($result['success']);
        $this->assertFileExists($result['path']);
        $this->assertEquals('sqlite', $result['manifest']['database']);
        $this->assertEquals(config('arsipari.app_name'), $result['manifest']['application']);

        // Clean up generated zip
        File::delete($result['path']);
    }

    public function test_admin_can_access_backup_management_ui(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/backups');

        $response->assertStatus(200);
        $response->assertSee('Paket Backup Sistem');
    }

    public function test_non_admin_cannot_access_backup_management(): void
    {
        $response = $this->actingAs($this->operator)->get('/admin/backups');
        $response->assertStatus(403);
    }

    public function test_path_traversal_attack_in_backup_download_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/backups/..%2F..%2Fdatabase.sqlite/download');
        $this->assertNotEquals(200, $response->getStatusCode());
    }
}
