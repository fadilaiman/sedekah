<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database
                            {--retain=30 : Number of days to retain backups}
                            {--dry-run   : Show what would be backed up without doing it}';

    protected $description = 'Backup the database and prune backups older than retain days';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $retain = (int) $this->option('retain');
        $connection = config('database.default');

        $this->info("Database backup — connection: {$connection}");

        match ($connection) {
            'mysql'  => $this->backupMysql($dryRun),
            'sqlite' => $this->backupSqlite($dryRun),
            default  => $this->error("Unsupported connection: {$connection}") && exit(1),
        };

        $this->pruneOldBackups($retain, $dryRun);

        return self::SUCCESS;
    }

    private function backupMysql(bool $dryRun): void
    {
        $host     = config('database.connections.mysql.host', '127.0.0.1');
        $port     = config('database.connections.mysql.port', 3306);
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $filename = 'backups/' . Carbon::now()->format('Y-m-d_H-i-s') . '_mysql.sql.gz';
        $storagePath = storage_path("app/{$filename}");

        $this->ensureBackupDir();

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s | gzip > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($storagePath),
        );

        if ($dryRun) {
            $this->line("[dry-run] Would run: mysqldump ... | gzip > {$storagePath}");
            return;
        }

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('mysqldump failed. Check database credentials and mysqldump availability.');
            exit(1);
        }

        $sizeMb = round(filesize($storagePath) / 1024 / 1024, 2);
        $this->info("Backup created: {$filename} ({$sizeMb} MB)");
    }

    private function backupSqlite(bool $dryRun): void
    {
        $source   = config('database.connections.sqlite.database');
        $filename = 'backups/' . Carbon::now()->format('Y-m-d_H-i-s') . '_sqlite.db';
        $dest     = storage_path("app/{$filename}");

        $this->ensureBackupDir();

        if ($dryRun) {
            $this->line("[dry-run] Would copy: {$source} → {$dest}");
            return;
        }

        if (! file_exists($source)) {
            $this->error("SQLite database not found at: {$source}");
            exit(1);
        }

        copy($source, $dest);

        $sizeMb = round(filesize($dest) / 1024 / 1024, 2);
        $this->info("Backup created: {$filename} ({$sizeMb} MB)");
    }

    private function pruneOldBackups(int $retainDays, bool $dryRun): void
    {
        $backupDir = storage_path('app/backups');

        if (! is_dir($backupDir)) {
            return;
        }

        $cutoff = Carbon::now()->subDays($retainDays);
        $pruned = 0;

        foreach (glob("{$backupDir}/*") as $file) {
            if (filemtime($file) < $cutoff->timestamp) {
                if ($dryRun) {
                    $this->line('[dry-run] Would delete: ' . basename($file));
                } else {
                    unlink($file);
                    $pruned++;
                }
            }
        }

        if (! $dryRun && $pruned > 0) {
            $this->info("Pruned {$pruned} backup(s) older than {$retainDays} days.");
        }
    }

    private function ensureBackupDir(): void
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
