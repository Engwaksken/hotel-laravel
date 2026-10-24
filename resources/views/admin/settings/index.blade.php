@extends('layouts.app')

@section('title', 'System Configuration')
@section('page_title', 'System Configuration')

@php
    $tab = $activeTab ?? request('tab', 'general');

    $getSetting = static fn (
        string $key,
        mixed $default = null
    ) => \App\Support\SystemSetting::get($key, $default);

    $systemName = $getSetting(
        'system_name',
        config('app.name', 'Hotel Management System')
    );

    $systemShortName = $getSetting(
        'system_short_name',
        'Hotel HMS'
    );

    $primaryColor = $getSetting(
        'primary_color',
        '#0f766e'
    );

    $secondaryColor = $getSetting(
        'secondary_color',
        '#134e4a'
    );

    $accentColor = $getSetting(
        'accent_color',
        '#14b8a6'
    );

    $logoPath = $getSetting('logo_path');
    $faviconPath = $getSetting('favicon_path');
@endphp

@section('content')
<div class="system-config-page">

    <div class="page-head system-config-head">
        <div>
            <span class="eyebrow">
                <i class="fas fa-palette"></i>
                Super Admin
            </span>

            <h1>System configuration</h1>

            <p>
                Manage the hotel system name, branding colours, logo,
                favicon and invoice/receipt presentation.
            </p>
        </div>
    </div>

    @if(empty($settingsReady))
        <div class="alert warning">
            <i class="fas fa-triangle-exclamation"></i>

            <div>
                <strong>System settings table is missing.</strong>
                <div>
                    Run <code>php artisan migrate --force</code> before saving.
                </div>
            </div>
        </div>
    @endif

    <div
        class="system-config-tabs"
        role="tablist"
        aria-label="System configuration"
    >
        <a
            href="{{ route('admin.system-configuration.index', ['tab' => 'general']) }}"
            class="system-config-tab {{ $tab === 'general' ? 'active' : '' }}"
        >
            <i class="fas fa-sliders"></i>

            <span>
                <strong>General</strong>
                <small>System identity</small>
            </span>
        </a>

        <a
            href="{{ route('admin.system-configuration.index', ['tab' => 'branding']) }}"
            class="system-config-tab {{ $tab === 'branding' ? 'active' : '' }}"
        >
            <i class="fas fa-palette"></i>

            <span>
                <strong>Branding</strong>
                <small>Colours &amp; logo</small>
            </span>
        </a>

        <a
            href="{{ route('admin.system-configuration.index', ['tab' => 'documents']) }}"
            class="system-config-tab {{ $tab === 'documents' ? 'active' : '' }}"
        >
            <i class="fas fa-file-invoice"></i>

            <span>
                <strong>Documents</strong>
                <small>Invoices &amp; receipts</small>
            </span>
        </a>
    </div>

    <div class="system-config-shell">

        @if($tab === 'general')
            <section class="system-config-panel">
                <div class="system-config-panel-head">
                    <div>
                        <h2>General system identity</h2>
                        <p>
                            Change the name and support information displayed
                            throughout the system.
                        </p>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.system-configuration.general') }}"
                >
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div>
                            <label for="system_name">System name</label>

                            <input
                                id="system_name"
                                type="text"
                                name="system_name"
                                value="{{ old('system_name', $systemName) }}"
                                required
                            >
                        </div>

                        <div>
                            <label for="system_short_name">Short name</label>

                            <input
                                id="system_short_name"
                                type="text"
                                name="system_short_name"
                                value="{{ old('system_short_name', $systemShortName) }}"
                            >
                        </div>

                        <div>
                            <label for="support_email">Support email</label>

                            <input
                                id="support_email"
                                type="email"
                                name="support_email"
                                value="{{ old('support_email', $getSetting('support_email')) }}"
                            >
                        </div>

                        <div>
                            <label for="support_phone">Support phone</label>

                            <input
                                id="support_phone"
                                type="text"
                                name="support_phone"
                                value="{{ old('support_phone', $getSetting('support_phone')) }}"
                            >
                        </div>
                    </div>

                    <div class="form-row-full">
                        <label for="company_address">Company address</label>

                        <textarea
                            id="company_address"
                            name="company_address"
                            rows="3"
                        >{{ old('company_address', $getSetting('company_address')) }}</textarea>
                    </div>

                    <div class="form-actions">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            @disabled(empty($settingsReady))
                        >
                            <i class="fas fa-floppy-disk"></i>
                            Save General Settings
                        </button>
                    </div>
                </form>
            </section>
        @endif

        @if($tab === 'branding')
            <section class="system-config-panel">
                <div class="system-config-panel-head">
                    <div>
                        <h2>Branding</h2>
                        <p>
                            Customise the colours, system logo and browser
                            favicon used throughout the hotel system.
                        </p>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.system-configuration.branding') }}"
                    enctype="multipart/form-data"
                >
                    @csrf
                    @method('PUT')

                    <div class="branding-colour-grid">
                        <label class="colour-field">
                            <span>Primary colour</span>

                            <div>
                                <input
                                    type="color"
                                    name="primary_color"
                                    value="{{ old('primary_color', $primaryColor) }}"
                                >

                                <code>{{ old('primary_color', $primaryColor) }}</code>
                            </div>
                        </label>

                        <label class="colour-field">
                            <span>Secondary colour</span>

                            <div>
                                <input
                                    type="color"
                                    name="secondary_color"
                                    value="{{ old('secondary_color', $secondaryColor) }}"
                                >

                                <code>{{ old('secondary_color', $secondaryColor) }}</code>
                            </div>
                        </label>

                        <label class="colour-field">
                            <span>Accent colour</span>

                            <div>
                                <input
                                    type="color"
                                    name="accent_color"
                                    value="{{ old('accent_color', $accentColor) }}"
                                >

                                <code>{{ old('accent_color', $accentColor) }}</code>
                            </div>
                        </label>
                    </div>

                    <div class="branding-assets-grid">
                        <div class="branding-asset-card">
                            <div class="branding-preview logo-preview">
                                @if($logoPath)
                                    <img
                                        src="{{ asset('storage/'.$logoPath) }}"
                                        alt="Current system logo"
                                    >
                                @else
                                    <i class="fas fa-hotel"></i>
                                @endif
                            </div>

                            <div>
                                <label for="logo">System logo</label>

                                <input
                                    id="logo"
                                    type="file"
                                    name="logo"
                                    accept=".png,.jpg,.jpeg,.webp,.svg"
                                >

                                @if($logoPath)
                                    <label class="remove-option">
                                        <input
                                            type="checkbox"
                                            name="remove_logo"
                                            value="1"
                                        >
                                        Remove current logo
                                    </label>
                                @endif
                            </div>
                        </div>

                        <div class="branding-asset-card">
                            <div class="branding-preview favicon-preview">
                                @if($faviconPath)
                                    <img
                                        src="{{ asset('storage/'.$faviconPath) }}"
                                        alt="Current favicon"
                                    >
                                @else
                                    <i class="fas fa-image"></i>
                                @endif
                            </div>

                            <div>
                                <label for="favicon">Favicon</label>

                                <input
                                    id="favicon"
                                    type="file"
                                    name="favicon"
                                    accept=".png,.jpg,.jpeg,.webp,.ico"
                                >

                                @if($faviconPath)
                                    <label class="remove-option">
                                        <input
                                            type="checkbox"
                                            name="remove_favicon"
                                            value="1"
                                        >
                                        Remove current favicon
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="branding-live-preview">
                        <span
                            style="background:{{ $primaryColor }}"
                        >
                            Primary
                        </span>

                        <span
                            style="background:{{ $secondaryColor }}"
                        >
                            Secondary
                        </span>

                        <span
                            style="background:{{ $accentColor }}"
                        >
                            Accent
                        </span>
                    </div>

                    <div class="form-actions">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            @disabled(empty($settingsReady))
                        >
                            <i class="fas fa-floppy-disk"></i>
                            Save Branding
                        </button>
                    </div>
                </form>
            </section>
        @endif

        @if($tab === 'documents')
            <section class="system-config-panel">
                <div class="system-config-panel-head">
                    <div>
                        <h2>Invoice &amp; receipt settings</h2>
                        <p>
                            Set document headings, prefixes, currency and
                            footer information.
                        </p>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.system-configuration.documents') }}"
                >
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div>
                            <label for="invoice_title">Invoice title</label>

                            <input
                                id="invoice_title"
                                type="text"
                                name="invoice_title"
                                value="{{ old('invoice_title', $getSetting('invoice_title', 'INVOICE')) }}"
                            >
                        </div>

                        <div>
                            <label for="receipt_title">Receipt title</label>

                            <input
                                id="receipt_title"
                                type="text"
                                name="receipt_title"
                                value="{{ old('receipt_title', $getSetting('receipt_title', 'RECEIPT')) }}"
                            >
                        </div>

                        <div>
                            <label for="invoice_prefix">Invoice prefix</label>

                            <input
                                id="invoice_prefix"
                                type="text"
                                name="invoice_prefix"
                                value="{{ old('invoice_prefix', $getSetting('invoice_prefix', 'INV')) }}"
                            >
                        </div>

                        <div>
                            <label for="receipt_prefix">Receipt prefix</label>

                            <input
                                id="receipt_prefix"
                                type="text"
                                name="receipt_prefix"
                                value="{{ old('receipt_prefix', $getSetting('receipt_prefix', 'RCT')) }}"
                            >
                        </div>

                        <div>
                            <label for="document_currency">Currency</label>

                            <input
                                id="document_currency"
                                type="text"
                                name="document_currency"
                                value="{{ old('document_currency', $getSetting('document_currency', 'UGX')) }}"
                            >
                        </div>
                    </div>

                    <div class="document-toggle-grid">
                        <label class="toggle-option">
                            <input
                                type="checkbox"
                                name="show_logo_on_documents"
                                value="1"
                                @checked((string) old(
                                    'show_logo_on_documents',
                                    $getSetting('show_logo_on_documents', '1')
                                ) === '1')
                            >

                            <span>
                                <strong>Show logo on documents</strong>
                                <small>
                                    Include the configured system logo on
                                    invoices and receipts.
                                </small>
                            </span>
                        </label>

                        <label class="toggle-option">
                            <input
                                type="checkbox"
                                name="show_qr_on_receipts"
                                value="1"
                                @checked((string) old(
                                    'show_qr_on_receipts',
                                    $getSetting('show_qr_on_receipts', '1')
                                ) === '1')
                            >

                            <span>
                                <strong>Show QR on receipts</strong>
                                <small>
                                    Include supported verification QR codes
                                    on receipts.
                                </small>
                            </span>
                        </label>
                    </div>

                    <div class="form-row-full">
                        <label for="invoice_footer">Invoice footer</label>

                        <textarea
                            id="invoice_footer"
                            name="invoice_footer"
                            rows="3"
                        >{{ old('invoice_footer', $getSetting('invoice_footer')) }}</textarea>
                    </div>

                    <div class="form-row-full">
                        <label for="receipt_footer">Receipt footer</label>

                        <textarea
                            id="receipt_footer"
                            name="receipt_footer"
                            rows="3"
                        >{{ old('receipt_footer', $getSetting('receipt_footer')) }}</textarea>
                    </div>

                    <div class="form-actions">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            @disabled(empty($settingsReady))
                        >
                            <i class="fas fa-floppy-disk"></i>
                            Save Document Settings
                        </button>
                    </div>
                </form>
            </section>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.system-config-head {
    margin-bottom: 14px;
}

