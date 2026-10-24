<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebsiteContentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorise($request);

        return view('admin.website.index', [
            'banners' => Schema::hasTable('homepage_banners')
                ? DB::table('homepage_banners')->orderBy('display_order')->orderByDesc('id')->get()
                : collect(),
            'services' => Schema::hasTable('hotel_services')
                ? DB::table('hotel_services')->orderBy('display_order')->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function storeBanner(Request $request): RedirectResponse
    {
        $this->authorise($request);
        abort_unless(Schema::hasTable('homepage_banners'), 503);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'button_text' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:500'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $path = $request->file('image')->store('website/banners', 'public');

        DB::table('homepage_banners')->insert([
            'property_id' => $request->user()->property_id ?? null,
            'title' => trim($data['title']),
            'subtitle' => filled($data['subtitle'] ?? null) ? trim($data['subtitle']) : null,
            'image_path' => $path,
            'button_text' => filled($data['button_text'] ?? null) ? trim($data['button_text']) : null,
            'button_url' => filled($data['button_url'] ?? null) ? trim($data['button_url']) : null,
            'display_order' => (int) ($data['display_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Homepage banner added successfully.');
    }

    public function updateBanner(Request $request, int $banner): RedirectResponse
    {
        $this->authorise($request);

        $record = DB::table('homepage_banners')->where('id', $banner)->first();
        abort_if(! $record, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'button_text' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:500'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'title' => trim($data['title']),
            'subtitle' => filled($data['subtitle'] ?? null) ? trim($data['subtitle']) : null,
            'button_text' => filled($data['button_text'] ?? null) ? trim($data['button_text']) : null,
            'button_url' => filled($data['button_url'] ?? null) ? trim($data['button_url']) : null,
            'display_order' => (int) ($data['display_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'updated_at' => now(),
        ];

        if ($request->hasFile('image')) {
            $payload['image_path'] = $request->file('image')->store('website/banners', 'public');

            if (! empty($record->image_path)) {
                Storage::disk('public')->delete($record->image_path);
            }
        }

        DB::table('homepage_banners')->where('id', $banner)->update($payload);

        return back()->with('success', 'Homepage banner updated successfully.');
    }

    public function destroyBanner(Request $request, int $banner): RedirectResponse
    {
        $this->authorise($request);

        $record = DB::table('homepage_banners')->where('id', $banner)->first();
        abort_if(! $record, 404);

        if (! empty($record->image_path)) {
            Storage::disk('public')->delete($record->image_path);
        }

        DB::table('homepage_banners')->where('id', $banner)->delete();

        return back()->with('success', 'Homepage banner deleted.');
    }

    public function storeService(Request $request): RedirectResponse
    {
        $this->authorise($request);
        abort_unless(Schema::hasTable('hotel_services'), 503);

        $data = $this->validateService($request, null);

        $path = $request->hasFile('image')
            ? $request->file('image')->store('website/services', 'public')
            : null;

        DB::table('hotel_services')->insert([
            'property_id' => $request->user()->property_id ?? null,
            'name' => trim($data['name']),
            'slug' => $this->uniqueSlug($data['name']),
            'short_description' => filled($data['short_description'] ?? null) ? trim($data['short_description']) : null,
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'image_path' => $path,
            'price' => filled($data['price'] ?? null) ? $data['price'] : null,
            'price_label' => filled($data['price_label'] ?? null) ? trim($data['price_label']) : null,
            'button_text' => filled($data['button_text'] ?? null) ? trim($data['button_text']) : null,
            'button_url' => filled($data['button_url'] ?? null) ? trim($data['button_url']) : null,
            'display_order' => (int) ($data['display_order'] ?? 0),
            'show_on_homepage' => $request->boolean('show_on_homepage', true),
            'is_active' => $request->boolean('is_active', true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Hotel service added successfully.');
    }

    public function updateService(Request $request, int $service): RedirectResponse
    {
        $this->authorise($request);

        $record = DB::table('hotel_services')->where('id', $service)->first();
        abort_if(! $record, 404);

        $data = $this->validateService($request, $service);

        $payload = [
            'name' => trim($data['name']),
            'slug' => $this->uniqueSlug($data['name'], $service),
            'short_description' => filled($data['short_description'] ?? null) ? trim($data['short_description']) : null,
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'price' => filled($data['price'] ?? null) ? $data['price'] : null,
            'price_label' => filled($data['price_label'] ?? null) ? trim($data['price_label']) : null,
            'button_text' => filled($data['button_text'] ?? null) ? trim($data['button_text']) : null,
            'button_url' => filled($data['button_url'] ?? null) ? trim($data['button_url']) : null,
            'display_order' => (int) ($data['display_order'] ?? 0),
            'show_on_homepage' => $request->boolean('show_on_homepage'),
            'is_active' => $request->boolean('is_active'),
            'updated_at' => now(),
        ];

        if ($request->hasFile('image')) {
            $payload['image_path'] = $request->file('image')->store('website/services', 'public');

            if (! empty($record->image_path)) {
                Storage::disk('public')->delete($record->image_path);
            }
        }

        DB::table('hotel_services')->where('id', $service)->update($payload);

        return back()->with('success', 'Hotel service updated successfully.');
    }

    public function destroyService(Request $request, int $service): RedirectResponse
    {
        $this->authorise($request);

        $record = DB::table('hotel_services')->where('id', $service)->first();
        abort_if(! $record, 404);

        if (! empty($record->image_path)) {
            Storage::disk('public')->delete($record->image_path);
        }

        DB::table('hotel_services')->where('id', $service)->delete();

        return back()->with('success', 'Hotel service deleted.');
    }

    private function validateService(Request $request, ?int $id): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'short_description' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_label' => ['nullable', 'string', 'max:80'],
            'button_text' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:500'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'show_on_homepage' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name) ?: 'service';
        $slug = $base;
        $counter = 2;

        while (
            DB::table('hotel_services')
                ->where('slug', $slug)
                ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function authorise(Request $request): void
    {
        $role = strtolower((string) ($request->user()?->role ?? ''));

        abort_unless(
            in_array($role, ['super_admin', 'admin', 'hotel_owner', 'general_manager'], true),
            403
        );
    }
}
