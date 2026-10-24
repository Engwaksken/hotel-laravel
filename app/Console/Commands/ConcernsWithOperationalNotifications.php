<?php

namespace App\Console\Commands;

use App\Models\SystemCommandNotificationLog;
use App\Models\User;
use App\Notifications\HotelOperationalAlertNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait ConcernsWithOperationalNotifications
{
    protected function operationalRecipients(int $propertyId, array $roles): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->where(function ($query) use ($propertyId) {
                $query->where('property_id', $propertyId)
                    ->orWhere(function ($global) {
                        $global->whereNull('property_id')
                            ->whereIn('role', ['super_admin', 'hotel_owner']);
                    });
            })
            ->whereIn('role', array_values(array_unique(array_merge(
                $roles,
                ['branch_manager', 'general_manager', 'admin', 'super_admin', 'hotel_owner']
            ))))
            ->get();
    }

    protected function notifyOnce(
        User $user,
        Model $subject,
        string $eventType,
        string $eventKey,
        array $alert
    ): bool {
        try {
            return (bool) DB::transaction(function () use ($user, $subject, $eventType, $eventKey, $alert) {
                $log = SystemCommandNotificationLog::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'event_type' => $eventType,
                        'subject_type' => $subject::class,
                        'subject_id' => $subject->getKey(),
                        'event_key' => $eventKey,
                    ],
                    [
                        'property_id' => $subject->property_id ?? $user->property_id,
                        'sent_at' => now(),
                    ]
                );

                if (! $log->wasRecentlyCreated) {
                    return false;
                }

                $user->notify(new HotelOperationalAlertNotification($alert + [
                    'subject_type' => $subject::class,
                    'subject_id' => $subject->getKey(),
                ]));

                return true;
            });
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }
}
