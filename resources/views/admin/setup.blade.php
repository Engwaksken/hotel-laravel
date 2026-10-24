@extends('layouts.app')
@section('title', auth()->user()->isBranchManager() ? 'Branch Setup' : 'Setup Wizard')
@section('page_title', auth()->user()->isBranchManager() ? 'Hotel Branch Setup' : 'Hotel Go-live Setup')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ auth()->user()->isBranchManager() ? 'Branch profile' : 'Hotel setup' }}</h1>
        <p>
            {{ auth()->user()->isBranchManager()
                ? 'Manage the identity and operational setup of your assigned hotel branch.'
                : 'Configure the hotel profile, integrations and go-live readiness.' }}
        </p>
    </div>
    @if(auth()->user()->isBranchManager())
        <span class="badge info"><i class="fas fa-building"></i> {{ $property->name }}</span>
    @endif
</div>

<div class="card">
    <div class="card-head"><h2><i class="fas fa-hotel"></i> Property profile</h2></div>
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.setup.property') }}" class="form-grid">
        @csrf
        <label>Hotel / Branch name<input name="name" value="{{ $property->name }}" required></label>
        <label>Legal name<input name="legal_name" value="{{ $property->legal_name }}"></label>
        <label>TIN<input name="tin" value="{{ $property->tin }}"></label>
        <label>Telephone<input name="phone" value="{{ $property->phone }}"></label>
        <label>Email<input name="email" value="{{ $property->email }}" type="email"></label>
        <label>City<input name="city" value="{{ $property->city }}"></label>
        <label class="span-2">Address<textarea name="address">{{ $property->address }}</textarea></label>
        <label>Currency<input name="currency" value="{{ $property->currency }}" maxlength="3" required></label>
        <label>Timezone<input name="timezone" value="{{ $property->timezone }}" required></label>
        <label>Website<input name="website" type="url" value="{{ $property->website }}" placeholder="https://hotel.example.com"></label>
        <label>Primary colour<input name="primary_color" value="{{ $property->primary_color }}" placeholder="#08675d"></label>
        <label>Secondary colour<input name="secondary_color" value="{{ $property->secondary_color }}" placeholder="#d9e441"></label>
        <label>Hotel logo<input name="logo" type="file" accept="image/*"></label>
        <label>Favicon<input name="favicon" type="file" accept="image/*"></label>
        <div class="span-2 actions end"><button class="btn primary"><i class="fas fa-floppy-disk"></i> Save branch profile</button></div>
    </form>
</div>

<div class="card">
    <div class="card-head"><h2><i class="fas fa-list-check"></i> Setup areas</h2></div>
    <div class="module-tabs module-tabs-links">
        <a class="module-tab" href="{{ route('admin.users') }}"><i class="fas fa-users-gear"></i> Branch Users</a>
        <a class="module-tab" href="{{ route('admin.imports') }}"><i class="fas fa-file-import"></i> Rooms & Rates</a>
        @unless(auth()->user()->isBranchManager())
            <a class="module-tab" href="{{ route('admin.configuration') }}"><i class="fas fa-gears"></i> Taxes & Integrations</a>
            <a class="module-tab" href="{{ route('management.backups.index') }}"><i class="fas fa-database"></i> Backups</a>
        @endunless
    </div>

    @if(auth()->user()->isBranchManager())
        <div class="alert warning" style="margin-bottom:0">
            <i class="fas fa-shield-halved"></i>
            Payment credentials, tax controls, security, backups, restore and permission administration are protected system settings and remain with the Hotel Owner, General Manager or Super Admin.
        </div>
    @endif
</div>
@endsection
