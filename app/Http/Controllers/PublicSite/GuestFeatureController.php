<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Support\HotelFeatureSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GuestFeatureController extends Controller
{
    public function storeComment(Request $request): RedirectResponse
    {
        abort_unless(
            HotelFeatureSetting::bool('comments.enabled', true),
            404
        );

        abort_unless(Schema::hasTable('guest_comments'), 503);

        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:190'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        DB::table('guest_comments')->insert([
            'property_id' => null,
            'guest_name' => trim($data['guest_name']),
            'email' => filled($data['email'] ?? null)
                ? strtolower(trim($data['email']))
                : null,
            'rating' => (int) $data['rating'],
            'comment' => trim($data['comment']),
            'status' => 'pending',
            'is_featured' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with(
            'success',
            'Thank you. Your comment has been submitted for review.'
        );
    }

    public function chatbot(Request $request): JsonResponse
    {
        abort_unless(
            HotelFeatureSetting::bool('chatbot.enabled', true),
            404
        );

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $message = Str::lower(trim($data['message']));

        if (Schema::hasTable('chatbot_faqs')) {
            $faqs = DB::table('chatbot_faqs')
                ->where('is_active', true)
                ->orderBy('display_order')
                ->orderBy('id')
                ->get();

            $best = null;
            $bestScore = 0;

            foreach ($faqs as $faq) {
                $tokens = collect(
                    preg_split(
                        '/[\s,;|]+/',
                        Str::lower(
                            ($faq->keywords ?? '')
                            .' '.$faq->question
                        )
                    )
                )
                    ->filter(fn ($token) => mb_strlen($token) >= 3)
                    ->unique();

                $score = $tokens
                    ->filter(fn ($token) => str_contains($message, $token))
                    ->count();

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $faq;
                }
            }

            if ($best && $bestScore > 0) {
                return response()->json([
                    'success' => true,
                    'reply' => $best->answer,
                ]);
            }
        }

        $fallback = HotelFeatureSetting::get(
            'chatbot.fallback',
            'Thank you for contacting us. Please ask about rooms, bookings, payments, check-in, check-out or hotel services.'
        );

        return response()->json([
            'success' => true,
            'reply' => $fallback,
        ]);
    }
}
