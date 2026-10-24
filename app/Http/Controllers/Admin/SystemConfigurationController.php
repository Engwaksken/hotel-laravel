<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SystemConfigurationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorise($request);

        return view('admin.settings.index', [
            'activeTab' => $this->normaliseTab(
                (string) $request->query('tab', 'general')
            ),
            'settingsReady' => Schema::hasTable('system_settings'),
        ]);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $this->authorise($request);
        $this->ensureSettingsTable();

        $data = $request->validate([
            'system_name' => ['required', 'string', 'max:150'],
            'system_short_name' => ['nullable', 'string', 'max:60'],
            'support_email' => ['nullable', 'email', 'max:190'],
            'support_phone' => ['nullable', 'string', 'max:60'],
            'company_address' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::put(
                $key,
                filled($value) ? trim((string) $value) : null
            );
        }

        return redirect()
            ->route('admin.system-configuration.index', ['tab' => 'general'])
            ->with('success', 'General system settings updated successfully.');
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $this->authorise($request);
        $this->ensureSettingsTable();

        $data = $request->validate([
            'primary_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'secondary_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'accent_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp,svg',
                'max:4096',
            ],
            'favicon' => [
                'nullable',
                'file',
                'mimes:png,jpg,jpeg,webp,ico',
                'max:2048',
            ],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
        ]);

        SystemSetting::put('primary_color', $data['primary_color']);
        SystemSetting::put('secondary_color', $data['secondary_color']);
        SystemSetting::put('accent_color', $data['accent_color']);

        if ($request->boolean('remove_logo')) {
            $this->deleteStoredAsset(
                (string) SystemSetting::get('logo_path', '')
            );
            SystemSetting::put('logo_path', null);
        }

        if ($request->boolean('remove_favicon')) {
            $this->deleteStoredAsset(
                (string) SystemSetting::get('favicon_path', '')
            );
            SystemSetting::put('favicon_path', null);
        }

        if ($request->hasFile('logo')) {
            $old = (string) SystemSetting::get('logo_path', '');

            $path = $request->file('logo')
                ->store('system/branding', 'public');

            if ($old && $old !== $path) {
                $this->deleteStoredAsset($old);
            }

            SystemSetting::put('logo_path', $path);
        }

        if ($request->hasFile('favicon')) {
            $old = (string) SystemSetting::get('favicon_path', '');

            $path = $request->file('favicon')
                ->store('system/branding', 'public');

            if ($old && $old !== $path) {
                $this->deleteStoredAsset($old);
            }

            SystemSetting::put('favicon_path', $path);
        }

        return redirect()
            ->route('admin.system-configuration.index', ['tab' => 'branding'])
            ->with('success', 'Branding settings updated successfully.');
    }

    public function updateDocuments(Request $request): RedirectResponse
    {
        $this->authorise($request);
        $this->ensureSettingsTable();

        $data = $request->validate([
            'invoice_title' => ['nullable', 'string', 'max:150'],
            'invoice_footer' => ['nullable', 'string', 'max:1000'],
            'receipt_title' => ['nullable', 'string', 'max:150'],
            'receipt_footer' => ['nullable', 'string', 'max:1000'],
            'document_currency' => ['nullable', 'string', 'max:10'],
            'invoice_prefix' => ['nullable', 'string', 'max:30'],
            'receipt_prefix' => ['nullable', 'string', 'max:30'],
            'show_logo_on_documents' => ['nullable', 'boolean'],
            'show_qr_on_receipts' => ['nullable', 'boolean'],
        ]);

        foreach ([
            'invoice_title',
            'invoice_footer',
            'receipt_title',
            'receipt_footer',
            'document_currency',
            'invoice_prefix',
            'receipt_prefix',
        ] as $key) {
            SystemSetting::put(
                $key,
                filled($data[$key] ?? null)
                    ? trim((string) $data[$key])
                    : null
            );
        }

        SystemSetting::put(
            'show_logo_on_documents',
            $request->boolean('show_logo_on_documents') ? '1' : '0'
        );

        SystemSetting::put(
            'show_qr_on_receipts',
            $request->boolean('show_qr_on_receipts') ? '1' : '0'
        );

        return redirect()
            ->route('admin.system-configuration.index', ['tab' => 'documents'])
            ->with('success', 'Invoice and receipt settings updated successfully.');
    }

    private function ensureSettingsTable(): void
    {
        abort_unless(
            Schema::hasTable('system_settings'),
            503,
            'The system_settings table is missing. Run the latest migrations.'
        );
    }

    private function normaliseTab(string $tab): string
    {
        return in_array(
            $tab,
            ['general', 'branding', 'documents'],
            true
        ) ? $tab : 'general';
    }

    private function authorise(Request $request): void
    {
        $role = strtolower(
            (string) ($request->user()?->role ?? '')
        );

        abort_unless(
            $role === 'super_admin',
            403,
            'Only the Super Admin can manage system-wide configuration.'
        );
    }

    private function deleteStoredAsset(string $path): void
    {
        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
