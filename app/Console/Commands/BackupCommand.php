<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Exception;

class BackupCommand extends Command
{
    protected $signature   = 'arsipari:backup';
    protected $description = 'Jalankan backup lengkap sistem ARSIPARI (Database SQLite + Berkas Arsip Fisik + Manifest JSON)';

    public function handle(BackupService $backupService): int
    {
        $this->info("==================================================");
        $this->info("  ARSIPARI - PROSES BACKUP SISTEM ARSIP DIGITAL   ");
        $this->info("==================================================");
        $this->line("Waktu Mulai: " . date('Y-m-d H:i:s'));

        try {
            $result = $backupService->createBackup();

            $this->info("✔ Backup Database SQLite: OK");
            $this->info("✔ Backup Berkas Arsip Private: OK ({$result['files_count']} Berkas)");
            $this->info("✔ Generate Manifest JSON: OK");
            $this->info("✔ Kompresi Paket ZIP: OK");
            $this->info("✔ SHA-256 Checksum: {$result['checksum']}");
            $this->newLine();
            $this->info("🎉 BACKUP SELESAI DENGAN SUKSES!");
            $this->line("File Backup : {$result['filename']}");
            $this->line("Lokasi      : {$result['path']}");
            $this->line("Ukuran File : " . round($result['size'] / (1024 * 1024), 2) . " MB");
            $this->newLine();

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("❌ BACKUP GAGAL: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