.system-config-head h1 {
    margin: 4px 0 3px;
    font-size: 21px;
}

.system-config-head p {
    max-width: 700px;
    margin: 0;
    color: var(--hms-muted);
    font-size: 10px;
}

.eyebrow {
    color: var(--hms-primary);
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.system-config-tabs {
    display: flex;
    align-items: stretch;
    overflow-x: auto;
    border-bottom: 1px solid var(--hms-border);
}

.system-config-tab {
    flex: 0 0 180px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid transparent;
    border-bottom: 0;
    border-radius: 9px 9px 0 0;
    color: #5f6c77;
    background: transparent;
    text-decoration: none;
}

.system-config-tab.active {
    border-color: var(--hms-border);
    background: #fff;
    color: var(--hms-primary);
}

.system-config-tab > i {
    width: 25px;
    text-align: center;
}

.system-config-tab strong,
.system-config-tab small {
    display: block;
}

.system-config-tab strong {
    font-size: 9.5px;
}

.system-config-tab small {
    margin-top: 1px;
    color: var(--hms-muted);
    font-size: 7.5px;
}

.system-config-shell {
    padding: 16px;
    border: 1px solid var(--hms-border);
    border-top: 0;
    border-radius: 0 0 11px 11px;
    background: #fff;
}

.system-config-panel-head {
    margin-bottom: 14px;
}

.system-config-panel-head h2 {
    margin: 0 0 3px;
    font-size: 15px;
}

.system-config-panel-head p {
    max-width: 680px;
    margin: 0;
    color: var(--hms-muted);
    font-size: 9px;
}

.form-row-full {
    margin-top: 11px;
}

.branding-colour-grid,
.branding-assets-grid,
.document-toggle-grid {
    display: grid;
    gap: 10px;
}

.branding-colour-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.branding-assets-grid,
.document-toggle-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-top: 12px;
}

