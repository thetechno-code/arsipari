<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Services\AuditLogService;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Exception;

class BackupController extends Controller
{
    public function __construct(
        protected BackupService $backupService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Display list of backups (Admin Only).
     */
    public function index()
    {
        $backups = $this->backupService->listBackups();
        $retentionLimit = config('arsipari.backup_retention', 7);

        return view('admin.backups.index', compact('backups', 'retentionLimit'));
    }

    /**
     * Trigger manual backup from Web UI.
     */
    public function store(Request $request)
    {
        try {
            $result = $this->backupService->createBackup();

            $this->auditLogService->record(
                AuditAction::CREATE,
                "Membuat backup sistem manual ({$result['filename']})",
                null,
                ['filename' => $result['filename'], 'size' => $result['size']]
            );

            return redirect()->route('admin.backups.index')
                ->with('success', "Backup sistem berhasil dibuat: {$result['filename']} ({$this->formatBytes($result['size'])}).");
        } catch (Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', "Gagal membuat backup: " . $e->getMessage());
        }
    }

    /**
     * Securely download backup ZIP package.
     */
    public function download(string $filename)
    {
        try {
            $path = $this->backupService->getSanitizedBackupPath($filename);

            if (! file_exists($path)) {
                return back()->with('error', "File backup tidak ditemukan.");
            }

            $this->auditLogService->record(
                AuditAction::DOWNLOAD,
                "Mengunduh file backup sistem ({$filename})",
                null,
                ['filename' => $filename]
            );

            return response()->download($path, $filename, [
                'Content-Type' => 'application/zip',
            ]);
        } catch (Exception $e) {
            return back()->with('error', "Gagal mengunduh backup: " . $e->getMessage());
        }
    }

    /**
     * Delete a backup package ZIP file.
     */
    public function destroy(string $filename)
    {
        try {
            $deleted = $this->backupService->deleteBackup($filename);

            if ($deleted) {
                $this->auditLogService->record(
                    AuditAction::DELETE,
                    "Menghapus file backup sistem ({$filename})",
                    null,
                    ['filename' => $filename]
                );

                return redirect()->route('admin.backups.index')
                    ->with('success', "File backup \"{$filename}\" berhasil dihapus.");
            }

            return redirect()->route('admin.backups.index')
                ->with('error', "File backup tidak ditemukan atau gagal dihapus.");
        } catch (Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', $e->getMessage());
        }
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }
}
