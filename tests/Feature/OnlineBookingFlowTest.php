<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyWebSetting;
use App\Models\Room;
use App\Models\RoomType;
use App\Enums\RoomStatus;
use App\Services\Payments\Contracts\PaymentGateway;
use App\Services\Payments\PaymentManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
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
        DB::table('hotel_services')->insert([
            'property_id' => $property->id,
            'name' => 'Rooms',
            'slug' => 'room',
            'is_active' => true,
            'show_on_homepage' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Room::create([
            'property_id' => $property->id,
            'room_type_id' => $roomType->id,
            'room_number' => '101',
            'status' => 'available',
            'is_active' => true,
        ]);

        $this->get(route('public.hotel.property', $web->slug))
            ->assertOk()
            ->assertSee('Book this room')
            ->assertSee(route('public.booking', [
                'slug' => $web->slug,
                'room_type_id' => $roomType->id,
            ]), false);

        $this->get(route('public.services.show', 'room'))
            ->assertOk()
            ->assertSee(route('public.booking', $web->slug), false)
            ->assertSee('Book this room');

        $this->get(route('public.booking', $web->slug))
            ->assertOk()
            ->assertSee('Book your stay')
            ->assertSee('availability-feedback', false);

        $this->get(route('public.booking', [
            'slug' => $web->slug,
            'room_type_id' => $roomType->id,
        ]))
            ->assertOk()
            ->assertSee('value="'.$roomType->id.'"', false)
            ->assertSee('selected', false);

        DB::table('rooms')->where('room_number', '101')->update(['status' => 'clean']);
        $this->assertSame(RoomStatus::Available, Room::query()->where('room_number', '101')->firstOrFail()->status);

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
        $confirmation->assertOk()
            ->assertSee('Booking confirmed')
            ->assertSee('Proceed to payment');

        $reservation = \App\Models\Reservation::query()->firstOrFail();
        $this->assertStringContainsString($reservation->reference, $confirmationUrl);

        $paymentUrl = URL::temporarySignedRoute(
            'public.booking.payment',
            now()->addHour(),
            ['slug' => $web->slug, 'reservation' => $reservation->reference],
        );
        $this->get($paymentUrl)
            ->assertOk()
            ->assertSee('Pay your booking deposit')
            ->assertSee('UGX 30');

        $initiateUrl = URL::temporarySignedRoute(
            'public.booking.payment.initiate',
            now()->addHour(),
            ['slug' => $web->slug, 'reservation' => $reservation->reference],
        );
        $manager = new PaymentManager();
        $manager->register('pesapal', new FakePaymentGateway());
        $this->app->instance(PaymentManager::class, $manager);

        $this->post($initiateUrl, ['method' => 'pesapal'])
            ->assertRedirect('https://checkout.example.test/pay');

        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'method' => 'pesapal',
            'status' => 'processing',
            'amount' => 30,
        ]);

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

class FakePaymentGateway implements PaymentGateway
{
    public function initiate(array $payload): array
    {
        return [
            'mode' => 'redirect',
            'redirect_url' => 'https://checkout.example.test/pay',
            'gateway_reference' => 'test-gateway-reference',
        ];
    }

    public function verify(string $reference): array
    {
        return ['successful' => false, 'status' => 'pending'];
    }

    public function refund(string $reference, float $amount): array
    {
        return ['status' => 'pending'];
    }
}
