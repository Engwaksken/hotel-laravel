<?php

namespace App\Jobs;

use App\Models\BackupRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

class RunHotelBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries = 2;

    public function __construct(public int $backupRunId) {}

    public function handle(): void
    {
        $run = BackupRun::findOrFail($this->backupRunId);
        $run->update(['status' => 'running', 'started_at' => now(), 'message' => null]);
        $tmp = null;

        try {
            $disk = $run->disk ?: config('filesystems.default', 'local');
            $stamp = now()->format('Ymd_His');
            $relative = 'hotel-backups/'.($run->property_id ?: 'group')."/{$stamp}_{$run->id}";
            $tmp = storage_path("app/tmp/hms-backup-{$run->id}");
            if (!is_dir($tmp) && !mkdir($tmp, 0770, true) && !is_dir($tmp)) {
                throw new RuntimeException('Cannot create temporary backup directory.');
            }

            $files = [];
            if (in_array($run->type, ['database', 'full'], true)) {
                $dbFile = "$tmp/database.sql";
                $cfg = config('database.connections.'.config('database.default'));
                if (($cfg['driver'] ?? null) !== 'mysql') {
                    throw new RuntimeException('Phase 13 database backup worker currently supports MySQL connections.');
                }
                $cmd = ['mysqldump', '--single-transaction', '--quick', '--skip-lock-tables', '-h', (string) $cfg['host'], '-P', (string) ($cfg['port'] ?? 3306), '-u', (string) $cfg['username']];
                if (!empty($cfg['password'])) $cmd[] = '--password='.(string) $cfg['password'];
                $cmd[] = (string) $cfg['database'];
                $process = new Process($cmd); $process->setTimeout(600); $process->run();
                if (!$process->isSuccessful()) throw new RuntimeException('Database backup failed: '.$process->getErrorOutput());
                file_put_contents($dbFile, $process->getOutput());
                $files[] = $dbFile;
            }

            if (in_array($run->type, ['files', 'full'], true)) {
                $filesArchive = "$tmp/files.tar.gz";
                $sources = array_values(array_filter(['public', is_dir(base_path('storage/app/public')) ? 'storage/app/public' : null]));
                $process = new Process(array_merge(['tar', '-czf', $filesArchive, '-C', base_path()], $sources));
                $process->setTimeout(600); $process->run();
                if (!$process->isSuccessful()) throw new RuntimeException('Files backup failed: '.$process->getErrorOutput());
                $files[] = $filesArchive;
            }

            $manifest = "$tmp/manifest.json";
            file_put_contents($manifest, json_encode([
                'backup_run_id' => $run->id,
                'created_at' => now()->toIso8601String(),
                'type' => $run->type,
                'files' => array_map('basename', $files),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $files[] = $manifest;

            $archive = "$tmp/hms-backup.tar.gz";
            $process = new Process(array_merge(['tar', '-czf', $archive, '-C', $tmp], array_map('basename', $files)));
            $process->setTimeout(600); $process->run();
            if (!$process->isSuccessful() || !is_file($archive)) throw new RuntimeException('Final backup archive could not be created.');

            $path = "$relative.tar.gz";
            $stream = fopen($archive, 'rb');
            if (!$stream || !Storage::disk($disk)->put($path, $stream)) throw new RuntimeException('Backup upload to configured storage failed.');
            if (is_resource($stream)) fclose($stream);
            if (!Storage::disk($disk)->exists($path)) throw new RuntimeException('Backup archive was not found on configured storage after upload.');

            $checksum = hash_file('sha256', $archive);
            $size = filesize($archive);
            $run->update([
                'status' => 'successful', 'path' => $path, 'checksum' => $checksum,
                'size_bytes' => $size, 'finished_at' => now(), 'verified_at' => now(),
                'message' => 'Backup written and verified on configured storage.',
            ]);
        } catch (\Throwable $e) {
            $run->update(['status' => 'failed', 'finished_at' => now(), 'message' => mb_substr($e->getMessage(), 0, 2000)]);
            throw $e;
        } finally {
            if ($tmp && is_dir($tmp)) {
                $process = new Process(['rm', '-rf', $tmp]);
                $process->run();
            }
        }
    }
}
