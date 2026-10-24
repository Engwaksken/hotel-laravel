<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class GuestQrController extends Controller
{
    public function show(int $guest)
    {
        $record = DB::table('guests')->where('id', $guest)->first();
        abort_if(! $record, 404);

        if (empty($record->qr_token)) {
            DB::table('guests')->where('id', $guest)->update([
                'qr_token' => (string) Str::uuid(),
                'updated_at' => now(),
            ]);

            $record = DB::table('guests')->where('id', $guest)->first();
        }

        return view('guests.qr', compact('record'));
    }

    public function svg(int $guest): Response
    {
        $record = DB::table('guests')->where('id', $guest)->first();
        abort_if(! $record, 404);

        if (empty($record->qr_token)) {
            DB::table('guests')->where('id', $guest)->update([
                'qr_token' => (string) Str::uuid(),
                'updated_at' => now(),
            ]);

            $record = DB::table('guests')->where('id', $guest)->first();
        }

        $payload = URL::signedRoute('guests.qr.verify', [
            'guest' => $record->id,
            'token' => $record->qr_token,
        ]);

        $result = new Builder(
            writer: new SvgWriter(),
            data: $payload,
            size: 320,
            margin: 10
        );

        return response($result->build()->getString(), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function verify(int $guest, string $token)
    {
        $record = DB::table('guests')
            ->where('id', $guest)
            ->where('qr_token', $token)
            ->first();

        abort_if(! $record, 404, 'Invalid or expired guest QR code.');

        return view('guests.qr-verified', compact('record'));
    }
}
