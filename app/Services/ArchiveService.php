<?php

namespace App\Services;

use App\Models\Archive;
use App\Models\ArchiveVersion;
use App\Models\Category;
use App\Models\Department;
use App\Models\RetentionPolicy;
use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ArchiveService
{
    public function __construct(
        protected ArchiveNumberService $numberService,
        protected ArchiveFileService $fileService,
        protected RetentionService $retentionService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Create archive record, calculate retention, store version 1 file, and audit.
     */
    public function createArchive(array $metadata, UploadedFile $file, User $user): Archive
    {
        // Validate Category & Department active status
        $category   = Category::findOrFail($metadata['category_id']);
        $department = Department::findOrFail($metadata['department_id']);

        if (! $category->is_active) {
            throw new InvalidArgumentException('Kategori yang dipilih tidak aktif.');
        }

        if (! $department->is_active) {
            throw new InvalidArgumentException('Unit/bidang yang dipilih tidak aktif.');
        }

        $year = (int) ($metadata['year'] ?? date('Y', strtotime($metadata['document_date'])));

        // Calculate retention_until date
        $retentionPolicy = isset($metadata['retention_policy_id'])
            ? RetentionPolicy::find($metadata['retention_policy_id'])
            : null;

        $retentionUntil = $this->retentionService->calculateUntil(
            $metadata['document_date'] ?? null,
            $retentionPolicy
        );

        // Pre-generate archive ID for file path storage structure: archives/{year}/{archiveId}/v1/
        $archiveId = strtolower((string) \Illuminate\Support\Str::ulid());

        // 1. Upload initial file to private storage (v1)
        $fileInfo = $this->fileService->storeVersionFile($file, $year, $archiveId, 1);

        try {
            // 2. DB Transaction
            return DB::transaction(function () use ($metadata, $fileInfo, $user, $year, $retentionUntil, $archiveId) {
                $archiveNumber = $this->numberService->generate($year);

                $archive = Archive::create(array_merge($metadata, $fileInfo, [
                    'id'              => $archiveId,
                    'archive_number'  => $archiveNumber,
                    'year'            => $year,
                    'status'          => 'active',
                    'retention_until' => $retentionUntil,
                    'uploaded_by'     => $user->id,
                ]));

                // Create initial ArchiveVersion (v1)
                ArchiveVersion::create(array_merge($fileInfo, [
                    'archive_id'     => $archive->id,
                    'version_number' => 1,
                    'change_note'    => 'Dokumen awal diunggah',
                    'uploaded_by'    => $user->id,
                ]));

                // Audit log creation
                $this->auditLogService->logArchiveCreate($archive);

                return $archive;
            });
        } catch (Exception $e) {
            // Cleanup on DB failure
            $this->fileService->deleteFile($fileInfo['file_path']);
            throw $e;
        }
    }

    /**
     * Update archive metadata, and create a new version if a file is uploaded.
     */
    public function updateArchive(Archive $archive, array $metadata, ?UploadedFile $file = null, ?string $changeNote = null, ?User $user = null): Archive
    {
        $user = $user ?? auth()->user();

        // Validate Category & Department active status if updated
        if (isset($metadata['category_id'])) {
            $category = Category::findOrFail($metadata['category_id']);
            if (! $category->is_active) {
                throw new InvalidArgumentException('Kategori yang dipilih tidak aktif.');
            }
        }

        if (isset($metadata['department_id'])) {
            $department = Department::findOrFail($metadata['department_id']);
            if (! $department->is_active) {
                throw new InvalidArgumentException('Unit/bidang yang dipilih tidak aktif.');
            }
        }

        // Calculate updated retention_until if date or policy changed
        $documentDate = $metadata['document_date'] ?? ($archive->document_date ? $archive->document_date->format('Y-m-d') : null);
        $policyId     = $metadata['retention_policy_id'] ?? $archive->retention_policy_id;
        $policy       = $policyId ? RetentionPolicy::find($policyId) : null;
        $retentionUntil = $this->retentionService->calculateUntil($documentDate, $policy);

        $year = (int) ($metadata['year'] ?? ($documentDate ? date('Y', strtotime($documentDate)) : $archive->year));

        DB::transaction(function () use ($archive, $metadata, $year, $retentionUntil) {
            $updateData = array_merge($metadata, [
                'year'            => $year,
                'retention_until' => $retentionUntil,
            ]);

            $archive->update($updateData);
            $this->auditLogService->logArchiveUpdate($archive, $updateData);
        });

        // If a new file is uploaded, create a new version
        if ($file) {
            $this->createNewVersion($archive, $file, $changeNote, $user);
        }

        return $archive->refresh();
    }

    /**
     * Create a new version for an archive.
     */
    public function createNewVersion(Archive $archive, UploadedFile $file, ?string $changeNote, User $user): ArchiveVersion
    {
        $nextVersionNum = ((int) $archive->versions()->max('version_number')) + 1;
        $fileInfo = $this->fileService->storeVersionFile($file, $archive->year, $archive->id, $nextVersionNum);

        try {
            return DB::transaction(function () use ($archive, $fileInfo, $nextVersionNum, $changeNote, $user) {
                $version = ArchiveVersion::create(array_merge($fileInfo, [
                    'archive_id'     => $archive->id,
                    'version_number' => $nextVersionNum,
                    'change_note'    => $changeNote ?: "Mengunggah revisi versi {$nextVersionNum}",
                    'uploaded_by'    => $user->id,
                ]));

                // Update legacy file pointer on Archive
                $archive->update($fileInfo);

                // Audit log
                $this->auditLogService->logArchiveVersionCreated($archive, $version);

                return $version;
            });
        } catch (Exception $e) {
            $this->fileService->deleteFile($fileInfo['file_path']);
            throw $e;
        }
    }

    /**
     * Restore an older version by creating a new version pointing to a copy of the old physical file.
     */
    public function restoreVersion(Archive $archive, ArchiveVersion $oldVersion, User $user): ArchiveVersion
    {
        if ($oldVersion->archive_id !== $archive->id) {
            throw new InvalidArgumentException('Versi yang dipilih tidak sesuai dengan arsip target.');
        }

        $nextVersionNum = ((int) $archive->versions()->max('version_number')) + 1;

        // Copy old physical file to new version folder
        $fileInfo = $this->fileService->copyVersionFile(
            $oldVersion->file_path,
            $archive->year,
            $archive->id,
            $nextVersionNum,
            $oldVersion->original_filename
        );

        try {
            return DB::transaction(function () use ($archive, $oldVersion, $fileInfo, $nextVersionNum, $user) {
                $newVersion = ArchiveVersion::create(array_merge($fileInfo, [
                    'archive_id'     => $archive->id,
                    'version_number' => $nextVersionNum,
                    'change_note'    => "Dipulihkan dari versi {$oldVersion->version_number}",
                    'uploaded_by'    => $user->id,
                ]));

                // Update Archive file pointer
                $archive->update($fileInfo);

                // Audit log
                $this->auditLogService->logArchiveVersionRestored($archive, $newVersion, $oldVersion->version_number);

                return $newVersion;
            });
        } catch (Exception $e) {
            $this->fileService->deleteFile($fileInfo['file_path']);
            throw $e;
        }
    }

    /**
     * Toggle archive status (active <-> inactive).
     */
    public function updateStatus(Archive $archive, string $status, User $user): void
    {
        if (! in_array($status, ['active', 'inactive'])) {
            throw new InvalidArgumentException('Status arsip tidak valid.');
        }

        $archive->update(['status' => $status]);
        $this->auditLogService->logArchiveStatusChanged($archive, $status);
    }

    /**
     * Soft delete archive. Physical file is kept intact for recovery.
     */
    public function deleteArchive(Archive $archive): void
    {
        $archive->delete();
        $this->auditLogService->logArchiveDelete($archive);
    }

    /**
     * Restore soft-deleted archive.
     */
    public function restoreArchive(Archive $archive): void
    {
        if (! $this->fileService->fileExists($archive->file_path)) {
            throw new InvalidArgumentException('Berkas arsip tidak ditemukan di penyimpanan fisik. Pemulihan gagal.');
        }

        $archive->restore();
        $archive->update(['status' => 'active']);
        $this->auditLogService->logArchiveRestore($archive);
    }
}
