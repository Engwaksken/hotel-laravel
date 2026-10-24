<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ClientPortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $bookings = $this->bookingsFor($user);
        $payments = $this->paymentsFor($user);
        $services = $this->serviceRows();

        return view('client.dashboard', [
            'user' => $user,
            'bookings' => $bookings,
            'payments' => $payments,
            'services' => $services,
        ]);
    }

    public function services(): View
    {
        return view('client.services', ['services' => $this->serviceRows()]);
    }

    public function bookings(Request $request): View
    {
        return view('client.bookings', [
            'bookings' => $this->bookingsFor($request->user()),
        ]);
    }

    public function payments(Request $request): View
    {
        return view('client.payments', [
            'payments' => $this->paymentsFor($request->user()),
        ]);
    }

    private function serviceRows(): Collection
    {
        foreach (['catering_services', 'hotel_services', 'services', 'laundry_services'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $query = DB::table($table);

            if (Schema::hasColumn($table, 'is_active')) {
                $query->where('is_active', true);
            }

            if (Schema::hasColumn($table, 'status')) {
                $query->whereIn('status', ['active', 'available', 'published']);
            }

            return $query->orderBy(Schema::hasColumn($table, 'name') ? 'name' : 'id')->get();
        }

        return collect();
    }

    private function bookingsFor(object $user): Collection
    {
        $table = Schema::hasTable('reservations')
            ? 'reservations'
            : (Schema::hasTable('bookings') ? 'bookings' : null);

        if (! $table) {
            return collect();
        }

        $query = DB::table($table);
        $this->scopeToUser($query, $table, $user);

        return $query->orderByDesc(Schema::hasColumn($table, 'created_at') ? 'created_at' : 'id')->get();
    }

    private function paymentsFor(object $user): Collection
    {
        foreach (['payments', 'folio_payments', 'invoice_receipts', 'receipts'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $query = DB::table($table);
            $this->scopeToUser($query, $table, $user);

            return $query->orderByDesc(Schema::hasColumn($table, 'created_at') ? 'created_at' : 'id')->get();
        }

        return collect();
    }

    private function scopeToUser(object $query, string $table, object $user): void
    {
        if (Schema::hasColumn($table, 'user_id')) {
            $query->where('user_id', $user->id);
            return;
        }

        if (! empty($user->email) && Schema::hasColumn($table, 'email')) {
            $query->where('email', $user->email);
            return;
        }

        if (Schema::hasColumn($table, 'guest_id') && Schema::hasTable('guests')) {
            $guestId = null;

            if (! empty($user->email) && Schema::hasColumn('guests', 'email')) {
                $guestId = DB::table('guests')->where('email', $user->email)->value('id');
            }

            if ($guestId) {
                $query->where('guest_id', $guestId);
                return;
            }
        }

        $query->whereRaw('1 = 0');
    }
}
