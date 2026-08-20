<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;
    private User $viewer;
    private Archive $archive;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $this->operator = User::factory()->create([
            'role'      => UserRole::OPERATOR,
            'is_active' => true,
        ]);

        $this->viewer = User::factory()->create([
            'role'      => UserRole::VIEWER,
            'is_active' => true,
        ]);

        $this->archive = Archive::create([
            'archive_number'    => 'ARSIP-AUTH-01',
            'title'             => 'Dokumen Auth Test',
            'year'              => 2026,
            'original_filename' => 'auth.pdf',
            'stored_filename'   => 'auth.pdf',
            'file_path'         => 'archives/auth.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 1024,
            'uploaded_by'       => $this->admin->id,
        ]);
    }

    public function test_admin_has_full_permissions(): void
    {
        $this->assertTrue($this->admin->can('viewAny', Archive::class));
        $this->assertTrue($this->admin->can('view', $this->archive));
        $this->assertTrue($this->admin->can('create', Archive::class));
        $this->assertTrue($this->admin->can('update', $this->archive));
        $this->assertTrue($this->admin->can('delete', $this->archive));
        $this->assertTrue($this->admin->can('download', $this->archive));
        $this->assertTrue($this->admin->can('viewAny', User::class));
        $this->assertTrue($this->admin->can('viewAny', AuditLog::class));
    }

    public function test_operator_permissions(): void
    {
        // Can view, create, update, download archives
        $this->assertTrue($this->operator->can('viewAny', Archive::class));
        $this->assertTrue($this->operator->can('view', $this->archive));
        $this->assertTrue($this->operator->can('create', Archive::class));
        $this->assertTrue($this->operator->can('update', $this->archive));
        $this->assertTrue($this->operator->can('download', $this->archive));

        // CANNOT delete archives
        $this->assertFalse($this->operator->can('delete', $this->archive));

        // CANNOT manage users or view audit logs
        $this->assertFalse($this->operator->can('viewAny', User::class));
        $this->assertFalse($this->operator->can('viewAny', AuditLog::class));
        $this->assertFalse($this->operator->can('create', Category::class));
        $this->assertFalse($this->operator->can('create', Department::class));
    }

    public function test_viewer_permissions(): void
    {
        // Can view and download archives
        $this->assertTrue($this->viewer->can('viewAny', Archive::class));
        $this->assertTrue($this->viewer->can('view', $this->archive));
        $this->assertTrue($this->viewer->can('download', $this->archive));

        // CANNOT create, update, delete archives
        $this->assertFalse($this->viewer->can('create', Archive::class));
        $this->assertFalse($this->viewer->can('update', $this->archive));
        $this->assertFalse($this->viewer->can('delete', $this->archive));

        // CANNOT manage users or view audit logs
        $this->assertFalse($this->viewer->can('viewAny', User::class));
        $this->assertFalse($this->viewer->can('viewAny', AuditLog::class));
    }
}