.colour-field,
.branding-asset-card,
.toggle-option {
    padding: 11px;
    border: 1px solid var(--hms-border);
    border-radius: 9px;
    background: #fbfcfd;
}

.colour-field {
    display: block;
}

.colour-field > span {
    display: block;
    margin-bottom: 7px;
    font-size: 9px;
    font-weight: 650;
}

.colour-field > div {
    display: flex;
    align-items: center;
    gap: 8px;
}

.colour-field input[type="color"] {
    flex: 0 0 44px;
    width: 44px;
    height: 34px;
    padding: 2px;
}

.colour-field code {
    font-size: 8px;
}

.branding-asset-card {
    display: grid;
    grid-template-columns: 72px minmax(0, 1fr);
    gap: 10px;
    align-items: center;
}

.branding-preview {
    width: 72px;
    height: 72px;
    display: grid;
    place-items: center;
    overflow: hidden;
    border: 1px solid var(--hms-border);
    border-radius: 12px;
    background: #fff;
    color: var(--hms-primary);
    font-size: 22px;
}

.branding-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.favicon-preview {
    width: 56px;
    height: 56px;
}

.remove-option {
    margin-top: 7px !important;
    display: flex !important;
    align-items: center;
    gap: 6px;
    font-size: 8px;
}

.remove-option input {
    width: 14px !important;
    height: 14px !important;
    min-height: 0 !important;
}

.branding-live-preview {
    margin-top: 12px;
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.branding-live-preview span {
    padding: 7px 12px;
    border-radius: 8px;
    color: #fff;
    font-size: 8px;
    font-weight: 700;
}

.toggle-option {
    margin: 0 !important;
    display: flex !important;
    align-items: center;
    gap: 8px;
}

.toggle-option input {
    flex: 0 0 16px;
    width: 16px !important;
    height: 16px !important;
    min-height: 0 !important;
    accent-color: var(--hms-primary);
}

.toggle-option strong,
.toggle-option small {
    display: block;
}

.toggle-option strong {
    font-size: 9px;
}

.toggle-option small {
    margin-top: 2px;
    color: var(--hms-muted);
    font-size: 7.5px;
}

@media(max-width:760px) {
    .branding-colour-grid,
    .branding-assets-grid,
    .document-toggle-grid {
        grid-template-columns: 1fr;
    }

    .system-config-tab {
        flex-basis: 155px;
    }

    .system-config-shell {
        padding: 12px;
    }
}
</style>
@endpush
