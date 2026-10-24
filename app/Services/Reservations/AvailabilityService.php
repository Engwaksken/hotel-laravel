<?php

namespace App\Services\Reservations;

use App\Models\Room;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use LogicException;

class AvailabilityService
{
    public function availableRoomIds(
        int $propertyId,
        CarbonInterface $checkIn,
        CarbonInterface $checkOut,
        ?int $excludeReservationId = null
    ): array {
        return Room::query()
            ->where('property_id', $propertyId)
            ->where('is_active', true)
            ->whereNotIn('status', ['maintenance', 'blocked', 'out_of_order'])
            ->whereDoesntHave('reservationRooms', function ($query) use ($checkIn, $checkOut, $excludeReservationId): void {
                $query->whereHas('reservation', function ($reservationQuery) use ($excludeReservationId): void {
                    $reservationQuery->whereIn('status', ['pending', 'confirmed', 'checked_in']);

                    if ($excludeReservationId !== null) {
                        $reservationQuery->where('id', '!=', $excludeReservationId);
                    }
                })
                    ->where('check_in_at', '<', $checkOut)
                    ->where('check_out_at', '>', $checkIn);
            })
            ->pluck('id')
            ->all();
    }

    /**
     * Serialize bookings for a room and check for conflicts using a locking read.
     * Call inside the same transaction that creates the reservation.
     */
    public function lockRoomIfAvailable(
        Room $room,
        CarbonInterface|string $checkIn,
        CarbonInterface|string $checkOut,
        ?int $excludeReservationId = null
    ): bool {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('Room availability must be locked inside a database transaction.');
        }

        $in = $checkIn instanceof CarbonInterface ? $checkIn : Carbon::parse($checkIn);
        $out = $checkOut instanceof CarbonInterface ? $checkOut : Carbon::parse($checkOut);

        $lockedRoom = Room::query()
            ->where('property_id', $room->property_id)
            ->whereKey($room->id)
            ->lockForUpdate()
            ->first();

        if (! $lockedRoom || ! $lockedRoom->is_active) {
            return false;
        }

        $status = $lockedRoom->status->value ?? $lockedRoom->status;

        if (in_array($status, ['maintenance', 'blocked', 'out_of_order'], true)) {
            return false;
        }

        $conflict = DB::table('reservation_rooms as reserved_rooms')
            ->join('reservations', 'reservations.id', '=', 'reserved_rooms.reservation_id')
            ->where('reserved_rooms.room_id', $lockedRoom->id)
            ->where('reservations.property_id', $lockedRoom->property_id)
            ->whereIn('reservations.status', ['pending', 'confirmed', 'checked_in'])
            ->where('reserved_rooms.check_in_at', '<', $out)
            ->where('reserved_rooms.check_out_at', '>', $in)
            ->when(
                $excludeReservationId !== null,
                fn ($query) => $query->where('reservations.id', '!=', $excludeReservationId)
            )
            ->select('reserved_rooms.id')
            ->lockForUpdate()
            ->first();

        return $conflict === null;
    }

    public function roomIsAvailable(
        Room $room,
        CarbonInterface|string $checkIn,
        CarbonInterface|string $checkOut,
        ?int $excludeReservationId = null
    ): bool {
        $in = $checkIn instanceof CarbonInterface ? $checkIn : Carbon::parse($checkIn);
        $out = $checkOut instanceof CarbonInterface ? $checkOut : Carbon::parse($checkOut);
        $status = $room->status->value ?? $room->status;

        if (! $room->is_active || in_array($status, ['maintenance', 'blocked', 'out_of_order'], true)) {
            return false;
        }

        return in_array(
            $room->id,
            $this->availableRoomIds($room->property_id, $in, $out, $excludeReservationId),
            true
        );
    }
}
