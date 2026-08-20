<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Exception;

class RestoreCommand extends Command
{
    protected $signature   = 'arsipari:restore {backup_file? : Nama file backup ZIP yang akan dipulihkan}';
    protected $description = 'Pulihkan database SQLite dan berkas arsip dari file paket backup ZIP';

    public function handle(BackupService $backupService): int
    {
        $this->warn("==================================================");
        $this->warn("   WARNING: PROSES PEMULIHAN SISTEM (RESTORE)     ");
        $this->warn("==================================================");

        $backupFile = $this->argument('backup_file');

        if (! $backupFile) {
            $backups = $backupService->listBackups();
            if (empty($backups)) {
                $this->error("Tidak ada file paket backup yang tersedia di storage/app/backups!");
                return Command::FAILURE;
            }

            $choices = array_map(fn($b) => $b['filename'] . " ({$b['size_formatted']} - {$b['created_at']})", $backups);
            $selected = $this->choice("Pilih file paket backup yang ingin dipulihkan:", $choices);

            $index = array_search($selected, $choices);
            $backupFile = $backups[$index]['filename'];
        }

        $this->alert("PERHATIAN: Proses ini akan MENIMPA database SQLite dan seluruh berkas arsip yang ada saat ini!");
        $this->info("Sistem akan secara otomatis membuat Emergency Pre-Restore Backup sebelum pemulihan dijalankan.");

        $confirm = $this->ask("Ketik kata 'RESTORE' (huruf kapital) untuk melanjutkan pemulihan data:");

        if (trim($confirm) !== 'RESTORE') {
            $this->info("Proses pemulihan dibatalkan oleh pengguna.");
            return Command::SUCCESS;
        }

        $this->line("Memproses pemulihan data dari: {$backupFile}...");

        try {
            $result = $backupService->restoreBackup($backupFile);

            $this->info("✔ Emergency Backup Dibuat : {$result['emergency_backup']}");
            $this->info("✔ Database SQLite Restored  : OK");
            $this->info("✔ Private Archive Files     : OK");
            $this->newLine();
            $this->info("🎉 PEMULIHAN SISTEM (RESTORE) SELESAI DENGAN SUKSES!");
            $this->line("Dipulihkan dari : {$result['restored_from']}");
            $this->newLine();

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("❌ PEMULIHAN (RESTORE) GAGAL: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
