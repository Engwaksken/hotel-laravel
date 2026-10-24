@extends('layouts.app')

@section('title', 'Guest Experience & Integrations')
@section('page_title', 'Guest Experience & Integrations')

@section('content')
<div class="page-head">
    <div>
        <h1>Guest Experience &amp; Integrations</h1>
        <p>
            Configure accessibility, chatbot, guest comments and URA EFRIS.
        </p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="guest-settings-tabs">
    <button class="active" type="button" data-gf-tab="accessibility">
        <i class="fas fa-universal-access"></i>
        Accessibility
    </button>

    <button type="button" data-gf-tab="chatbot">
        <i class="fas fa-comments"></i>
        Chatbot
    </button>

    <button type="button" data-gf-tab="comments">
        <i class="fas fa-star"></i>
        Guest Comments
    </button>

    <button type="button" data-gf-tab="efris">
        <i class="fas fa-file-invoice"></i>
        URA EFRIS
    </button>
</div>

<section class="guest-settings-panel active" data-gf-panel="accessibility">
    <div class="card">
        <h2>Accessibility widget</h2>
        <p class="text-muted">
            Controls the floating accessibility button shown on public guest pages.
        </p>

        <form
            method="POST"
            action="{{ route('admin.guest-features.accessibility.update') }}"
        >
            @csrf
            @method('PUT')

            <label class="feature-check">
                <input
                    type="checkbox"
                    name="enabled"
                    value="1"
                    {{ $settings['accessibility_enabled'] ? 'checked' : '' }}
                >
                Enable accessibility widget
            </label>

            <label class="feature-check">
                <input
                    type="checkbox"
                    name="read_aloud"
                    value="1"
                    {{ $settings['accessibility_read_aloud'] ? 'checked' : '' }}
                >
                Enable browser read-aloud controls
            </label>

            <div class="form-group">
                <label>Default text size (%)</label>
                <input
                    type="number"
                    name="default_font_scale"
                    min="90"
                    max="130"
                    value="{{ $settings['accessibility_font_scale'] }}"
                >
            </div>

            <button class="btn btn-primary" type="submit">
                Save Accessibility
            </button>
        </form>
    </div>
</section>

