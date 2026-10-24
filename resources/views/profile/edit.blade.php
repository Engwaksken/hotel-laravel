@extends('layouts.app')

@section('title', 'My Profile')
@section('page_title', 'My Profile')

@section('content')
<div class="profile-page">
    <div class="page-head">
        <div>
            <span class="eyebrow">
                <i class="fas fa-user"></i>
                My Account
            </span>

            <h1>User profile</h1>

            <p>
                Update your personal information and account password.
                Role, permissions and branch assignment are managed by an administrator.
            </p>
        </div>
    </div>

    <div class="profile-grid">
        <aside class="card profile-summary">
            <div class="profile-avatar">
                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
            </div>

            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>

            <div class="profile-tags">
                <span class="badge info">
                    {{ ucwords(str_replace('_', ' ', $user->role ?? 'staff')) }}
                </span>

                @if($user->property)
                    <span class="badge">
                        {{ $user->property->name }}
                    </span>
                @endif
            </div>
        </aside>

        <section class="card">
            <form
                method="POST"
                action="{{ route('profile.update') }}"
            >
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div>
                        <label>Full name</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                        >
                    </div>

                    <div>
                        <label>Email address</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                        >
                    </div>

                    @if(\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone'))
                        <div>
                            <label>Phone number</label>
                            <input
                                type="text"
                                name="phone"
                                value="{{ old('phone', $user->phone) }}"
                            >
                        </div>
                    @endif

                    @if(\Illuminate\Support\Facades\Schema::hasColumn('users', 'job_title'))
                        <div>
                            <label>Job title</label>
                            <input
                                type="text"
                                name="job_title"
                                value="{{ old('job_title', $user->job_title) }}"
                            >
                        </div>
                    @endif
                </div>

                @if(\Illuminate\Support\Facades\Schema::hasColumn('users', 'address'))
                    <div style="margin-top:12px">
                        <label>Address</label>
                        <textarea
                            name="address"
                            rows="3"
                        >{{ old('address', $user->address) }}</textarea>
                    </div>
                @endif

                <hr>

                <h3>Change password</h3>

                <div class="form-grid">
                    <div>
                        <label>Current password</label>
                        <input
                            type="password"
                            name="current_password"
                            autocomplete="current-password"
                        >
                    </div>

                    <div></div>

                    <div>
                        <label>New password</label>
                        <input
                            type="password"
                            name="password"
                            autocomplete="new-password"
                        >
                    </div>

                    <div>
                        <label>Confirm new password</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="fas fa-floppy-disk"></i>
                        Save profile
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
.profile-page .page-head {
    margin-bottom: 14px;
}

.profile-page .page-head h1 {
    margin: 4px 0 3px;
    font-size: 21px;
}

.profile-page .page-head p {
    max-width: 700px;
    margin: 0;
    color: var(--hms-muted);
}

.profile-page .eyebrow {
    color: var(--hms-primary);
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
}

.profile-grid {
    display: grid;
    grid-template-columns: 240px minmax(0, 1fr);
    gap: 12px;
    align-items: start;
}

.profile-summary {
    text-align: center;
}

.profile-avatar {
    width: 74px;
    height: 74px;
    margin: 0 auto 10px;
    display: grid;
    place-items: center;
    border-radius: 22px;
    background: var(--hms-primary-soft);
    color: var(--hms-primary);
    font-size: 28px;
    font-weight: 800;
}

.profile-summary h2 {
    margin: 0;
    font-size: 16px;
}

.profile-summary p {
    margin: 4px 0 10px;
    color: var(--hms-muted);
}

.profile-tags {
    display: flex;
    justify-content: center;
    gap: 5px;
    flex-wrap: wrap;
}

@media(max-width:760px) {
    .profile-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
