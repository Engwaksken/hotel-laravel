<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Guest;
use App\Models\Property;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $checkIn = now()->addDays(7)->startOfDay();

        return [
            'property_id' => Property::factory(),
            'guest_id' => Guest::factory(),
            'reference' => Str::upper(Str::random(12)),
            'status' => ReservationStatus::Pending,
            'source' => 'front_desk',
            'check_in_at' => $checkIn,
            'check_out_at' => $checkIn->copy()->addDay(),
            'adults' => 1,
            'children' => 0,
        ];
    }
}
