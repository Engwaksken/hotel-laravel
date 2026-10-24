<?php

namespace App\Http\Controllers;

use App\Models\{Reservation, Stay};
use App\Services\FrontDesk\{CheckInService, CheckOutService};
use Illuminate\Http\Request;

class FrontDeskController extends Controller
{
    public function checkIn(Request $request, Reservation $reservation, CheckInService $service)
    {
        abort_unless($request->user()->canManageProperty((int) $reservation->property_id), 404);

        $service->execute($reservation, $request->user()->id);

        return redirect()
            ->route('reservations.show', $reservation)
            ->with('success', 'Guest checked in successfully. Room is now occupied.');
    }

    public function checkOut(Request $request, Stay $stay, CheckOutService $service)
    {
        abort_unless($request->user()->canManageProperty((int) $stay->property_id), 404);

        $canAuthoriseBalance = $request->user()->isManager()
            || $request->user()->isBranchManager();

        $service->execute(
            $stay,
            $request->user()->id,
            $canAuthoriseBalance && $request->boolean('allow_balance')
        );

        return redirect()
            ->route('reservations.show', $stay->reservation_id)
            ->with('success', 'Guest checked out successfully. Room has been marked dirty for housekeeping.');
    }
}
