<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Console\Command;

class SendArrivalReminders extends Command
{
    use ConcernsWithOperationalNotifications;

    protected $signature = 'hotel:arrival-reminders {--hours=2 : Look-ahead window in hours}';
    protected $description = 'Notify front-office and hotel management of upcoming guest arrivals.';

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $from = now();
        $to = now()->copy()->addHours($hours);
        $sent = 0;
        $reservations = Reservation::query()
            ->with(['guest', 'property', 'rooms.room'])
            ->whereIn('status', [ReservationStatus::Pending->value, ReservationStatus::Confirmed->value])
            ->whereBetween('check_in_at', [$from, $to])
            ->orderBy('check_in_at')
            ->get();

        foreach ($reservations as $reservation) {
            $guest = trim(($reservation->guest?->first_name ?? '').' '.($reservation->guest?->last_name ?? '')) ?: 'Guest';
            $rooms = $reservation->rooms->pluck('room.room_number')->filter()->implode(', ');
            $eventKey = 'arrival:'.$reservation->check_in_at->format('YmdHi');

            foreach ($this->operationalRecipients($reservation->property_id, ['front_office_manager', 'receptionist', 'reservation_officer']) as $user) {
                if ($this->notifyOnce($user, $reservation, 'arrival_reminder', $eventKey, [
                    'event' => 'arrival_reminder',
                    'subject' => 'Upcoming arrival: '.$guest,
                    'message' => $guest.' is expected to arrive at '.$reservation->check_in_at->format('h:i A').'.',
                    'detail' => 'Booking '.$reservation->reference.($rooms ? ' · Room(s): '.$rooms : ''),
                    'url' => url('/reservations/'.$reservation->id),
                    'action' => 'Open reservation',
                    'icon' => 'fa-plane-arrival',
                ])) {
                    $sent++;
                }
            }
        }

        $this->info("Checked {$reservations->count()} upcoming arrival(s); sent {$sent} notification(s).");
        return self::SUCCESS;
    }
}
