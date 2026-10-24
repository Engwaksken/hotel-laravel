<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class HotelHealthCheck extends Command
{
    protected $signature = 'hotel:health-check {--store : Save a health snapshot}';

    protected $description = 'Check database, storage, queue and recent authentication health.';

    public function handle(): int
    {
        $checks = [];
        $status = 'healthy';

        /*
        |--------------------------------------------------------------------------
        | Database
        |--------------------------------------------------------------------------
        */
        try {
            DB::select('select 1');
            $checks['database'] = 'ok';
        } catch (\Throwable $e) {
            $checks['database'] = 'failed';
            $status = 'critical';
        }

        /*
        |--------------------------------------------------------------------------
        | Storage
        |--------------------------------------------------------------------------
        */
        try {
            $disk = Storage::disk(config('filesystems.default'));

            $probe = 'health/.probe';

            $disk->put(
                $probe,
                now()->toIso8601String()
            );

            if (! $disk->exists($probe)) {
                throw new \RuntimeException('Storage probe could not be verified.');
            }

            $disk->delete($probe);

            $checks['storage'] = 'ok';
        } catch (\Throwable $e) {
            $checks['storage'] = 'failed';

            if ($status !== 'critical') {
                $status = 'degraded';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Queue
        |--------------------------------------------------------------------------
        */
        $pending = Schema::hasTable('jobs')
            ? DB::table('jobs')->count()
            : 0;

        $failed = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->count()
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Security Events
        |--------------------------------------------------------------------------
        |
        | security_events uses occurred_at rather than created_at.
        |
        */
        $failedLogins = Schema::hasTable('security_events')
            ? DB::table('security_events')
                ->where('event_type', 'login_failed')
                ->where('occurred_at', '>=', now()->subDay())
                ->count()
            : 0;

        if ($failed > 0 && $status === 'healthy') {
            $status = 'degraded';
        }

        $payload = [
            'status' => $status,
            'queue_pending' => $pending,
            'queue_failed' => $failed,
            'failed_logins_24h' => $failedLogins,
            'checks' => $checks,
            'checked_at' => now(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Store Snapshot
        |--------------------------------------------------------------------------
        */
        if (
            $this->option('store')
            && Schema::hasTable('system_health_snapshots')
        ) {
            DB::table('system_health_snapshots')->insert([
                'status' => $payload['status'],
                'queue_pending' => $payload['queue_pending'],
                'queue_failed' => $payload['queue_failed'],
                'failed_logins_24h' => $payload['failed_logins_24h'],
                'checks' => json_encode($payload['checks']),
                'checked_at' => $payload['checked_at'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Console Output
        |--------------------------------------------------------------------------
        */
        $this->table(
            ['Metric', 'Value'],
            [
                ['Status', $status],
                ['Database', $checks['database'] ?? 'unknown'],
                ['Storage', $checks['storage'] ?? 'unknown'],
                ['Pending jobs', $pending],
                ['Failed jobs', $failed],
                ['Failed logins / 24h', $failedLogins],
            ]
        );

        return $status === 'critical'
            ? self::FAILURE
            : self::SUCCESS;
    }
}
