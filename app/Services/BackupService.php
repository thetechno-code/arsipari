<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Exception;
use ZipArchive;
use PharData;

class BackupService
{
    protected string $backupDir;
    protected string $sqliteDbPath;
    protected string $archiveStoragePath;

    public function __construct()
    {
        $this->backupDir          = storage_path('app/backups');
        $this->sqliteDbPath       = database_path('database.sqlite');
        $this->archiveStoragePath = storage_path('app/private/archives');
    }

    /**
     * Perform full backup (Database + Uploaded Private Archive Files + Manifest.json -> ZIP/TAR).
     */
    public function createBackup(): array
    {
        if (! File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }

        $timestamp  = date('Y-m-d_His');
        $tempFolder = $this->backupDir . '/temp_' . $timestamp;
        File::makeDirectory($tempFolder, 0755, true);

        try {
            // 1. Copy SQLite Database safely
            $dbBackupFile = $tempFolder . '/database.sqlite';
            if (File::exists($this->sqliteDbPath)) {
                File::copy($this->sqliteDbPath, $dbBackupFile);
            } else {
                throw new Exception("Database SQLite file not found at: {$this->sqliteDbPath}");
            }
            $dbChecksum = hash_file('sha256', $dbBackupFile);

            // 2. Copy Archive Files
            $archiveTempDir = $tempFolder . '/archives';
            File::makeDirectory($archiveTempDir, 0755, true);

            $archiveFileCount = 0;
            if (File::exists($this->archiveStoragePath)) {
                File::copyDirectory($this->archiveStoragePath, $archiveTempDir);
                $allFiles = File::allFiles($archiveTempDir);
                $archiveFileCount = count($allFiles);
            }

            // 3. Generate Manifest JSON
            $manifestData = [
                'application'         => config('arsipari.app_name', 'ARSIPARI'),
                'version'             => config('arsipari.version', '1.0.0'),
                'created_at'          => date('c'),
                'database'            => 'sqlite',
                'database_checksum'   => $dbChecksum,
                'archive_files_count' => $archiveFileCount,
                'environment'         => config('app.env'),
            ];
            File::put($tempFolder . '/manifest.json', json_encode($manifestData, JSON_PRETTY_PRINT));

            // 4. Create Archive Package (ZIP or TAR)
            $zipFilename = "arsipari-backup-{$timestamp}.zip";
            $zipFilePath = $this->backupDir . '/' . $zipFilename;

            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                    throw new Exception("Could not create ZIP backup file at: {$zipFilePath}");
                }

                $zip->addFile($dbBackupFile, 'database.sqlite');
                $zip->addFile($tempFolder . '/manifest.json', 'manifest.json');

                if (File::exists($archiveTempDir)) {
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($archiveTempDir),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    foreach ($files as $name => $file) {
                        if (! $file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = 'archives/' . substr($filePath, strlen($archiveTempDir) + 1);
                            $zip->addFile($filePath, str_replace('\\', '/', $relativePath));
                        }
                    }
                }
                $zip->close();
            } else {
                // Fallback using PharData if native ZipArchive extension is disabled in PHP
                $tarFile = $this->backupDir . "/arsipari-backup-{$timestamp}.tar";
                if (File::exists($tarFile)) File::delete($tarFile);

                $phar = new PharData($tarFile);
                $phar->buildFromDirectory($tempFolder);

                // Convert tar to zip format if possible or rename
                $zipFilePath = $tarFile;
                $zipFilename = basename($tarFile);
            }

            // 5. Cleanup Temp Folder
            File::deleteDirectory($tempFolder);

            // 6. Generate Checksum & enforce retention
            $checksum = hash_file('sha256', $zipFilePath);
            $this->enforceRetentionLimit();

