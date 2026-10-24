<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\BookingAddon;
use App\Models\Promotion;
use App\Models\PropertyWebSetting;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Reservations\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HotelWebsiteController extends Controller
{
    public function home(?string $slug = null)
    {
        $web = $slug
            ? PropertyWebSetting::query()->where('slug', $slug)->firstOrFail()
            : PropertyWebSetting::query()->firstOrFail();

        $web->load('property');

        $types = RoomType::query()
            ->where('property_id', $web->property_id)
            ->where('is_active', true)
            ->orderBy('base_rate')
            ->get();

        $promos = Promotion::query()
            ->where('property_id', $web->property_id)
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->limit(4)
            ->get();

        return view('public.home', compact('web', 'types', 'promos'));
    }

    public function booking(string $slug)
    {
        $web = PropertyWebSetting::query()
            ->where('slug', $slug)
            ->with('property')
            ->firstOrFail();

        abort_unless($web->online_booking_enabled, 404);

        $types = RoomType::query()
            ->where('property_id', $web->property_id)
            ->where('is_active', true)
            ->orderBy('base_rate')
            ->get();

        $addons = BookingAddon::query()
            ->where('property_id', $web->property_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('public.booking', compact('web', 'types', 'addons'));
    }

    public function availability(
        Request $request,
        string $slug,
        AvailabilityService $availability
    ) {
        $web = PropertyWebSetting::query()
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($web->online_booking_enabled, 404);

        $data = $request->validate([
            'room_type_id' => ['required', 'integer'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
        ]);

        $roomType = RoomType::query()
            ->where('property_id', $web->property_id)
            ->whereKey($data['room_type_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $checkIn = Carbon::parse($data['check_in'])->setTime(14, 0);
        $checkOut = Carbon::parse($data['check_out'])->setTime(11, 0);
        $availableIds = $availability->availableRoomIds(
            $web->property_id,
            $checkIn,
            $checkOut
        );

        $count = Room::query()
            ->where('property_id', $web->property_id)
            ->where('room_type_id', $roomType->id)
            ->whereIn('id', $availableIds)
            ->count();

        return response()->json([
            'available' => $count > 0,
            'available_rooms' => $count,
        ]);
    }
}
