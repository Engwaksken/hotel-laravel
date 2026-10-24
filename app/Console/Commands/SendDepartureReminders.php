<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Console\Command;

class SendDepartureReminders extends Command
{
    use ConcernsWithOperationalNotifications;

    protected $signature = 'hotel:departure-reminders {--hours=1 : Look-ahead window in hours}';
    protected $description = 'Notify front-office and hotel management of upcoming guest departures.';

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $from = now();
        $to = now()->copy()->addHours($hours);
        $sent = 0;

        $reservations = Reservation::query()
            ->with(['guest', 'property', 'rooms.room', 'folio'])
            ->where('status', ReservationStatus::CheckedIn->value)
            ->whereBetween('check_out_at', [$from, $to])
            ->orderBy('check_out_at')
            ->get();

        foreach ($reservations as $reservation) {
            $guest = trim(($reservation->guest?->first_name ?? '').' '.($reservation->guest?->last_name ?? '')) ?: 'Guest';
            $rooms = $reservation->rooms->pluck('room.room_number')->filter()->implode(', ');
            $balance = (float) ($reservation->folio?->balance ?? 0);
            $eventKey = 'departure:'.$reservation->check_out_at->format('YmdHi');

            foreach ($this->operationalRecipients($reservation->property_id, ['front_office_manager', 'receptionist', 'cashier']) as $user) {
                if ($this->notifyOnce($user, $reservation, 'departure_reminder', $eventKey, [
                    'event' => 'departure_reminder',
                    'subject' => 'Upcoming departure: '.$guest,
                    'message' => $guest.' is due to check out at '.$reservation->check_out_at->format('h:i A').'.',
                    'detail' => 'Booking '.$reservation->reference.($rooms ? ' · Room(s): '.$rooms : '').' · Folio balance: '.number_format($balance, 0).' UGX',
                    'url' => url('/reservations/'.$reservation->id),
                    'action' => 'Review checkout',
                    'icon' => 'fa-plane-departure',
                ])) {
                    $sent++;
                }
            }
        }

        $this->info("Checked {$reservations->count()} upcoming departure(s); sent {$sent} notification(s).");
        return self::SUCCESS;
    }
}
