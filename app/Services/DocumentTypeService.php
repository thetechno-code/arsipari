<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\Archive;
use App\Models\DocumentType;
use InvalidArgumentException;

class DocumentTypeService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function createDocumentType(array $data): DocumentType
    {
        $data['code'] = strtoupper(trim($data['code']));

        $docType = DocumentType::create($data);

        $this->auditLogService->logDocumentTypeMutation(
            AuditAction::DOCUMENT_TYPE_CREATE,
            $docType,
            "Membuat jenis dokumen: {$docType->name} ({$docType->code})"
        );

        return $docType;
    }

    public function updateDocumentType(DocumentType $docType, array $data): DocumentType
    {
        $data['code'] = strtoupper(trim($data['code']));
        $oldData = $docType->only(['name', 'code', 'description', 'is_active']);

        $docType->update($data);

        $this->auditLogService->logDocumentTypeMutation(
            AuditAction::DOCUMENT_TYPE_UPDATE,
            $docType,
            "Mengubah jenis dokumen: {$docType->name}",
            ['old' => $oldData, 'new' => $data]
        );

        return $docType;
    }

    public function toggleStatus(DocumentType $docType): bool
    {
        $newStatus = ! $docType->is_active;
        $docType->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'mengaktifkan' : 'menonaktifkan';
        $this->auditLogService->logDocumentTypeMutation(
            AuditAction::DOCUMENT_TYPE_UPDATE,
            $docType,
            "Admin {$statusText} jenis dokumen: {$docType->name}",
            ['is_active' => $newStatus]
        );

        return $newStatus;
    }

    public function deleteDocumentType(DocumentType $docType): void
    {
        $usedInArchives = Archive::where('document_type', strtolower($docType->code))
            ->orWhere('document_type', strtoupper($docType->code))
            ->count();

        if ($usedInArchives > 0) {
            throw new InvalidArgumentException('Jenis dokumen tidak dapat dihapus karena masih digunakan oleh arsip.');
        }

        $name = $docType->name;
        $docType->delete();

        $this->auditLogService->logDocumentTypeMutation(
            AuditAction::DOCUMENT_TYPE_DELETE,
            $docType,
            "Menghapus jenis dokumen: {$name}"
        );
    }
}
