<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PropertyWebSetting;
use App\Models\Reservation;
use App\Services\Guest\OnlineBookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class OnlineBookingController extends Controller
{
    public function store(Request $request, string $slug, OnlineBookingService $service)
    {
        $web = PropertyWebSetting::query()
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($web->online_booking_enabled, 404);

        $data = $request->validate([
            'room_type_id' => 'required|integer',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1|max:20',
            'children' => 'nullable|integer|min:0|max:20',
            'first_name' => 'required|string|max:120',
            'last_name' => 'nullable|string|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'required|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'special_requests' => 'nullable|string|max:2000',
            'addons' => 'nullable|array',
            'addons.*' => 'nullable|integer|min:0|max:20',
        ]);

        $data['property_id'] = $web->property_id;
        $data['deposit_percent'] = $web->default_deposit_percent;
        $reservation = $service->create($data);

        return redirect()->route('public.booking.confirmation', [
            $slug,
            $reservation->reference,
        ])->with('success', 'Booking confirmed.');
    }

    public function confirmation(string $slug, Reservation $reservation)
    {
        $web = PropertyWebSetting::query()
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($reservation->property_id === $web->property_id, 404);

        $reservation->load('guest', 'rooms.roomType');
        $paid = (float) $reservation->payments()
            ->where('status', 'successful')
            ->sum('amount');
        $depositDue = max(0, round((float) $reservation->deposit_required - $paid, 2));
        $paymentUrl = $depositDue >= 1
            ? URL::temporarySignedRoute(
                'public.booking.payment',
                now()->addHours(24),
                ['slug' => $slug, 'reservation' => $reservation->reference],
            )
            : null;

        return view('public.confirmation', compact('web', 'reservation', 'depositDue', 'paymentUrl'));
    }
}
