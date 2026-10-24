@extends('layouts.app')
@section('title','Hotel Operations') @section('page_title','Hotel Operations')
@section('content')
<div class="page-head"><div><h1>Hotel Operations</h1><p>Housekeeping, engineering, laundry, mini-bar and guest service control centre.</p></div></div>
<div class="stats compact-stats">
<div class="stat stat-orange"><div class="stat-icon"><i class="fas fa-broom"></i></div><div><span>Housekeeping Open</span><b>{{ $hkPending }}</b><small>Pending through clean</small></div></div>
<div class="stat stat-red"><div class="stat-icon"><i class="fas fa-screwdriver-wrench"></i></div><div><span>Maintenance Open</span><b>{{ $maintenanceOpen }}</b><small>Engineering issues</small></div></div>
<div class="stat stat-blue"><div class="stat-icon"><i class="fas fa-shirt"></i></div><div><span>Laundry Active</span><b>{{ $laundryActive }}</b><small>Not yet delivered</small></div></div>
<div class="stat stat-green"><div class="stat-icon"><i class="fas fa-bell-concierge"></i></div><div><span>Guest Requests</span><b>{{ $guestRequests }}</b><small>Awaiting completion</small></div></div>
</div>
<div class="module-tabs module-tabs-links">
<a class="module-tab active" href="{{ route('operations.housekeeping.index') }}"><i class="fas fa-broom"></i> Housekeeping</a>
<a class="module-tab" href="{{ route('operations.maintenance.index') }}"><i class="fas fa-screwdriver-wrench"></i> Maintenance</a>
<a class="module-tab" href="{{ route('operations.laundry.index') }}"><i class="fas fa-shirt"></i> Laundry</a>
<a class="module-tab" href="{{ route('operations.minibar.index') }}"><i class="fas fa-wine-bottle"></i> Mini-bar</a>
<a class="module-tab" href="{{ route('operations.guest-services.index') }}"><i class="fas fa-hand-holding-heart"></i> Guest Services</a>
<a class="module-tab" href="{{ route('operations.lost-found.index') }}"><i class="fas fa-magnifying-glass"></i> Lost & Found</a>
</div>
<div class="panel"><div class="panel-title"><div><h2><i class="fas fa-chart-line"></i> Operations summary</h2><p>Use the tabs above to open each operational workspace.</p></div></div><div class="summary-strip"><div><span>Mini-bar revenue today</span><b>{{ config('hms.currency','UGX') }} {{ number_format($minibarToday,0) }}</b></div><div><span>Lost & found stored</span><b>{{ $lostFound }}</b></div></div></div>
@endsection
