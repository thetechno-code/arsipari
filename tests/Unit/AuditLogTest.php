<?php

namespace Tests\Unit;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $log = AuditLog::create([
            'user_id'     => $user->id,
            'action'      => AuditAction::LOGIN,
            'description' => 'User login',
            'created_at'  => now(),
        ]);

        $this->assertEquals($user->id, $log->user->id);
        $this->assertEquals('Login', $log->action_label);
    }

    public function test_audit_log_user_id_null_on_user_deletion(): void
    {
        $user = User::factory()->create();

        $log = AuditLog::create([
            'user_id'     => $user->id,
            'action'      => AuditAction::CREATE,
            'description' => 'Membuat arsip',
            'created_at'  => now(),
        ]);

        // Force delete the user to test nullOnDelete foreign key constraint
        $user->forceDelete();

        $log->refresh();

        $this->assertNull($log->user_id);
        $this->assertNull($log->user);
    }

    public function test_record_helper_method_saves_metadata(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        AuditLog::record(
            AuditAction::DOWNLOAD,
            'Mengunduh arsip SK',
            null,
            ['archive_id' => 'ULID-123', 'filename' => 'SK.pdf']
        );

        $log = AuditLog::latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('Mengunduh', $log->action_label);
        $this->assertEquals('ULID-123', $log->metadata['archive_id']);
    }
}