<section class="guest-settings-panel" data-gf-panel="chatbot">
    <div class="card">
        <h2>Hotel chatbot</h2>

        <form
            method="POST"
            action="{{ route('admin.guest-features.chatbot.update') }}"
        >
            @csrf
            @method('PUT')

            <label class="feature-check">
                <input
                    type="checkbox"
                    name="enabled"
                    value="1"
                    {{ $settings['chatbot_enabled'] ? 'checked' : '' }}
                >
                Enable chatbot on public guest pages
            </label>

            <div class="form-grid">
                <div>
                    <label>Chatbot title</label>
                    <input
                        type="text"
                        name="title"
                        value="{{ $settings['chatbot_title'] }}"
                    >
                </div>
            </div>

            <div class="form-group">
                <label>Welcome message</label>
                <textarea
                    name="welcome"
                    rows="3"
                >{{ $settings['chatbot_welcome'] }}</textarea>
            </div>

            <div class="form-group">
                <label>Fallback response</label>
                <textarea
                    name="fallback"
                    rows="3"
                >{{ $settings['chatbot_fallback'] }}</textarea>
            </div>

            <button class="btn btn-primary" type="submit">
                Save Chatbot
            </button>
        </form>
    </div>

    <div class="card">
        <h2>Chatbot FAQs</h2>

        <form
            method="POST"
            action="{{ route('admin.guest-features.faqs.store') }}"
        >
            @csrf

            <div class="form-group">
                <label>Question</label>
                <input type="text" name="question" required>
            </div>

            <div class="form-group">
                <label>Answer</label>
                <textarea name="answer" rows="4" required></textarea>
            </div>

            <div class="form-grid">
                <div>
                    <label>Keywords</label>
                    <input
                        type="text"
                        name="keywords"
                        placeholder="room, booking, reservation"
                    >
                </div>

                <div>
                    <label>Display order</label>
                    <input
                        type="number"
                        name="display_order"
                        value="0"
                        min="0"
                    >
                </div>
            </div>

            <label class="feature-check">
                <input type="checkbox" name="is_active" value="1" checked>
                Active
            </label>

            <button class="btn btn-primary" type="submit">
                Add FAQ
            </button>
        </form>

        <div class="feature-list">
            @foreach($faqs as $faq)
                <article>
                    <div>
                        <strong>{{ $faq->question }}</strong>
                        <small>{{ $faq->answer }}</small>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.guest-features.faqs.destroy', $faq->id) }}"
                    >
                        @csrf
                        @method('DELETE')

                        <button class="btn btn-sm btn-danger" type="submit">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="guest-settings-panel" data-gf-panel="comments">
    <div class="card">
        <h2>Guest comments</h2>

        <form
            method="POST"
            action="{{ route('admin.guest-features.comments.update') }}"
        >
            @csrf
            @method('PUT')

            <label class="feature-check">
                <input
                    type="checkbox"
                    name="enabled"
                    value="1"
                    {{ $settings['comments_enabled'] ? 'checked' : '' }}
                >
                Allow guests to submit comments
            </label>

            <label class="feature-check">
                <input
                    type="checkbox"
                    name="show_homepage"
                    value="1"
                    {{ $settings['comments_show_homepage'] ? 'checked' : '' }}
                >
                Display approved comments on homepage
            </label>

            <button class="btn btn-primary" type="submit">
                Save Comment Settings
            </button>
        </form>
    </div>

    <div class="card">
        <h2>Moderation</h2>

        <div class="feature-list comments-list">
            @forelse($comments as $comment)
                <article>
                    <div>
                        <strong>
                            {{ $comment->guest_name }}
                            · {{ $comment->rating }}/5
                        </strong>

                        <small>{{ $comment->comment }}</small>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.guest-features.comments.moderate', $comment->id) }}"
                    >
                        @csrf
                        @method('PUT')

                        <select name="status">
                            @foreach(['pending', 'approved', 'rejected'] as $status)
                                <option
                                    value="{{ $status }}"
                                    {{ $comment->status === $status ? 'selected' : '' }}
                                >
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>

                        <label class="feature-check compact">
                            <input
                                type="checkbox"
                                name="is_featured"
                                value="1"
                                {{ $comment->is_featured ? 'checked' : '' }}
                            >
                            Featured
                        </label>

                        <button class="btn btn-sm btn-primary" type="submit">
                            Save
                        </button>
                    </form>
                </article>
            @empty
                <p class="text-muted">No guest comments yet.</p>
            @endforelse
        </div>
    </div>
</section>

