@extends('layouts.app')

@section('title', 'Hotel Configuration')
@section('page_title', 'Hotel Configuration')

@php
    $taxes = isset($taxes) ? collect($taxes) : collect();
    $gateways = isset($gateways) ? collect($gateways) : collect();
    $printers = isset($printers) ? collect($printers) : collect();
    $providers = isset($providers) ? collect($providers) : collect();

    $requestedTab = old('_config_tab', request('tab', 'taxes'));

    $activeTab = in_array(
        $requestedTab,
        ['taxes', 'gateways', 'printers', 'providers'],
        true
    )
        ? $requestedTab
        : 'taxes';
@endphp

@section('content')
<div class="configuration-page">

    {{-- ============================================================
       PAGE HEADER
       ============================================================ --}}
    <div class="page-head configuration-head">
        <div>
            <span class="eyebrow">
                <i class="fas fa-screwdriver-wrench"></i>
                Administration
            </span>

            <h1>Hotel configuration</h1>

            <p>
                Configure taxes, payment integrations, printing and
                notification services from one workspace.
            </p>
        </div>

        <div class="configuration-head-meta">
            <span class="badge info">
                <i class="fas fa-building"></i>
                {{ auth()->user()?->property?->name ?? 'Main Hotel' }}
            </span>
        </div>
    </div>

    {{-- ============================================================
       CONFIGURATION TABS
       ============================================================ --}}
    <div
        class="configuration-tabs"
        role="tablist"
        aria-label="Hotel configuration sections"
    >
        <button
            type="button"
            class="configuration-tab {{ $activeTab === 'taxes' ? 'active' : '' }}"
            data-config-tab="taxes"
            role="tab"
            aria-selected="{{ $activeTab === 'taxes' ? 'true' : 'false' }}"
        >
            <span class="configuration-tab-icon">
                <i class="fas fa-percent"></i>
            </span>

            <span>
                <strong>Tax Rules</strong>
                <small>{{ $taxes->count() }} configured</small>
            </span>
        </button>

        <button
            type="button"
            class="configuration-tab {{ $activeTab === 'gateways' ? 'active' : '' }}"
            data-config-tab="gateways"
            role="tab"
            aria-selected="{{ $activeTab === 'gateways' ? 'true' : 'false' }}"
        >
            <span class="configuration-tab-icon">
                <i class="fas fa-credit-card"></i>
            </span>

            <span>
                <strong>Payment Gateways</strong>
                <small>{{ $gateways->count() }} configured</small>
            </span>
        </button>

        <button
            type="button"
            class="configuration-tab {{ $activeTab === 'printers' ? 'active' : '' }}"
            data-config-tab="printers"
            role="tab"
            aria-selected="{{ $activeTab === 'printers' ? 'true' : 'false' }}"
        >
            <span class="configuration-tab-icon">
                <i class="fas fa-print"></i>
            </span>

            <span>
                <strong>Printers</strong>
                <small>{{ $printers->count() }} configured</small>
            </span>
        </button>

        <button
            type="button"
            class="configuration-tab {{ $activeTab === 'providers' ? 'active' : '' }}"
            data-config-tab="providers"
            role="tab"
            aria-selected="{{ $activeTab === 'providers' ? 'true' : 'false' }}"
        >
            <span class="configuration-tab-icon">
                <i class="fas fa-bell"></i>
            </span>

            <span>
                <strong>Notification Providers</strong>
                <small>{{ $providers->count() }} configured</small>
            </span>
        </button>
    </div>

    <div class="configuration-tab-shell">

        {{-- ========================================================
           TAX RULES
           ======================================================== --}}
        <section
            class="configuration-panel {{ $activeTab === 'taxes' ? 'active' : '' }}"
            data-config-panel="taxes"
            role="tabpanel"
        >
            <div class="configuration-panel-head">
                <div>
                    <h2>
                        <i class="fas fa-percent"></i>
                        Tax rules
                    </h2>

                    <p>
                        Set VAT, service charge and other taxes with
                        effective dates and inclusive/exclusive treatment.
                    </p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.tax') }}"
                class="configuration-form"
            >
                @csrf

                <input
                    type="hidden"
                    name="_config_tab"
                    value="taxes"
                >

                <div class="configuration-form-grid">

                    <div class="form-group">
                        <label for="tax-name">Tax name</label>

                        <input
                            id="tax-name"
                            type="text"
                            name="name"
                            value="{{ old('name', 'VAT') }}"
                            placeholder="VAT"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="tax-code">Code / label</label>

                        <input
                            id="tax-code"
                            type="text"
                            name="code"
                            value="{{ old('code', 'VAT') }}"
                            placeholder="VAT"
                        >
                    </div>

                    <div class="form-group">
                        <label for="tax-rate">Rate</label>

                        <div class="input-with-suffix">
                            <input
                                id="tax-rate"
                                type="number"
                                step="0.0001"
                                min="0"
                                name="rate"
                                value="{{ old('rate', 0) }}"
                                required
                            >

                            <span>%</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tax-type">Tax type</label>

                        <select
                            id="tax-type"
                            name="type"
                            required
                        >
                            <option
                                value="percentage"
                                @selected(old('type', 'percentage') === 'percentage')
                            >
                                Percentage
                            </option>

                            <option
                                value="fixed"
                                @selected(old('type') === 'fixed')
                            >
                                Fixed amount
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="effective-from">
                            Effective from
                        </label>

                        <input
                            id="effective-from"
                            type="date"
                            name="effective_from"
                            value="{{ old('effective_from') }}"
                        >
                    </div>

                    <div class="form-group">
                        <label for="effective-to">
                            Effective to
                        </label>

                        <input
                            id="effective-to"
                            type="date"
                            name="effective_to"
                            value="{{ old('effective_to') }}"
                        >
                    </div>
                </div>

                <div class="configuration-toggles">
                    <label class="switch-row">
                        <input
                            type="checkbox"
                            name="inclusive"
                            value="1"
                            @checked(old('inclusive'))
                        >

                        <span>
                            <strong>Inclusive</strong>
                            <small>
                                Tax is already included in the selling price.
                            </small>
                        </span>
                    </label>

                    <label class="switch-row">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', true))
                        >

                        <span>
                            <strong>Active</strong>
                            <small>
                                Make this tax available for hotel transactions.
                            </small>
                        </span>
                    </label>
                </div>

                <div class="configuration-actions">
                    <button
                        type="submit"
                        class="btn primary"
                    >
                        <i class="fas fa-floppy-disk"></i>
                        Save tax
                    </button>
                </div>
            </form>

            <div class="configuration-list">
                <div class="configuration-list-head">
                    <strong>Configured tax rules</strong>
                    <span>{{ $taxes->count() }}</span>
                </div>

                @forelse($taxes as $tax)
                    <div class="configuration-list-row">
                        <div class="configuration-list-icon">
                            <i class="fas fa-percent"></i>
                        </div>

                        <div class="configuration-list-copy">
                            <strong>
                                {{ $tax->name ?? $tax->code ?? 'Tax' }}
                            </strong>

                            <small>
                                {{ number_format((float) ($tax->rate ?? 0), 4) }}
                                @if(
                                    ($tax->type ?? 'percentage')
                                    === 'percentage'
                                )
                                    %
                                @endif

                                .
                                {{ !empty($tax->is_active) ? 'Active' : 'Inactive' }}
                            </small>
                        </div>

                        <span
                            class="badge {{ !empty($tax->is_active) ? 'success' : 'warning' }}"
                        >
                            {{ !empty($tax->is_active) ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                @empty
                    <div class="configuration-empty">
                        <i class="fas fa-percent"></i>

                        <div>
                            <strong>No tax rules configured</strong>
                            <small>
                                Use the form above to create the first rule.
                            </small>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- ========================================================
           PAYMENT GATEWAYS
           ======================================================== --}}
        <section
            class="configuration-panel {{ $activeTab === 'gateways' ? 'active' : '' }}"
            data-config-panel="gateways"
            role="tabpanel"
        >
            <div class="configuration-panel-head">
                <div>
                    <h2>
                        <i class="fas fa-credit-card"></i>
                        Payment gateways
                    </h2>

                    <p>
                        Configure mobile money and online payment
                        integrations used by hotel transactions.
                    </p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.gateway') }}"
                class="configuration-form"
            >
                @csrf

                <input
                    type="hidden"
                    name="_config_tab"
                    value="gateways"
                >

                <div class="configuration-form-grid">

                    <div class="form-group">
                        <label for="gateway-provider">
                            Gateway
                        </label>

                        <select
                            id="gateway-provider"
                            name="provider"
                            required
                        >
                            @foreach([
                                'mtn_momo' => 'MTN MoMo',
                                'airtel_money' => 'Airtel Money',
                                'pesapal' => 'Pesapal',
                                'flutterwave' => 'Flutterwave',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(old('provider', 'mtn_momo') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="gateway-label">
                            Display label
                        </label>

                        <input
                            id="gateway-label"
                            type="text"
                            name="label"
                            value="{{ old('label') }}"
                            placeholder="Display label"
                        >
                    </div>

                    <div class="form-group">
                        <label for="gateway-username">
                            Username / account
                        </label>

                        <input
                            id="gateway-username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            autocomplete="off"
                            placeholder="Account username"
                        >
                    </div>

                    <div class="form-group">
                        <label for="gateway-password">
                            Password / secret
                        </label>

                        <div class="password-field">
                            <input
                                id="gateway-password"
                                type="password"
                                name="password"
                                autocomplete="new-password"
                                placeholder="Password / secret"
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                data-password-toggle
                                aria-label="Show or hide password"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="gateway-api-key">
                            API key / Flutterwave secret key
                        </label>

                        <div class="password-field">
                            <input
                                id="gateway-api-key"
                                type="password"
                                name="api_key"
                                value="{{ old('api_key') }}"
                                autocomplete="off"
                                placeholder="API key"
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                data-password-toggle
                                aria-label="Show or hide API key"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="gateway-client-id">Client ID (Airtel)</label>
                        <input id="gateway-client-id" type="password" name="client_id" autocomplete="new-password" placeholder="Leave blank to keep saved value">
                    </div>

                    <div class="form-group">
                        <label for="gateway-client-secret">Client secret (Airtel)</label>
                        <input id="gateway-client-secret" type="password" name="client_secret" autocomplete="new-password" placeholder="Leave blank to keep saved value">
                    </div>

                    <div class="form-group">
                        <label for="gateway-consumer-key">Consumer key (Pesapal)</label>
                        <input id="gateway-consumer-key" type="password" name="consumer_key" autocomplete="new-password" placeholder="Leave blank to keep saved value">
                    </div>

                    <div class="form-group">
                        <label for="gateway-consumer-secret">Consumer secret (Pesapal)</label>
                        <input id="gateway-consumer-secret" type="password" name="consumer_secret" autocomplete="new-password" placeholder="Leave blank to keep saved value">
                    </div>

                    <div class="form-group">
                        <label for="gateway-api-user">MTN API user</label>
                        <input id="gateway-api-user" type="password" name="api_user" autocomplete="new-password" placeholder="Leave blank to keep saved value">
                    </div>

                    <div class="form-group">
                        <label for="gateway-webhook-secret">Flutterwave webhook secret</label>
                        <input id="gateway-webhook-secret" type="password" name="webhook_secret" autocomplete="new-password" placeholder="Leave blank to keep saved value">
                    </div>

                    <div class="form-group">
                        <label for="gateway-base-url">API base URL (optional)</label>
                        <input id="gateway-base-url" type="url" name="base_url" placeholder="Leave blank for provider default">
                    </div>

                    <div class="form-group">
                        <label for="gateway-subscription-key">
                            Subscription key
                        </label>

                        <div class="password-field">
                            <input
                                id="gateway-subscription-key"
                                type="password"
                                name="subscription_key"
                                value="{{ old('subscription_key') }}"
                                autocomplete="off"
                                placeholder="Subscription key"
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                data-password-toggle
                                aria-label="Show or hide subscription key"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="gateway-currency">
                            Currency
                        </label>

                        <input
                            id="gateway-currency"
                            type="text"
                            name="currency"
                            value="{{ old('currency', 'UGX') }}"
                            maxlength="10"
                            placeholder="UGX"
                        >
                    </div>

                    <div class="form-group">
                        <label for="gateway-callback">
                            Callback / webhook URL
                        </label>

                        <input
                            id="gateway-callback"
                            type="url"
                            name="callback_url"
                            value="{{ old('callback_url') }}"
                            placeholder="https://..."
                        >
                    </div>
                </div>

                <div class="configuration-toggles">
                    <label class="switch-row">
                        <input
                            type="checkbox"
                            name="sandbox"
                            value="1"
                            @checked(old('sandbox', true))
                        >

                        <span>
                            <strong>Sandbox / test mode</strong>
                            <small>
                                Use test credentials instead of live payments.
                            </small>
                        </span>
                    </label>

                    <label class="switch-row">
                        <input
                            type="checkbox"
                            name="enabled"
                            value="1"
                            @checked(old('is_active', true))
                        >

                        <span>
                            <strong>Active</strong>
                            <small>
                                Allow this gateway to process payments.
                            </small>
                        </span>
                    </label>
                </div>

                <div class="configuration-actions">
                    <button
                        type="submit"
                        class="btn primary"
                    >
                        <i class="fas fa-floppy-disk"></i>
                        Save gateway
                    </button>
                </div>
            </form>

            <div class="configuration-list">
                <div class="configuration-list-head">
                    <strong>Configured gateways</strong>
                    <span>{{ $gateways->count() }}</span>
                </div>

                @forelse($gateways as $gateway)
                    @php
                        $gatewayName =
                            $gateway->label
                            ?? $gateway->display_label
                            ?? $gateway->provider
                            ?? $gateway->name
                            ?? 'Gateway';

                        $gatewayProvider =
                            $gateway->provider
                            ?? $gateway->type
                            ?? '';
                    @endphp

                    <div class="configuration-list-row">
                        <div class="configuration-list-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>

                        <div class="configuration-list-copy">
                            <strong>{{ $gatewayName }}</strong>

                            <small>
                                {{ ucwords(str_replace('_', ' ', $gatewayProvider)) }}

                                @if(!empty($gateway->currency))
                                    . {{ $gateway->currency }}
                                @endif

                                @if(
                                    !empty($gateway->sandbox)
                                    || !empty($gateway->sandbox_mode)
                                )
                                    . Sandbox
                                @else
                                    . Live
                                @endif
                            </small>
                        </div>

                        <span
                            class="badge {{ !empty($gateway->is_active) || !empty($gateway->is_enabled) ? 'success' : 'warning' }}"
                        >
                            {{
                                !empty($gateway->is_active)
                                || !empty($gateway->is_enabled)
                                    ? 'Active'
                                    : 'Inactive'
                            }}
                        </span>
                    </div>
                @empty
                    <div class="configuration-empty">
                        <i class="fas fa-credit-card"></i>

                        <div>
                            <strong>No payment gateways configured</strong>
                            <small>
                                Add MTN MoMo, Airtel Money, Pesapal or Flutterwave.
                            </small>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- ========================================================
           PRINTERS
           ======================================================== --}}
        <section
            class="configuration-panel {{ $activeTab === 'printers' ? 'active' : '' }}"
            data-config-panel="printers"
            role="tabpanel"
        >
            <div class="configuration-panel-head">
                <div>
                    <h2>
                        <i class="fas fa-print"></i>
                        Printers
                    </h2>

                    <p>
                        Configure front-office, POS, kitchen and receipt
                        printers.
                    </p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.printer') }}"
                class="configuration-form"
            >
                @csrf

                <input
                    type="hidden"
                    name="_config_tab"
                    value="printers"
                >

                <div class="configuration-form-grid">

                    <div class="form-group">
                        <label for="printer-name">
                            Printer name
                        </label>

                        <input
                            id="printer-name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Front Desk Receipt Printer"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="printer-type">
                            Printer type
                        </label>

                        <select
                            id="printer-type"
                            name="type"
                            required
                        >
                            <option value="receipt">
                                Receipt
                            </option>

                            <option value="kitchen">
                                Kitchen
                            </option>

                            <option value="invoice">
                                Invoice / A4
                            </option>

                            <option value="general">
                                General
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="printer-connection">
                            Connection
                        </label>

                        <select
                            id="printer-connection"
                            name="connection_type"
                        >
                            <option value="network">
                                Network
                            </option>

                            <option value="usb">
                                USB
                            </option>

                            <option value="bluetooth">
                                Bluetooth
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="printer-address">
                            IP / device address
                        </label>

                        <input
                            id="printer-address"
                            type="text"
                            name="address"
                            value="{{ old('address') }}"
                            placeholder="192.168.1.50 or USB name"
                        >
                    </div>

                    <div class="form-group">
                        <label for="printer-port">
                            Port
                        </label>

                        <input
                            id="printer-port"
                            type="number"
                            name="port"
                            value="{{ old('port', 9100) }}"
                            min="1"
                            max="65535"
                        >
                    </div>

                    <div class="form-group">
                        <label for="printer-paper">
                            Paper size
                        </label>

                        <select
                            id="printer-paper"
                            name="paper_size"
                        >
                            <option value="80mm">
                                80 mm thermal
                            </option>

                            <option value="58mm">
                                58 mm thermal
                            </option>

                            <option value="a4">
                                A4
                            </option>
                        </select>
                    </div>
                </div>

                <div class="configuration-toggles">
                    <label class="switch-row">
                        <input
                            type="checkbox"
                            name="is_default"
                            value="1"
                            @checked(old('is_default'))
                        >

                        <span>
                            <strong>Default printer</strong>
                            <small>
                                Use this printer automatically for its document type.
                            </small>
                        </span>
                    </label>

                    <label class="switch-row">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', true))
                        >

                        <span>
                            <strong>Active</strong>
                            <small>
                                Make the printer available to hotel users.
                            </small>
                        </span>
                    </label>
                </div>

                <div class="configuration-actions">
                    <button
                        type="submit"
                        class="btn primary"
                    >
                        <i class="fas fa-floppy-disk"></i>
                        Save printer
                    </button>
                </div>
            </form>

            <div class="configuration-list">
                <div class="configuration-list-head">
                    <strong>Configured printers</strong>
                    <span>{{ $printers->count() }}</span>
                </div>

                @forelse($printers as $printer)
                    <div class="configuration-list-row">
                        <div class="configuration-list-icon">
                            <i class="fas fa-print"></i>
                        </div>

                        <div class="configuration-list-copy">
                            <strong>
                                {{ $printer->name ?? 'Printer' }}
                            </strong>

                            <small>
                                {{ ucwords(str_replace('_', ' ', $printer->type ?? 'general')) }}

                                @if(!empty($printer->address))
                                    . {{ $printer->address }}
                                @endif
                            </small>
                        </div>

                        <span
                            class="badge {{ !empty($printer->is_active) ? 'success' : 'warning' }}"
                        >
                            {{ !empty($printer->is_active) ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                @empty
                    <div class="configuration-empty">
                        <i class="fas fa-print"></i>

                        <div>
                            <strong>No printers configured</strong>
                            <small>
                                Add front desk, POS or kitchen printers.
                            </small>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- ========================================================
           NOTIFICATION PROVIDERS
           ======================================================== --}}
        <section
            class="configuration-panel {{ $activeTab === 'providers' ? 'active' : '' }}"
            data-config-panel="providers"
            role="tabpanel"
        >
            <div class="configuration-panel-head">
                <div>
                    <h2>
                        <i class="fas fa-bell"></i>
                        Notification providers
                    </h2>

                    <p>
                        Configure email, SMS, WhatsApp and other services used
                        for staff and guest alerts.
                    </p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.provider') }}"
                class="configuration-form"
            >
                @csrf

                <input
                    type="hidden"
                    name="_config_tab"
                    value="providers"
                >

                <div class="configuration-form-grid">

                    <div class="form-group">
                        <label for="provider-type">
                            Provider type
                        </label>

                        <select
                            id="provider-type"
                            name="type"
                            required
                        >
                            <option value="email">
                                Email
                            </option>

                            <option value="sms">
                                SMS
                            </option>

                            <option value="whatsapp">
                                WhatsApp
                            </option>

                            <option value="push">
                                Push notification
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="provider-name">
                            Provider name
                        </label>

                        <input
                            id="provider-name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="SMTP / Africa's Talking / Twilio"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="provider-host">
                            Host / API endpoint
                        </label>

                        <input
                            id="provider-host"
                            type="text"
                            name="host"
                            value="{{ old('host') }}"
                            placeholder="smtp.example.com"
                        >
                    </div>

                    <div class="form-group">
                        <label for="provider-port">
                            Port
                        </label>

                        <input
                            id="provider-port"
                            type="number"
                            name="port"
                            value="{{ old('port') }}"
                            min="1"
                            max="65535"
                        >
                    </div>

                    <div class="form-group">
                        <label for="provider-username">
                            Username
                        </label>

                        <input
                            id="provider-username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            autocomplete="off"
                        >
                    </div>

                    <div class="form-group">
                        <label for="provider-password">
                            Password / API secret
                        </label>

                        <div class="password-field">
                            <input
                                id="provider-password"
                                type="password"
                                name="password"
                                autocomplete="new-password"
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                data-password-toggle
                                aria-label="Show or hide password"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="provider-from">
                            Sender / from address
                        </label>

                        <input
                            id="provider-from"
                            type="text"
                            name="from_address"
                            value="{{ old('from_address') }}"
                            placeholder="hotel@example.com"
                        >
                    </div>

                    <div class="form-group">
                        <label for="provider-key">
                            API key
                        </label>

                        <div class="password-field">
                            <input
                                id="provider-key"
                                type="password"
                                name="api_key"
                                value="{{ old('api_key') }}"
                                autocomplete="off"
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                data-password-toggle
                                aria-label="Show or hide API key"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="configuration-toggles">
                    <label class="switch-row">
                        <input
                            type="checkbox"
                            name="is_default"
                            value="1"
                            @checked(old('is_default'))
                        >

                        <span>
                            <strong>Default provider</strong>
                            <small>
                                Use this provider by default for its notification type.
                            </small>
                        </span>
                    </label>

                    <label class="switch-row">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', true))
                        >

                        <span>
                            <strong>Active</strong>
                            <small>
                                Enable this provider for notifications.
                            </small>
                        </span>
                    </label>
                </div>

                <div class="configuration-actions">
                    <button
                        type="submit"
                        class="btn primary"
                    >
                        <i class="fas fa-floppy-disk"></i>
                        Save provider
                    </button>
                </div>
            </form>

            <div class="configuration-list">
                <div class="configuration-list-head">
                    <strong>Configured providers</strong>
                    <span>{{ $providers->count() }}</span>
                </div>

                @forelse($providers as $provider)
                    <div class="configuration-list-row">
                        <div class="configuration-list-icon">
                            <i class="fas fa-bell"></i>
                        </div>

                        <div class="configuration-list-copy">
                            <strong>
                                {{ $provider->name ?? 'Provider' }}
                            </strong>

                            <small>
                                {{ ucwords(str_replace('_', ' ', $provider->type ?? 'notification')) }}

                                @if(!empty($provider->host))
                                    . {{ $provider->host }}
                                @endif
                            </small>
                        </div>

                        <span
                            class="badge {{ !empty($provider->is_active) ? 'success' : 'warning' }}"
                        >
                            {{ !empty($provider->is_active) ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                @empty
                    <div class="configuration-empty">
                        <i class="fas fa-bell"></i>

                        <div>
                            <strong>No providers configured</strong>
                            <small>
                                Add email, SMS, WhatsApp or push providers.
                            </small>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
    .configuration-page {
        width: 100%;
        max-width: 100%;
    }

    .configuration-head {
        margin-bottom: 16px;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
    }

    .configuration-head h1 {
        margin: 5px 0 4px;
        font-size: 22px;
    }

    .configuration-head p {
        max-width: 680px;
        margin: 0;
        color: var(--hms-muted);
        font-size: 10.5px;
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--hms-primary);
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .configuration-tabs {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 0;
    }

    .configuration-tab {
        appearance: none;
        min-width: 0;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        gap: 9px;
        border: 1px solid var(--hms-border);
        border-bottom: 2px solid transparent;
        border-radius: 10px 10px 0 0;
        background: #f7f9fa;
        color: #55626d;
        text-align: left;
        cursor: pointer;
        transition:
            background .16s ease,
            border-color .16s ease,
            color .16s ease;
    }

    .configuration-tab:hover {
        background: #f0f6f5;
        color: var(--hms-primary);
    }

    .configuration-tab.active {
        position: relative;
        z-index: 2;
        border-color: var(--hms-border);
        border-bottom-color: #fff;
        background: #fff;
        color: var(--hms-primary);
    }

    .configuration-tab-icon {
        flex: 0 0 33px;
        width: 33px;
        height: 33px;
        display: grid;
        place-items: center;
        border-radius: 9px;
        background: var(--hms-primary-soft);
        color: var(--hms-primary);
    }

    .configuration-tab strong,
    .configuration-tab small {
        display: block;
    }

    .configuration-tab strong {
        overflow: hidden;
        font-size: 10.5px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .configuration-tab small {
        margin-top: 1px;
        color: #87919b;
        font-size: 8px;
    }

    .configuration-tab-shell {
        position: relative;
        z-index: 1;
        border: 1px solid var(--hms-border);
        border-radius: 0 0 12px 12px;
        background: #fff;
    }

    .configuration-panel {
        display: none;
        padding: 18px;
    }

    .configuration-panel.active {
        display: block;
    }

    .configuration-panel-head {
        margin-bottom: 16px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .configuration-panel-head h2 {
        margin: 0 0 3px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 16px;
    }

    .configuration-panel-head h2 i {
        color: var(--hms-primary);
    }

    .configuration-panel-head p {
        max-width: 650px;
        margin: 0;
        color: var(--hms-muted);
        font-size: 9.5px;
    }

    .configuration-form {
        padding: 15px;
        border: 1px solid var(--hms-border-soft);
        border-radius: 10px;
        background: #fbfcfd;
    }

    .configuration-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 11px 12px;
    }

    .configuration-form .form-group {
        min-width: 0;
    }

    .configuration-form label {
        display: block;
        margin-bottom: 5px;
        color: #34414c;
        font-size: 9.5px;
        font-weight: 600;
    }

    .configuration-form input,
    .configuration-form select,
    .configuration-form textarea {
        width: 100%;
        min-height: 37px;
        margin: 0;
        padding: 7px 9px;
        border: 1px solid #d7dee3;
        border-radius: 7px;
        background: #fff;
        color: #24303b;
        font-size: 10.5px;
    }

    .configuration-form input:focus,
    .configuration-form select:focus,
    .configuration-form textarea:focus {
        outline: none;
        border-color: var(--hms-primary);
        box-shadow: 0 0 0 3px var(--hms-primary-soft);
    }

    .input-with-suffix {
        position: relative;
    }

    .input-with-suffix input {
        padding-right: 38px;
    }

    .input-with-suffix > span {
        position: absolute;
        top: 50%;
        right: 12px;
        color: #78848e;
        font-size: 10px;
        transform: translateY(-50%);
    }

    .password-field {
        position: relative;
    }

    .password-field input {
        padding-right: 40px;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 5px;
        width: 30px;
        height: 30px;
        padding: 0;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: #74808a;
        cursor: pointer;
        transform: translateY(-50%);
    }

    .password-toggle:hover {
        background: var(--hms-primary-soft);
        color: var(--hms-primary);
    }

    .configuration-toggles {
        margin-top: 12px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
    }

    .switch-row {
        margin: 0 !important;
        padding: 10px;
        display: flex !important;
        align-items: center;
        gap: 9px;
        border: 1px solid var(--hms-border-soft);
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
    }

    .switch-row input {
        flex: 0 0 17px;
        width: 17px !important;
        height: 17px !important;
        min-height: 0 !important;
        margin: 0;
        accent-color: var(--hms-primary);
    }

    .switch-row strong,
    .switch-row small {
        display: block;
    }

    .switch-row strong {
        color: #27333d;
        font-size: 9.5px;
    }

    .switch-row small {
        margin-top: 1px;
        color: var(--hms-muted);
        font-size: 8px;
        font-weight: 400;
    }

    .configuration-actions {
        margin-top: 13px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .configuration-list {
        margin-top: 15px;
        overflow: hidden;
        border: 1px solid var(--hms-border);
        border-radius: 10px;
        background: #fff;
    }

    .configuration-list-head {
        min-height: 39px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--hms-border-soft);
        background: #f8fafb;
        font-size: 9.5px;
    }

    .configuration-list-head > span {
        min-width: 24px;
        height: 24px;
        padding: 0 7px;
        display: inline-grid;
        place-items: center;
        border-radius: 999px;
        background: var(--hms-primary-soft);
        color: var(--hms-primary);
        font-size: 8px;
        font-weight: 700;
    }

    .configuration-list-row {
        min-height: 52px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 1px solid var(--hms-border-soft);
    }

    .configuration-list-row:last-child {
        border-bottom: 0;
    }

    .configuration-list-icon {
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: var(--hms-primary-soft);
        color: var(--hms-primary);
    }

    .configuration-list-copy {
        min-width: 0;
        flex: 1;
    }

    .configuration-list-copy strong,
    .configuration-list-copy small {
        display: block;
    }

    .configuration-list-copy strong {
        overflow: hidden;
        color: #24303b;
        font-size: 9.8px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .configuration-list-copy small {
        margin-top: 2px;
        color: var(--hms-muted);
        font-size: 8px;
    }

    .configuration-empty {
        min-height: 90px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: var(--hms-muted);
    }

    .configuration-empty > i {
        font-size: 18px;
    }

    .configuration-empty strong,
    .configuration-empty small {
        display: block;
    }

    .configuration-empty strong {
        color: #33414c;
        font-size: 9.5px;
    }

    .configuration-empty small {
        margin-top: 2px;
        font-size: 8px;
    }

    @media (max-width: 900px) {
        .configuration-tabs {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .configuration-tab {
            border-radius: 9px;
        }

        .configuration-tab.active {
            border-bottom-color: var(--hms-primary);
        }

        .configuration-tab-shell {
            margin-top: 8px;
            border-radius: 10px;
        }
    }

    @media (max-width: 680px) {
        .configuration-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .configuration-tabs {
            display: flex;
            overflow-x: auto;
            gap: 6px;
            padding-bottom: 3px;
        }

        .configuration-tab {
            flex: 0 0 165px;
        }

        .configuration-panel {
            padding: 12px;
        }

        .configuration-form {
            padding: 12px;
        }

        .configuration-form-grid,
        .configuration-toggles {
            grid-template-columns: 1fr;
        }

        .configuration-actions {
            justify-content: stretch;
        }

        .configuration-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = Array.from(
        document.querySelectorAll('[data-config-tab]')
    );

    const panels = Array.from(
        document.querySelectorAll('[data-config-panel]')
    );

    function activateTab(name, updateUrl = true) {
        tabs.forEach(function (tab) {
            const active =
                tab.dataset.configTab === name;

            tab.classList.toggle(
                'active',
                active
            );

            tab.setAttribute(
                'aria-selected',
                active ? 'true' : 'false'
            );
        });

        panels.forEach(function (panel) {
            panel.classList.toggle(
                'active',
                panel.dataset.configPanel === name
            );
        });

        document
            .querySelectorAll(
                'input[name="_config_tab"]'
            )
            .forEach(function (field) {
                if (
                    field.closest(
                        '[data-config-panel="' + name + '"]'
                    )
                ) {
                    field.value = name;
                }
            });

        if (updateUrl) {
            const url =
                new URL(window.location.href);

            url.searchParams.set(
                'tab',
                name
            );

            window.history.replaceState(
                {},
                '',
                url
            );
        }

        try {
            window.localStorage.setItem(
                'hms_configuration_tab',
                name
            );
        } catch (_) {
            // Storage is optional.
        }
    }

    tabs.forEach(function (tab) {
        tab.addEventListener(
            'click',
            function () {
                activateTab(
                    tab.dataset.configTab
                );
            }
        );
    });

    const urlTab =
        new URL(window.location.href)
            .searchParams
            .get('tab');

    let savedTab = null;

    try {
        savedTab =
            window.localStorage.getItem(
                'hms_configuration_tab'
            );
    } catch (_) {
        savedTab = null;
    }

    const serverTab =
        @json($activeTab);

    const initialTab =
        urlTab
        || serverTab
        || savedTab
        || 'taxes';

    if (
        tabs.some(
            tab =>
                tab.dataset.configTab
                === initialTab
        )
    ) {
        activateTab(
            initialTab,
            false
        );
    }

    document
        .querySelectorAll(
            '[data-password-toggle]'
        )
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    const field =
                        button
                            .closest(
                                '.password-field'
                            )
                            ?.querySelector(
                                'input'
                            );

                    if (!field) {
                        return;
                    }

                    const hidden =
                        field.type
                        === 'password';

                    field.type =
                        hidden
                            ? 'text'
                            : 'password';

                    const icon =
                        button.querySelector('i');

                    if (icon) {
                        icon.classList.toggle(
                            'fa-eye',
                            !hidden
                        );

                        icon.classList.toggle(
                            'fa-eye-slash',
                            hidden
                        );
                    }
                }
            );
        });
});
</script>
@endpush
