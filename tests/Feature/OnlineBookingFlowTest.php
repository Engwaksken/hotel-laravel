<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyWebSetting;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineBookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_booking_flow_creates_reservation_and_uses_reference_for_confirmation(): void
    {
        $property = Property::factory()->create();
        $web = PropertyWebSetting::create([
            'property_id' => $property->id,
            'slug' => 'test-hotel',
            'online_booking_enabled' => true,
            'default_deposit_percent' => 30,
        ]);
        $roomType = RoomType::create([
            'property_id' => $property->id,
            'name' => 'Standard',
            'code' => 'STD',
            'base_rate' => 100,
            'max_adults' => 2,
            'max_children' => 1,
            'is_active' => true,
        ]);
        Room::create([
            'property_id' => $property->id,
            'room_type_id' => $roomType->id,
            'room_number' => '101',
            'status' => 'available',
            'is_active' => true,
        ]);

        $this->get(route('public.booking', $web->slug))
            ->assertOk()
            ->assertSee('Book your stay')
            ->assertSee('availability-feedback', false);

        $checkIn = now()->addDays(10)->toDateString();
        $checkOut = now()->addDays(11)->toDateString();
        $availabilityRoute = route('public.booking.availability', [
            'slug' => $web->slug,
            'room_type_id' => $roomType->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ]);

        $this->getJson($availabilityRoute)
            ->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('available_rooms', 1);

        $capacityErrorResponse = $this->followingRedirects()
            ->from(route('public.booking', $web->slug))
            ->post(route('public.booking.store', $web->slug), [
                'room_type_id' => $roomType->id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'adults' => 3,
                'children' => 0,
                'first_name' => 'Online',
                'email' => 'too-many@example.test',
                'phone' => '+256700000002',
            ]);

        $capacityErrorResponse
            ->assertOk()
            ->assertSee('The selected room type cannot accommodate that number of guests.')
            ->assertSee('value="3"', false);

        $response = $this->post(route('public.booking.store', $web->slug), [
            'room_type_id' => $roomType->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => 2,
            'children' => 1,
            'first_name' => 'Online',
            'last_name' => 'Guest',
            'email' => 'online@example.test',
            'phone' => '+256700000001',
        ]);

        $response->assertRedirect();
        $confirmationUrl = $response->headers->get('Location');
        $this->assertStringContainsString('/booking-confirmation/', $confirmationUrl);

        $confirmation = $this->get($confirmationUrl);
        $confirmation->assertOk()->assertSee('Booking confirmed');

        $reservation = \App\Models\Reservation::query()->firstOrFail();
        $this->assertStringContainsString($reservation->reference, $confirmationUrl);
        $this->get(route('public.booking.confirmation', ['another-hotel', $reservation->reference]))
            ->assertNotFound();

        $this->getJson($availabilityRoute)
            ->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('available_rooms', 0);
    }

    public function test_public_booking_page_is_hidden_when_booking_is_disabled(): void
    {
        $property = Property::factory()->create();
        $web = PropertyWebSetting::create([
            'property_id' => $property->id,
            'slug' => 'closed-hotel',
            'online_booking_enabled' => false,
        ]);

        $this->get(route('public.booking', $web->slug))->assertNotFound();
        $this->get(route('public.booking.availability', [
            'slug' => $web->slug,
            'room_type_id' => 1,
            'check_in' => now()->addDay()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
        ]))->assertNotFound();
    }
}
