<?php

namespace App\Console\Commands;

use App\Support\DatabaseBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    protected $signature = 'ogera:db-backup {--list : Show the backups currently kept}';

    protected $description = 'Write a compressed database backup and prune anything past the retention window';

    public function handle(DatabaseBackup $backup)
    {
        if ($this->option('list')) {
            return $this->listArchives($backup);
        }

        try {
            $archive = $backup->run();
        } catch (\Throwable $e) {
            Log::error('Database backup failed: ' . $e->getMessage());
            $this->error($e->getMessage());

            return 1;
        }

        $this->info(sprintf(
            'Backup written: %s (%s)',
            basename($archive),
            $this->humanSize(filesize($archive))
        ));
        Log::info('Database backup written: ' . basename($archive));

        return 0;
    }

    private function listArchives(DatabaseBackup $backup)
    {
        $archives = $backup->archives();

        if (empty($archives)) {
            $this->warn('No backups yet.');

            return 0;
        }

        $rows = [];
        foreach ($archives as $archive) {
            $rows[] = [
                basename($archive),
                date('Y-m-d H:i', filemtime($archive)),
                $this->humanSize(filesize($archive)),
            ];
        }

        $this->table(['Archive', 'Taken', 'Size'], $rows);
        $this->line(sprintf('%d kept in %s', count($archives), $backup->directory()));

        return 0;
    }

    private function humanSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;
        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 1) . ' ' . $units[$index];
    }
}
