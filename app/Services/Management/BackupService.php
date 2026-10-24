<?php

namespace App\Services\Management;

use App\Jobs\RunHotelBackup;
use App\Models\BackupRun;

class BackupService
{
    public function request(?int $propertyId, ?int $userId, string $type = 'database'): BackupRun
    {
        $run = BackupRun::create([
            'property_id' => $propertyId,
            'type' => $type,
            'status' => 'queued',
            'requested_by' => $userId,
            'disk' => config('filesystems.default', 'local'),
        ]);
        RunHotelBackup::dispatch($run->id)->onQueue('backups');
        return $run;
    }

    public function markFailed(BackupRun $run, string $message): void
    {
        $run->update(['status' => 'failed', 'message' => $message, 'finished_at' => now()]);
    }
}
