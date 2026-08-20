<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * Record a generic audit log entry.
     */
    public function record(
        string|AuditAction $action,
        ?string $description = null,
        ?Model $entity = null,
        ?array $metadata = null
    ): void {
        AuditLog::record($action, $description, $entity, $metadata);
    }

    /**
     * Record login event.
     */
    public function logLogin(?Model $user = null): void
    {
        $this->record(
            AuditAction::LOGIN,
            'Pengguna berhasil masuk ke sistem',
            $user
        );
    }

    /**
     * Record logout event.
     */
    public function logLogout(?Model $user = null): void
    {
        $this->record(
            AuditAction::LOGOUT,
            'Pengguna keluar dari sistem',
            $user
        );
    }

    /**
     * Record user creation event.
     */
    public function logUserCreate(Model $newUser): void
    {
        $this->record(
            AuditAction::USER_CREATE,
            "Membuat akun pengguna baru: {$newUser->name} ({$newUser->email})",
            $newUser,
            [
                'role'          => $newUser->role_label,
                'department_id' => $newUser->department_id,
            ]
        );
    }

    /**
     * Record user update event.
     */
    public function logUserUpdate(Model $updatedUser, array $changes = []): void
    {
        $this->record(
            AuditAction::USER_UPDATE,
            "Mengubah data pengguna: {$updatedUser->name}",
            $updatedUser,
            ['changes' => $changes]
        );
    }

    /**
     * Record user status change event.
     */
    public function logUserStatusChange(Model $user, bool $newStatus): void
    {
        $statusText = $newStatus ? 'mengaktifkan' : 'menonaktifkan';
        $this->record(
            AuditAction::USER_UPDATE,
            "Admin {$statusText} akun pengguna: {$user->name}",
            $user,
            ['is_active' => $newStatus]
        );
    }

    /**
     * Record password change event.
     */
    public function logPasswordChange(Model $targetUser, bool $isSelfChange = true): void
    {
        $desc = $isSelfChange
            ? 'Pengguna mengubah kata sandi mandiri'
            : "Admin memperbarui kata sandi pengguna: {$targetUser->name}";

        $this->record(
            AuditAction::UPDATE,
            $desc,
            $targetUser
        );
    }

    /**
     * Record category audit log.
     */
    public function logCategoryMutation(string|AuditAction $action, Model $category, string $description, ?array $metadata = null): void
    {
        $this->record($action, $description, $category, $metadata);
    }

    /**
     * Record department audit log.
     */
    public function logDepartmentMutation(string|AuditAction $action, Model $department, string $description, ?array $metadata = null): void
    {
        $this->record($action, $description, $department, $metadata);
    }

    /**
     * Record document type audit log.
     */
    public function logDocumentTypeMutation(string|AuditAction $action, Model $docType, string $description, ?array $metadata = null): void
    {
        $this->record($action, $description, $docType, $metadata);
    }

    /**
     * Record archive creation event.
     */
    public function logArchiveCreate(Model $archive): void
    {
        $this->record(
            AuditAction::ARCHIVE_CREATE,
            "Mengunggah arsip baru: {$archive->title} ({$archive->archive_number})",
            $archive,
            [
                'archive_number' => $archive->archive_number,
                'title'          => $archive->title,
                'category_id'    => $archive->category_id,
                'department_id'  => $archive->department_id,
                'year'           => $archive->year,
            ]
        );
    }

    /**
     * Record archive update event.
     */
    public function logArchiveUpdate(Model $archive, array $changes = []): void
    {
        $this->record(
            AuditAction::ARCHIVE_UPDATE,
            "Mengubah metadata arsip: {$archive->title} ({$archive->archive_number})",
            $archive,
            ['changed_fields' => array_keys($changes)]
        );
    }

    /**
     * Record archive download event.
     */
    public function logArchiveDownload(Model $archive): void
    {
        $this->record(
            AuditAction::ARCHIVE_DOWNLOAD,
            "Mengunduh berkas arsip: {$archive->original_filename} ({$archive->archive_number})",
            $archive,
            [
                'archive_id' => $archive->id,
                'filename'   => $archive->original_filename,
            ]
        );
    }

    /**
     * Record archive delete event.
     */
    public function logArchiveDelete(Model $archive): void
    {
        $this->record(
            AuditAction::ARCHIVE_DELETE,
            "Menghapus arsip (soft-delete): {$archive->title} ({$archive->archive_number})",
            $archive
        );
    }

    /**
     * Record archive restore event.
     */
    public function logArchiveRestore(Model $archive): void
    {
        $this->record(
            AuditAction::ARCHIVE_RESTORE,
            "Memulihkan arsip: {$archive->title} ({$archive->archive_number})",
            $archive
        );
    }

    /**
     * Record archive file replacement / version created event.
     */
    public function logArchiveFileReplaced(Model $archive, string $oldFilename, string $newFilename): void
    {
        $this->record(
            AuditAction::ARCHIVE_FILE_REPLACED,
            "Mengganti berkas arsip: {$oldFilename} → {$newFilename} ({$archive->archive_number})",
            $archive,
            [
                'old_filename' => $oldFilename,
                'new_filename' => $newFilename,
            ]
        );
    }

    /**
     * Record version creation event.
     */
    public function logArchiveVersionCreated(Model $archive, Model $version): void
    {
        $this->record(
            AuditAction::ARCHIVE_FILE_REPLACED,
            "Mengunggah berkas versi {$version->version_number} untuk arsip: {$archive->title} ({$archive->archive_number})",
            $archive,
            [
                'archive_number' => $archive->archive_number,
                'version'        => $version->version_number,
                'filename'       => $version->original_filename,
                'change_note'    => $version->change_note,
            ]
        );
    }

    /**
     * Record version restore event.
     */
    public function logArchiveVersionRestored(Model $archive, Model $newVersion, int $fromVersionNumber): void
    {
        $this->record(
            AuditAction::ARCHIVE_RESTORE,
            "Memulihkan berkas dari versi {$fromVersionNumber} menjadi versi {$newVersion->version_number} untuk arsip: {$archive->archive_number}",
            $archive,
            [
                'archive_number'      => $archive->archive_number,
                'from_version'        => $fromVersionNumber,
                'new_version'         => $newVersion->version_number,
                'restored_filename'  => $newVersion->original_filename,
            ]
        );
    }

    /**
     * Record status change event.
     */
    public function logArchiveStatusChanged(Model $archive, string $newStatus): void
    {
        $statusText = $newStatus === 'active' ? 'mengaktifkan' : 'menonaktifkan';
        $this->record(
            AuditAction::ARCHIVE_UPDATE,
            "Admin {$statusText} status operasional arsip: {$archive->title} ({$archive->archive_number})",
            $archive,
            ['status' => $newStatus]
        );
    }
}