            return [
                'success'      => true,
                'filename'     => $zipFilename,
                'path'         => $zipFilePath,
                'size'         => File::size($zipFilePath),
                'checksum'     => $checksum,
                'files_count'  => $archiveFileCount,
                'manifest'     => $manifestData,
            ];
        } catch (Exception $e) {
            if (File::exists($tempFolder)) {
                File::deleteDirectory($tempFolder);
            }
            throw new Exception("Backup Failed: " . $e->getMessage());
        }
    }

    /**
     * Get list of backups stored in storage/app/backups.
     */
    public function listBackups(): array
    {
        if (! File::exists($this->backupDir)) {
            return [];
        }

        $files = array_merge(
            File::glob($this->backupDir . '/*.zip'),
            File::glob($this->backupDir . '/*.tar')
        );
        usort($files, fn($a, $b) => File::lastModified($b) <=> File::lastModified($a));

        $backups = [];
        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $size     = File::size($filePath);
            $modified = File::lastModified($filePath);

            $manifest = $this->readManifestFromPackage($filePath);

            $backups[] = [
                'filename'       => $filename,
                'path'           => $filePath,
                'size'           => $size,
                'size_formatted' => $this->formatBytes($size),
                'created_at'     => date('Y-m-d H:i:s', $modified),
                'manifest'       => $manifest,
                'is_emergency'   => str_contains($filename, 'pre-restore'),
            ];
        }

        return $backups;
    }

    /**
     * Delete backup package file.
     */
    public function deleteBackup(string $filename): bool
    {
        $filePath = $this->getSanitizedBackupPath($filename);

        $backups = $this->listBackups();
        $normalBackups = array_values(array_filter($backups, fn($b) => !$b['is_emergency']));

        if (count($normalBackups) === 1 && $normalBackups[0]['filename'] === $filename) {
            throw new Exception("Tidak dapat menghapus file backup terbaru/satu-satunya. Sistem memerlukan minimal 1 backup untuk keselamatan data.");
        }

        if (File::exists($filePath)) {
            return File::delete($filePath);
        }

        return false;
    }

    /**
     * Restore database & archive files from backup package.
     */
    public function restoreBackup(string $filename): array
    {
        $backupFilePath = $this->getSanitizedBackupPath($filename);

        if (! File::exists($backupFilePath)) {
            throw new Exception("File backup tidak ditemukan: {$filename}");
        }

        // 1. Create Emergency Pre-Restore Backup first!
        $emergencyResult = $this->createBackup();
        $emergencyName   = $emergencyResult['filename'];

        $timestamp  = date('Y-m-d_His');
        $extractDir = $this->backupDir . '/restore_' . $timestamp;
        File::makeDirectory($extractDir, 0755, true);

        try {
            // 2. Extract archive package
            if (str_ends_with($filename, '.zip') && class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($backupFilePath) !== true) {
                    throw new Exception("Tidak dapat membuka file ZIP backup: {$filename}");
                }
                $zip->extractTo($extractDir);
                $zip->close();
            } elseif (class_exists('PharData')) {
                $phar = new PharData($backupFilePath);
                $phar->extractTo($extractDir, null, true);
            } else {
                throw new Exception("Tidak ada extractor terinstal untuk membuka file backup.");
            }

            // 3. Verify extracted contents
            $extractedDb = $extractDir . '/database.sqlite';
            if (! File::exists($extractedDb)) {
                throw new Exception("File database.sqlite tidak ditemukan dalam paket backup!");
            }

            // 4. Restore SQLite Database
            File::copy($extractedDb, $this->sqliteDbPath);

            // 5. Restore Archive Files
            $extractedArchives = $extractDir . '/archives';
            if (File::exists($extractedArchives)) {
                if (File::exists($this->archiveStoragePath)) {
                    File::cleanDirectory($this->archiveStoragePath);
                } else {
                    File::makeDirectory($this->archiveStoragePath, 0755, true);
                }
                File::copyDirectory($extractedArchives, $this->archiveStoragePath);
            }

            // 6. Cleanup Extract Dir
            File::deleteDirectory($extractDir);

            return [
                'success'          => true,
                'restored_from'    => $filename,
                'emergency_backup' => $emergencyName,
            ];
        } catch (Exception $e) {
            if (File::exists($extractDir)) {
                File::deleteDirectory($extractDir);
            }
            throw new Exception("Restore Gagal: " . $e->getMessage());
        }
    }

    /**
     * Enforce backup retention limit.
     */
    public function enforceRetentionLimit(): void
    {
        $limit = (int) config('arsipari.backup_retention', 7);
        $backups = $this->listBackups();

        $normalBackups = array_values(array_filter($backups, fn($b) => !$b['is_emergency']));

        if (count($normalBackups) > $limit) {
            $toDelete = array_slice($normalBackups, $limit);
            foreach ($toDelete as $b) {
                File::delete($b['path']);
            }
        }
    }

    /**
     * Sanitize path to prevent Path Traversal attacks (../).
     */
    public function getSanitizedBackupPath(string $filename): string
    {
        $basename = basename($filename);
        if ($basename !== $filename || str_contains($filename, '..')) {
            throw new Exception("Invalid backup filename detected (Path Traversal Protection).");
        }
        return $this->backupDir . '/' . $basename;
    }

    protected function readManifestFromPackage(string $filePath): ?array
    {
        try {
            if (str_ends_with($filePath, '.zip') && class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($filePath) === true) {
                    $stream = $zip->getStream('manifest.json');
                    if ($stream) {
                        $content = stream_get_contents($stream);
                        fclose($stream);
                        $zip->close();
                        return json_decode($content, true);
                    }
                    $zip->close();
                }
            } elseif (class_exists('PharData')) {
                $phar = new PharData($filePath);
                if (isset($phar['manifest.json'])) {
                    return json_decode($phar['manifest.json']->getContent(), true);
                }
            }
        } catch (Exception $e) {
            return null;
        }
        return null;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }
}
