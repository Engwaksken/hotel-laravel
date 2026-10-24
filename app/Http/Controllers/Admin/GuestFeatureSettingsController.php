<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\HotelFeatureSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class GuestFeatureSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorise($request);

        return view('admin.guest-features.index', [
            'settings' => $this->settings(),
            'comments' => Schema::hasTable('guest_comments')
                ? DB::table('guest_comments')
                    ->orderByDesc('created_at')
                    ->limit(100)
                    ->get()
                : collect(),
            'faqs' => Schema::hasTable('chatbot_faqs')
                ? DB::table('chatbot_faqs')
                    ->orderBy('display_order')
                    ->orderBy('id')
                    ->get()
                : collect(),
        ]);
    }

    public function updateAccessibility(Request $request): RedirectResponse
    {
        $this->authorise($request);

        HotelFeatureSetting::put(
            'accessibility.enabled',
            $request->boolean('enabled')
        );

        HotelFeatureSetting::put(
            'accessibility.read_aloud',
            $request->boolean('read_aloud')
        );

        HotelFeatureSetting::put(
            'accessibility.default_font_scale',
            (string) max(
                90,
                min(
                    130,
                    (int) $request->input(
                        'default_font_scale',
                        100
                    )
                )
            )
        );

        return back()->with(
            'success',
            'Accessibility settings updated successfully.'
        );
    }

    public function updateChatbot(Request $request): RedirectResponse
    {
        $this->authorise($request);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:80'],
            'welcome' => ['nullable', 'string', 'max:500'],
            'fallback' => ['nullable', 'string', 'max:1000'],
        ]);

        HotelFeatureSetting::put(
            'chatbot.enabled',
            $request->boolean('enabled')
        );

        HotelFeatureSetting::put(
            'chatbot.title',
            $data['title'] ?? 'Hotel Assistant'
        );

        HotelFeatureSetting::put(
            'chatbot.welcome',
            $data['welcome']
                ?? 'Hello. How can I help with your stay?'
        );

        HotelFeatureSetting::put(
            'chatbot.fallback',
            $data['fallback']
                ?? 'Please contact hotel reception for further assistance.'
        );

        return back()->with(
            'success',
            'Chatbot settings updated successfully.'
        );
    }

    public function updateEfris(Request $request): RedirectResponse
    {
        $this->authorise($request);

        $data = $request->validate([
            'environment' => ['required', 'in:sandbox,production'],
            'tin' => ['nullable', 'string', 'max:50'],
            'business_name' => ['nullable', 'string', 'max:180'],
            'branch_code' => ['nullable', 'string', 'max:80'],
            'device_number' => ['nullable', 'string', 'max:120'],
            'api_base_url' => ['nullable', 'url', 'max:500'],
            'client_id' => ['nullable', 'string', 'max:500'],
            'client_secret' => ['nullable', 'string', 'max:2000'],
            'certificate_reference' => ['nullable', 'string', 'max:500'],
        ]);

        HotelFeatureSetting::put(
            'efris.enabled',
            $request->boolean('enabled')
        );

        foreach ([
            'environment',
            'tin',
            'business_name',
            'branch_code',
            'device_number',
            'api_base_url',
            'certificate_reference',
        ] as $key) {
            HotelFeatureSetting::put(
                'efris.'.$key,
                $data[$key] ?? null
            );
        }

        if (filled($data['client_id'] ?? null)) {
            HotelFeatureSetting::put(
                'efris.client_id',
                $data['client_id'],
                true
            );
        }

        if (filled($data['client_secret'] ?? null)) {
            HotelFeatureSetting::put(
                'efris.client_secret',
                $data['client_secret'],
                true
            );
        }

        return back()->with(
            'success',
            'URA EFRIS configuration updated successfully.'
        );
    }

    public function updateComments(Request $request): RedirectResponse
    {
        $this->authorise($request);

        HotelFeatureSetting::put(
            'comments.enabled',
            $request->boolean('enabled')
        );

        HotelFeatureSetting::put(
            'comments.show_homepage',
            $request->boolean('show_homepage')
        );

        return back()->with(
            'success',
            'Guest comment settings updated successfully.'
        );
    }

    public function moderateComment(
        Request $request,
        int $comment
    ): RedirectResponse {
        $this->authorise($request);

        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        DB::table('guest_comments')
            ->where('id', $comment)
            ->update([
                'status' => $data['status'],
                'is_featured' => $request->boolean('is_featured'),
                'updated_at' => now(),
            ]);

        return back()->with(
            'success',
            'Guest comment updated.'
        );
    }

    public function storeFaq(Request $request): RedirectResponse
    {
        $this->authorise($request);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:3000'],
            'keywords' => ['nullable', 'string', 'max:1000'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::table('chatbot_faqs')->insert([
            'question' => trim($data['question']),
            'answer' => trim($data['answer']),
            'keywords' => filled($data['keywords'] ?? null)
                ? trim($data['keywords'])
                : null,
            'display_order' => (int) ($data['display_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with(
            'success',
            'Chatbot FAQ added successfully.'
        );
    }

    public function destroyFaq(
        Request $request,
        int $faq
    ): RedirectResponse {
        $this->authorise($request);

        DB::table('chatbot_faqs')
            ->where('id', $faq)
            ->delete();

        return back()->with(
            'success',
            'Chatbot FAQ deleted.'
        );
    }

    private function settings(): array
    {
        return [
            'accessibility_enabled' => HotelFeatureSetting::bool(
                'accessibility.enabled',
                true
            ),
            'accessibility_read_aloud' => HotelFeatureSetting::bool(
                'accessibility.read_aloud',
                true
            ),
            'accessibility_font_scale' => HotelFeatureSetting::get(
                'accessibility.default_font_scale',
                '100'
            ),
            'chatbot_enabled' => HotelFeatureSetting::bool(
                'chatbot.enabled',
                true
            ),
            'chatbot_title' => HotelFeatureSetting::get(
                'chatbot.title',
                'Hotel Assistant'
            ),
            'chatbot_welcome' => HotelFeatureSetting::get(
                'chatbot.welcome',
                'Hello. How can I help with your stay?'
            ),
            'chatbot_fallback' => HotelFeatureSetting::get(
                'chatbot.fallback',
                'Please contact hotel reception for further assistance.'
            ),
            'comments_enabled' => HotelFeatureSetting::bool(
                'comments.enabled',
                true
            ),
            'comments_show_homepage' => HotelFeatureSetting::bool(
                'comments.show_homepage',
                true
            ),
            'efris_enabled' => HotelFeatureSetting::bool(
                'efris.enabled',
                false
            ),
            'efris_environment' => HotelFeatureSetting::get(
                'efris.environment',
                'sandbox'
            ),
            'efris_tin' => HotelFeatureSetting::get('efris.tin'),
            'efris_business_name' => HotelFeatureSetting::get(
                'efris.business_name'
            ),
            'efris_branch_code' => HotelFeatureSetting::get(
                'efris.branch_code'
            ),
            'efris_device_number' => HotelFeatureSetting::get(
                'efris.device_number'
            ),
            'efris_api_base_url' => HotelFeatureSetting::get(
                'efris.api_base_url'
            ),
            'efris_certificate_reference' => HotelFeatureSetting::get(
                'efris.certificate_reference'
            ),
            'efris_has_client_id' => filled(
                HotelFeatureSetting::get('efris.client_id')
            ),
            'efris_has_client_secret' => filled(
                HotelFeatureSetting::get('efris.client_secret')
            ),
        ];
    }

    private function authorise(Request $request): void
    {
        $role = strtolower(
            (string) ($request->user()?->role ?? '')
        );

        abort_unless(
            in_array(
                $role,
                [
                    'super_admin',
                    'admin',
                    'hotel_owner',
                    'general_manager',
                ],
                true
            ),
            403
        );
    }
}
