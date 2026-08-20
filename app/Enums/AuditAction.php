<?php

namespace App\Enums;

enum AuditAction: string
{
    case LOGIN                   = 'login';
    case LOGOUT                  = 'logout';
    case CREATE                  = 'create';
    case UPDATE                  = 'update';
    case DOWNLOAD                = 'download';
    case DELETE                  = 'delete';
    case RESTORE                 = 'restore';
    case USER_CREATE             = 'user_create';
    case USER_UPDATE             = 'user_update';
    case USER_DELETE             = 'user_delete';
    case CATEGORY_CREATE         = 'category_create';
    case CATEGORY_UPDATE         = 'category_update';
    case CATEGORY_DELETE         = 'category_delete';
    case DEPARTMENT_CREATE       = 'department_create';
    case DEPARTMENT_UPDATE       = 'department_update';
    case DEPARTMENT_DELETE       = 'department_delete';
    case DOCUMENT_TYPE_CREATE    = 'document_type_create';
    case DOCUMENT_TYPE_UPDATE    = 'document_type_update';
    case DOCUMENT_TYPE_DELETE    = 'document_type_delete';
    case RETENTION_POLICY_CREATE = 'retention_policy_create';
    case RETENTION_POLICY_UPDATE = 'retention_policy_update';
    case ARCHIVE_CREATE          = 'archive_create';
    case ARCHIVE_UPDATE          = 'archive_update';
    case ARCHIVE_DOWNLOAD        = 'archive_download';
    case ARCHIVE_DELETE          = 'archive_delete';
    case ARCHIVE_RESTORE         = 'archive_restore';
    case ARCHIVE_FILE_REPLACED   = 'archive_file_replaced';
    case REPORT_EXPORTED_EXCEL   = 'report_exported_excel';
    case REPORT_EXPORTED_PDF     = 'report_exported_pdf';

    public function label(): string
    {
        return match ($this) {
            self::LOGIN                   => 'Login',
            self::LOGOUT                  => 'Logout',
            self::CREATE                  => 'Membuat',
            self::UPDATE                  => 'Mengubah',
            self::DOWNLOAD                => 'Mengunduh',
            self::DELETE                  => 'Menghapus',
            self::RESTORE                 => 'Memulihkan',
            self::USER_CREATE             => 'Tambah User',
            self::USER_UPDATE             => 'Ubah User',
            self::USER_DELETE             => 'Hapus User',
            self::CATEGORY_CREATE         => 'Tambah Kategori',
            self::CATEGORY_UPDATE         => 'Ubah Kategori',
            self::CATEGORY_DELETE         => 'Hapus Kategori',
            self::DEPARTMENT_CREATE       => 'Tambah Departemen',
            self::DEPARTMENT_UPDATE       => 'Ubah Departemen',
            self::DEPARTMENT_DELETE       => 'Hapus Departemen',
            self::DOCUMENT_TYPE_CREATE    => 'Tambah Jenis Dokumen',
            self::DOCUMENT_TYPE_UPDATE    => 'Ubah Jenis Dokumen',
            self::DOCUMENT_TYPE_DELETE    => 'Hapus Jenis Dokumen',
            self::RETENTION_POLICY_CREATE => 'Tambah Retensi',
            self::RETENTION_POLICY_UPDATE => 'Ubah Retensi',
            self::ARCHIVE_CREATE          => 'Tambah Arsip',
            self::ARCHIVE_UPDATE          => 'Ubah Arsip',
            self::ARCHIVE_DOWNLOAD        => 'Unduh Arsip',
            self::ARCHIVE_DELETE          => 'Hapus Arsip',
            self::ARCHIVE_RESTORE         => 'Pulihkan Arsip',
            self::ARCHIVE_FILE_REPLACED   => 'Ganti File Arsip',
            self::REPORT_EXPORTED_EXCEL   => 'Export Laporan Excel',
            self::REPORT_EXPORTED_PDF     => 'Export Laporan PDF',
        };
    }
}
