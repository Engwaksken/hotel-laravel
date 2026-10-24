<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    DataImportRun,
    NotificationProviderConfig,
    OnboardingState,
    PaymentGatewayConfig,
    PrinterConfig,
    Property,
    Room,
    RoomType,
    TaxRule,
    User
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdministrationController extends Controller
{
    private const ALL_ROLES = [
        'super_admin',
        'hotel_owner',
        'general_manager',
        'branch_manager',
        'front_office_manager',
        'receptionist',
        'accountant',
        'cashier',
        'restaurant_manager',
        'waiter',
        'chef',
        'bar_manager',
        'bartender',
        'store_manager',
        'procurement_officer',
        'housekeeping_manager',
        'housekeeper',
        'maintenance',
        'hr',
        'auditor',
    ];

    private function propertyId(Request $request): int
    {
        $user = $request->user();
        abort_unless($user && $user->canManageBranch(), 403);

        // Super Admin may explicitly inspect another branch. Everyone else is
        // forced to the property assigned to their account.
        if ($user->isSuperAdmin() && $request->filled('property_id')) {
            return (int) Property::query()->whereKey($request->integer('property_id'))->value('id');
        }

        abort_unless($user->property_id, 403, 'Your account is not assigned to a hotel branch.');

        return (int) $user->property_id;
    }

    private function allowedRoles(Request $request): array
    {
        return array_values(array_filter(
            self::ALL_ROLES,
            fn (string $role) => $request->user()->canAssignRole($role)
        ));
    }

    private function requireSystemConfigurationAccess(Request $request): void
    {
        abort_unless(in_array($request->user()->role, [
            'super_admin',
            'hotel_owner',
            'general_manager',
        ], true), 403, 'Branch Managers cannot change protected system or payment configuration.');
    }

    public function index(Request $request)
    {
        $pid = $this->propertyId($request);

        return view('admin.dashboard', [
            'property' => Property::findOrFail($pid),
            'users' => User::where('property_id', $pid)->count(),
            'rooms' => Room::where('property_id', $pid)->count(),
            'roomTypes' => RoomType::where('property_id', $pid)->count(),
            'taxes' => TaxRule::where('property_id', $pid)->count(),
            'gateways' => PaymentGatewayConfig::where('property_id', $pid)->count(),
            'steps' => OnboardingState::where('property_id', $pid)->get()->keyBy('step'),
        ]);
    }

    public function users(Request $request)
    {
        $pid = $this->propertyId($request);

        return view('admin.users', [
            'users' => User::where('property_id', $pid)->orderBy('name')->paginate(30),
            'allowedRoles' => $this->allowedRoles($request),
            'property' => Property::findOrFail($pid),
        ]);
    }

    public function saveUser(Request $request, ?User $user = null)
    {
        $pid = $this->propertyId($request);

        if ($user) {
            abort_unless($request->user()->canManageProperty((int) $user->property_id), 404);
        }

        $allowedRoles = $this->allowedRoles($request);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => 'nullable|string|max:50',
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'password' => $user ? 'nullable|min:8' : 'required|min:8',
            'is_active' => 'nullable|boolean',
        ]);

        abort_unless($request->user()->canAssignRole($data['role']), 403);

        $payload = [
            'property_id' => $pid,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        ($user ?? new User())->fill($payload)->save();

        return back()->with('success', 'Branch user account saved.');
    }

    public function setup(Request $request)
    {
        $pid = $this->propertyId($request);

        return view('admin.setup', [
            'property' => Property::findOrFail($pid),
            'steps' => OnboardingState::where('property_id', $pid)->get()->keyBy('step'),
        ]);
    }

    public function saveProperty(Request $request)
    {
        $pid = $this->propertyId($request);

        $data = $request->validate([
            'name' => 'required',
            'legal_name' => 'nullable',
            'tin' => 'nullable',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'address' => 'nullable',
            'city' => 'nullable',
            'currency' => 'required|size:3',
            'timezone' => 'required',
            'website' => 'nullable|url',
            'primary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo' => 'nullable|image|max:3072',
            'favicon' => 'nullable|image|max:1024',
        ]);

        $property = Property::findOrFail($pid);
        unset($data['logo'], $data['favicon']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('hotel-branding', 'public');
        }

        if ($request->hasFile('favicon')) {
            $data['favicon_path'] = $request->file('favicon')->store('hotel-branding', 'public');
        }

        $property->update($data);
        $this->done($pid, 'property', $request);

        return back()->with('success', 'Branch profile and branding saved.');
    }

    public function configuration(Request $request)
    {
        $this->requireSystemConfigurationAccess($request);
        $pid = $this->propertyId($request);

        return view('admin.configuration', [
            'taxes' => TaxRule::where('property_id', $pid)->orderBy('name')->get(),
            'gateways' => PaymentGatewayConfig::where('property_id', $pid)->orderBy('gateway')->get(),
            'printers' => PrinterConfig::where('property_id', $pid)->get(),
            'providers' => NotificationProviderConfig::where('property_id', $pid)->get(),
        ]);
    }

    public function tax(Request $request)
    {
        $this->requireSystemConfigurationAccess($request);
        $pid = $this->propertyId($request);

        $data = $request->validate([
            'name' => 'required',
            'code' => 'required|max:40',
            'rate' => 'required|numeric|min:0',
            'type' => 'required|in:percentage,fixed',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        TaxRule::updateOrCreate(
            ['property_id' => $pid, 'code' => $data['code']],
            $data + [
                'property_id' => $pid,
                'inclusive' => $request->boolean('inclusive'),
                'is_active' => $request->boolean('is_active'),
            ]
        );

        $this->done($pid, 'tax', $request);

        return back()->with('success', 'Tax rule saved.');
    }

    public function gateway(Request $request)
    {
        $this->requireSystemConfigurationAccess($request);
        $pid = $this->propertyId($request);

        $data = $request->validate([
            'gateway' => 'required|in:mtn_momo,airtel_money,pesapal,flutterwave,card,bank_transfer',
            'label' => 'required',
        ]);

        $existing = PaymentGatewayConfig::firstOrNew([
            'property_id' => $pid,
            'gateway' => $data['gateway'],
        ]);

        $credentials = $existing->credentials ?? [];

        foreach ([
            'client_id', 'client_secret', 'api_key', 'api_user',
            'subscription_key', 'consumer_key', 'consumer_secret',
            'webhook_secret', 'ipn_id',
        ] as $key) {
            if ($request->filled($key)) {
                $credentials[$key] = $request->input($key);
            }
        }

        $existing->fill([
            'label' => $data['label'],
            'enabled' => $request->boolean('enabled'),
            'sandbox' => $request->boolean('sandbox'),
            'credentials' => $credentials,
            'settings' => ['currency' => $request->input('currency', 'UGX')],
        ])->save();

        $this->done($pid, 'payments', $request);

        return back()->with('success', 'Payment gateway configuration saved. Secrets are stored encrypted.');
    }

    public function printer(Request $request)
    {
        $this->requireSystemConfigurationAccess($request);
        $pid = $this->propertyId($request);

        $data = $request->validate([
            'name' => 'required',
            'purpose' => 'required|in:front_desk,receipt,kitchen,bar,invoice,other',
            'connection' => 'required|in:network,usb,bluetooth,browser',
            'paper_size' => 'required',
            'address' => 'nullable',
            'port' => 'nullable|integer|min:1|max:65535',
        ]);

        PrinterConfig::create($data + [
            'property_id' => $pid,
            'is_default' => $request->boolean('is_default'),
            'is_active' => true,
        ]);

        $this->done($pid, 'printers', $request);

        return back()->with('success', 'Printer configured.');
    }

    public function provider(Request $request)
    {
        $this->requireSystemConfigurationAccess($request);
        $pid = $this->propertyId($request);

        $data = $request->validate([
            'channel' => 'required|in:email,sms,whatsapp,push',
            'provider' => 'required',
        ]);

        $existing = NotificationProviderConfig::firstOrNew([
            'property_id' => $pid,
            'channel' => $data['channel'],
        ]);

        $credentials = $existing->credentials ?? [];

        foreach ([
            'host', 'username', 'password', 'api_key', 'sender_id',
            'project_id', 'access_token', 'from_address',
        ] as $key) {
            if ($request->filled($key)) {
                $credentials[$key] = $request->input($key);
            }
        }

        $existing->fill([
            'provider' => $data['provider'],
            'enabled' => $request->boolean('enabled'),
            'credentials' => $credentials,
            'settings' => [],
        ])->save();

        $this->done($pid, 'notifications', $request);

        return back()->with('success', 'Notification provider saved.');
    }

    private function done(int $pid, string $step, Request $request): void
    {
        OnboardingState::updateOrCreate(
            ['property_id' => $pid, 'step' => $step],
            [
                'completed' => true,
                'completed_at' => now(),
                'completed_by' => $request->user()->id,
            ]
        );
    }
}