<section class="guest-settings-panel" data-gf-panel="efris">
    <div class="card">
        <h2>URA EFRIS integration</h2>

        <div class="integration-note">
            <i class="fas fa-circle-info"></i>

            <div>
                <strong>System-to-system configuration</strong>
                <p>
                    Enter the connection details issued or approved for your
                    URA EFRIS integration. API credentials are stored encrypted.
                    Do not use guessed production endpoints or credentials.
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.guest-features.efris.update') }}"
        >
            @csrf
            @method('PUT')

            <label class="feature-check">
                <input
                    type="checkbox"
                    name="enabled"
                    value="1"
                    {{ $settings['efris_enabled'] ? 'checked' : '' }}
                >
                Enable EFRIS integration
            </label>

            <div class="form-grid">
                <div>
                    <label>Environment</label>
                    <select name="environment" required>
                        <option
                            value="sandbox"
                            {{ $settings['efris_environment'] === 'sandbox' ? 'selected' : '' }}
                        >
                            Sandbox / Test
                        </option>

                        <option
                            value="production"
                            {{ $settings['efris_environment'] === 'production' ? 'selected' : '' }}
                        >
                            Production
                        </option>
                    </select>
                </div>

                <div>
                    <label>URA TIN</label>
                    <input
                        type="text"
                        name="tin"
                        value="{{ $settings['efris_tin'] }}"
                    >
                </div>

                <div>
                    <label>Registered business name</label>
                    <input
                        type="text"
                        name="business_name"
                        value="{{ $settings['efris_business_name'] }}"
                    >
                </div>

                <div>
                    <label>Branch / place-of-business code</label>
                    <input
                        type="text"
                        name="branch_code"
                        value="{{ $settings['efris_branch_code'] }}"
                    >
                </div>

                <div>
                    <label>EFRIS device number</label>
                    <input
                        type="text"
                        name="device_number"
                        value="{{ $settings['efris_device_number'] }}"
                    >
                </div>

                <div>
                    <label>API base URL</label>
                    <input
                        type="url"
                        name="api_base_url"
                        value="{{ $settings['efris_api_base_url'] }}"
                        placeholder="Use the endpoint supplied for your EFRIS integration"
                    >
                </div>

                <div>
                    <label>Client ID / API identifier</label>
                    <input
                        type="password"
                        name="client_id"
                        autocomplete="new-password"
                        placeholder="{{ $settings['efris_has_client_id'] ? 'Stored securely — enter only to replace' : 'Enter issued client ID' }}"
                    >
                </div>

                <div>
                    <label>Client secret / API secret</label>
                    <input
                        type="password"
                        name="client_secret"
                        autocomplete="new-password"
                        placeholder="{{ $settings['efris_has_client_secret'] ? 'Stored securely — enter only to replace' : 'Enter issued secret' }}"
                    >
                </div>

                <div>
                    <label>Certificate / key reference</label>
                    <input
                        type="text"
                        name="certificate_reference"
                        value="{{ $settings['efris_certificate_reference'] }}"
                        placeholder="Server-side certificate/key path or reference"
                    >
                </div>
            </div>

            <button class="btn btn-primary" type="submit">
                <i class="fas fa-shield-halved"></i>
                Save EFRIS Configuration
            </button>
        </form>
    </div>
</section>
@endsection

@push('styles')
<style>
.guest-settings-tabs{display:flex;gap:4px;overflow-x:auto;margin-bottom:12px;border-bottom:1px solid var(--hms-border,#dde3e6)}
.guest-settings-tabs button{padding:10px 13px;border:0;border-bottom:2px solid transparent;background:transparent;color:#68747d;white-space:nowrap;font-weight:700;cursor:pointer}
.guest-settings-tabs button.active{border-bottom-color:var(--hms-primary,#0f766e);color:var(--hms-primary,#0f766e)}
.guest-settings-panel{display:none}.guest-settings-panel.active{display:block}
.feature-check{margin:10px 0!important;display:flex!important;align-items:center;gap:7px}
.feature-check input{width:15px!important;height:15px!important;min-height:0!important}
.feature-check.compact{margin:0!important}
.feature-list{margin-top:12px;display:grid;gap:8px}
.feature-list article{padding:11px;display:flex;align-items:center;justify-content:space-between;gap:12px;border:1px solid var(--hms-border,#dde3e6);border-radius:10px}
.feature-list strong,.feature-list small{display:block}
.feature-list small{margin-top:4px;color:#68747d}
.comments-list article>form{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.integration-note{margin:10px 0 16px;padding:12px;display:flex;gap:10px;border:1px solid #cfe3de;border-radius:10px;background:#eff8f6;color:#315b53}
.integration-note p{margin:4px 0 0}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('[data-gf-tab]');
    const panels = document.querySelectorAll('[data-gf-panel]');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(item => item.classList.remove('active'));
            panels.forEach(item => item.classList.remove('active'));

            tab.classList.add('active');

            document.querySelector(
                '[data-gf-panel="' + tab.dataset.gfTab + '"]'
            )?.classList.add('active');
        });
    });
});
</script>
@endpush
