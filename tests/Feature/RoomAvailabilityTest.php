<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Guest\OnlineBookingService;
use App\Services\Reservations\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RoomAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_locking_availability_check_rejects_overlapping_stay_but_allows_checkout_boundary(): void
    {
        $property = Property::factory()->create();
        $roomType = RoomType::create([
            'property_id' => $property->id,
            'name' => 'Standard',
            'code' => 'STD',
            'base_rate' => 100,
            'is_active' => true,
        ]);
        $room = Room::create([
            'property_id' => $property->id,
            'room_type_id' => $roomType->id,
            'room_number' => '101',
            'status' => 'available',
            'is_active' => true,
        ]);

        $checkIn = now()->addDays(10)->setTime(14, 0);
        $checkOut = $checkIn->copy()->addDay()->setTime(11, 0);
        $reservation = Reservation::factory()->create([
            'property_id' => $property->id,
            'status' => 'confirmed',
            'check_in_at' => $checkIn,
            'check_out_at' => $checkOut,
        ]);
        ReservationRoom::create([
            'reservation_id' => $reservation->id,
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'nightly_rate' => 100,
            'check_in_at' => $checkIn,
            'check_out_at' => $checkOut,
        ]);

        $availability = app(AvailabilityService::class);

        $overlapIsAvailable = DB::transaction(fn () => $availability->lockRoomIfAvailable(
            $room,
            $checkIn->copy()->addHours(2),
            $checkOut->copy()->addDay()
        ));

        $adjacentIsAvailable = DB::transaction(fn () => $availability->lockRoomIfAvailable(
            $room,
            $checkOut,
            $checkOut->copy()->addDay()
        ));

        $this->assertFalse($overlapIsAvailable);
        $this->assertTrue($adjacentIsAvailable);
    }

    public function test_online_booking_does_not_confirm_a_second_booking_for_an_occupied_room(): void
    {
        $property = Property::factory()->create();
        $roomType = RoomType::create([
            'property_id' => $property->id,
            'name' => 'Standard',
            'code' => 'STD',
            'base_rate' => 100,
            'is_active' => true,
        ]);
        Room::create([
            'property_id' => $property->id,
            'room_type_id' => $roomType->id,
            'room_number' => '101',
            'status' => 'available',
            'is_active' => true,
        ]);

        $data = [
            'property_id' => $property->id,
            'room_type_id' => $roomType->id,
            'check_in' => now()->addDays(10)->toDateString(),
            'check_out' => now()->addDays(11)->toDateString(),
            'first_name' => 'Test Guest',
            'email' => 'guest@example.test',
        ];

        $service = app(OnlineBookingService::class);
        $firstBooking = $service->create($data);

        $this->assertDatabaseHas('reservations', [
            'id' => $firstBooking->id,
            'property_id' => $property->id,
            'status' => 'confirmed',
        ]);

        try {
            $service->create($data);
            $this->fail('An overlapping online booking should not be confirmed.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('room_type_id', $exception->errors());
        }

        $this->assertDatabaseCount('reservations', 1);
    }
}
