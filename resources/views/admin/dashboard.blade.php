@extends('layouts.app')
@section('title', auth()->user()->isBranchManager() ? 'Branch Administration' : 'Administration')
@section('page_title', auth()->user()->isBranchManager() ? 'Branch Administration' : 'System Administration')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ auth()->user()->isBranchManager() ? $property->name.' Administration' : 'System Administration' }}</h1>
        <p>{{ auth()->user()->isBranchManager() ? 'Manage branch staff, property profile, rooms, rates and operational readiness.' : 'Configure users, property setup, imports, integrations and go-live readiness.' }}</p>
    </div>
</div>

<div class="stats compact-stats">
    <div class="stat stat-blue"><div class="stat-icon"><i class="fas fa-users"></i></div><div><span>Users</span><b>{{ $users }}</b><small>Assigned to branch</small></div></div>
    <div class="stat stat-teal"><div class="stat-icon"><i class="fas fa-bed"></i></div><div><span>Rooms</span><b>{{ $rooms }}</b><small>Branch inventory</small></div></div>
    <div class="stat stat-purple"><div class="stat-icon"><i class="fas fa-door-open"></i></div><div><span>Room Types</span><b>{{ $roomTypes }}</b><small>Configured categories</small></div></div>
    <div class="stat stat-gold"><div class="stat-icon"><i class="fas fa-percent"></i></div><div><span>Tax Rules</span><b>{{ $taxes }}</b><small>Protected configuration</small></div></div>
</div>

<div class="module-tabs module-tabs-links">
    <a class="module-tab active" href="{{ route('admin.setup') }}"><i class="fas fa-building"></i> Branch Profile</a>
    <a class="module-tab" href="{{ route('admin.users') }}"><i class="fas fa-users-gear"></i> Branch Users</a>
    <a class="module-tab" href="{{ route('admin.imports') }}"><i class="fas fa-file-import"></i> Rooms & Rates</a>
    @unless(auth()->user()->isBranchManager())
        <a class="module-tab" href="{{ route('admin.configuration') }}"><i class="fas fa-gears"></i> Configuration</a>
        <a class="module-tab" href="{{ route('admin.restore') }}"><i class="fas fa-clock-rotate-left"></i> Restore</a>
    @endunless
</div>

<div class="grid-2">
    <div class="panel">
        <div class="panel-title"><h2><i class="fas fa-clipboard-check"></i> Branch readiness</h2></div>
        @php
            $visibleSteps = auth()->user()->isBranchManager()
                ? ['property','users','rooms']
                : ['property','users','rooms','tax','payments','printers','notifications','backup','go_live'];
        @endphp
        @foreach($visibleSteps as $step)
            <div class="readiness-row">
                <span>{{ ucwords(str_replace('_',' ',$step)) }}</span>
                <b class="{{ ($steps[$step]->completed??false)?'text-success':'text-muted' }}">
                    <i class="fas {{ ($steps[$step]->completed??false)?'fa-circle-check':'fa-clock' }}"></i>
                    {{ ($steps[$step]->completed??false)?'Complete':'Pending' }}
                </b>
            </div>
        @endforeach
    </div>

    <div class="panel">
        <div class="panel-title"><h2><i class="fas fa-hotel"></i> {{ $property->name }}</h2></div>
        <div class="details">
            <span>Branch ID<b>{{ $property->code ?? ('#'.$property->id) }}</b></span>
            <span>Address<b>{{ $property->address ?: 'Not configured' }}</b></span>
            <span>Contact<b>{{ $property->phone ?: '-' }} . {{ $property->email ?: '-' }}</b></span>
            @if(auth()->user()->isBranchManager())<span>Manager<b>{{ auth()->user()->name }}</b></span>@endif
        </div>
    </div>
</div>
@endsection
