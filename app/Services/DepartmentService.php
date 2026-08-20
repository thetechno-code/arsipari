<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\Department;
use InvalidArgumentException;

class DepartmentService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function createDepartment(array $data): Department
    {
        $data['code'] = strtoupper(trim($data['code']));

        $department = Department::create($data);

        $this->auditLogService->logDepartmentMutation(
            AuditAction::DEPARTMENT_CREATE,
            $department,
            "Membuat unit/bidang: {$department->name} ({$department->code})"
        );

        return $department;
    }

    public function updateDepartment(Department $department, array $data): Department
    {
        $data['code'] = strtoupper(trim($data['code']));
        $oldData = $department->only(['name', 'code', 'description', 'is_active']);

        $department->update($data);

        $this->auditLogService->logDepartmentMutation(
            AuditAction::DEPARTMENT_UPDATE,
            $department,
            "Mengubah unit/bidang: {$department->name}",
            ['old' => $oldData, 'new' => $data]
        );

        return $department;
    }

    public function toggleStatus(Department $department): bool
    {
        $newStatus = ! $department->is_active;
        $department->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'mengaktifkan' : 'menonaktifkan';
        $this->auditLogService->logDepartmentMutation(
            AuditAction::DEPARTMENT_UPDATE,
            $department,
            "Admin {$statusText} unit/bidang: {$department->name}",
            ['is_active' => $newStatus]
        );

        return $newStatus;
    }

    public function deleteDepartment(Department $department): void
    {
        if ($department->users()->count() > 0) {
            throw new InvalidArgumentException('Unit/bidang tidak dapat dihapus karena masih memiliki pengguna terdaftar.');
        }

        if ($department->archives()->count() > 0) {
            throw new InvalidArgumentException('Unit/bidang tidak dapat dihapus karena masih digunakan oleh arsip.');
        }

        $name = $department->name;
        $department->delete();

        $this->auditLogService->logDepartmentMutation(
            AuditAction::DEPARTMENT_DELETE,
            $department,
            "Menghapus unit/bidang: {$name}"
        );
    }
}
