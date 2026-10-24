<?php

namespace App\Services\Guest;

use App\Models\BookingAddon;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Reservations\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OnlineBookingService
{
    public function __construct(private AvailabilityService $availability)
    {
    }

    public function create(array $data): Reservation
    {
        return DB::transaction(function () use ($data): Reservation {
            $checkIn = Carbon::parse($data['check_in'])->setTime(14, 0);
            $checkOut = Carbon::parse($data['check_out'])->setTime(11, 0);

            if ($checkOut->lte($checkIn)) {
                throw ValidationException::withMessages([
                    'check_out' => 'Check-out must be after check-in.',
                ]);
            }

            $propertyId = (int) $data['property_id'];
            $roomType = RoomType::query()
                ->where('property_id', $propertyId)
                ->whereKey($data['room_type_id'])
                ->where('is_active', true)
                ->firstOrFail();

            if (
                (int) ($data['adults'] ?? 1) > (int) $roomType->max_adults
                || (int) ($data['children'] ?? 0) > (int) $roomType->max_children
            ) {
                throw ValidationException::withMessages([
                    'room_type_id' => 'The selected room type cannot accommodate that number of guests.',
                ]);
            }

            // Lock candidate rooms one at a time. This serializes competing
            // bookings for the same room before checking reservation overlaps.
            $room = null;

            $candidates = Room::query()
                ->where('property_id', $propertyId)
                ->where('room_type_id', $roomType->id)
                ->where('is_active', true)
                ->orderBy('room_number')
                ->get();

            foreach ($candidates as $candidate) {
                if ($this->availability->lockRoomIfAvailable($candidate, $checkIn, $checkOut)) {
                    $room = $candidate;
                    break;
                }
            }

            if (! $room) {
                throw ValidationException::withMessages([
                    'room_type_id' => 'No room of this type is available for the selected dates.',
                ]);
            }

            $guest = Guest::firstOrCreate(
                ['property_id' => $propertyId, 'email' => $data['email']],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'nationality' => $data['nationality'] ?? null,
                ]
            );

            $nights = max(1, $checkIn->diffInDays($checkOut));
            $subtotal = (float) $roomType->base_rate * $nights;
            $addonTotal = 0.0;
            $addons = [];

            foreach ($data['addons'] ?? [] as $id => $quantity) {
                if ((int) $quantity <= 0) {
                    continue;
                }

                $addon = BookingAddon::query()
                    ->where('property_id', $propertyId)
                    ->whereKey($id)
                    ->where('is_active', true)
                    ->first();

                if (! $addon) {
                    continue;
                }

                $addons[] = [$addon, (int) $quantity];
                $addonTotal += (float) $addon->price * (int) $quantity;
            }

            $total = $subtotal + $addonTotal;
            $reservation = Reservation::create([
                'property_id' => $propertyId,
                'guest_id' => $guest->id,
                'reference' => 'WEB-'.strtoupper(Str::random(10)),
                'status' => 'confirmed',
                'source' => 'website',
                'check_in_at' => $checkIn,
                'check_out_at' => $checkOut,
                'adults' => $data['adults'] ?? 1,
                'children' => $data['children'] ?? 0,
                'subtotal' => $total,
                'grand_total' => $total,
                'deposit_required' => $total * ((float) ($data['deposit_percent'] ?? 30) / 100),
                'special_requests' => $data['special_requests'] ?? null,
            ]);

            ReservationRoom::create([
                'reservation_id' => $reservation->id,
                'room_id' => $room->id,
                'room_type_id' => $roomType->id,
                'nightly_rate' => $roomType->base_rate,
                'adults' => $data['adults'] ?? 1,
                'children' => $data['children'] ?? 0,
                'check_in_at' => $checkIn,
                'check_out_at' => $checkOut,
            ]);

            foreach ($addons as [$addon, $quantity]) {
                DB::table('reservation_addons')->insert([
                    'reservation_id' => $reservation->id,
                    'booking_addon_id' => $addon->id,
                    'quantity' => $quantity,
                    'unit_price' => $addon->price,
                    'total' => (float) $addon->price * $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $reservation->load('guest', 'rooms.room');
        });
    }
}
