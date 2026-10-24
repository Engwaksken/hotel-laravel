<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Support\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ClientLandingController extends Controller
{
    public function home(): View
    {
        return view('public-site.home', [
            'banners' => $this->banners(),
            'services' => $this->serviceRows(true),
            'property' => $this->property(),
            'settings' => $this->settings(),
        ]);
    }

    public function services(): View
    {
        return view('public-site.services', [
            'services' => $this->serviceRows(false),
            'property' => $this->property(),
            'settings' => $this->settings(),
        ]);
    }

    public function service(string $slug): View
    {
        abort_unless(Schema::hasTable('hotel_services'), 404);

        $service = DB::table('hotel_services')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        abort_if(! $service, 404);

        return view('public-site.service', [
            'service' => $service,
            'property' => $this->property(),
            'settings' => $this->settings(),
        ]);
    }

    private function banners()
    {
        if (! Schema::hasTable('homepage_banners')) {
            return collect();
        }

        $query = DB::table('homepage_banners')
            ->where('is_active', true);

        $property = $this->property();

        if ($property && Schema::hasColumn('homepage_banners', 'property_id')) {
            $query->where(function ($sub) use ($property): void {
                $sub->whereNull('property_id')
                    ->orWhere('property_id', $property->id);
            });
        }

        return $query
            ->orderBy('display_order')
            ->orderByDesc('id')
            ->get();
    }

    private function serviceRows(bool $homepage)
    {
        if (! Schema::hasTable('hotel_services')) {
            return collect();
        }

        $query = DB::table('hotel_services')
            ->where('is_active', true);

        if ($homepage) {
            $query->where('show_on_homepage', true);
        }

        $property = $this->property();

        if ($property && Schema::hasColumn('hotel_services', 'property_id')) {
            $query->where(function ($sub) use ($property): void {
                $sub->whereNull('property_id')
                    ->orWhere('property_id', $property->id);
            });
        }

        return $query
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    private function property(): ?object
    {
        if (! Schema::hasTable('properties')) {
            return null;
        }

        $query = DB::table('properties');

        if (Schema::hasColumn('properties', 'is_active')) {
            $query->where('is_active', true);
        }

        return $query->orderBy('id')->first();
    }

    private function settings(): array
    {
        return [
            'system_name' => SystemSetting::get(
                'system_name',
                config('app.name', 'Hotel Management System')
            ),
            'logo_path' => SystemSetting::get('logo_path'),
            'support_phone' => SystemSetting::get('support_phone'),
            'support_email' => SystemSetting::get('support_email'),
            'company_address' => SystemSetting::get('company_address'),
            'primary_color' => SystemSetting::get('primary_color', '#0f766e'),
            'secondary_color' => SystemSetting::get('secondary_color', '#134e4a'),
            'accent_color' => SystemSetting::get('accent_color', '#14b8a6'),
        ];
    }
}
