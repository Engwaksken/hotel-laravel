@extends('layouts.app')
@section('title', 'Guest CRM')
@section('page_title', 'Guest CRM')
@section('content')
    <div class="card">
        <form method="GET" class="guest-search-form" role="search">
            <label class="guest-search-field" for="guest-search">Search guests</label>
            <input id="guest-search" name="q" value="{{ request('q') }}" placeholder="Name, phone or email">
            <button class="btn primary" type="submit">Search</button>
            @if(request('q'))<a class="btn" href="{{ route('crm.guests.index') }}">Clear</a>@endif
        </form>
    </div>
    <div class="card table-wrap"><table><thead><tr><th>Guest</th><th>Contact</th><th>Reservations</th><th>Loyalty</th><th>Complaints</th><th></th></tr></thead><tbody>
        @forelse($guests as $guest)<tr><td><b>{{ $guest->full_name }}</b><br><small>{{ $guest->nationality }}</small></td><td>{{ $guest->phone }}<br><small>{{ $guest->email }}</small></td><td>{{ $guest->reservations_count }}</td><td>{{ number_format($guest->loyaltyAccount?->points_balance ?? 0) }} pts<br><small>{{ $guest->loyaltyAccount?->tier?->name ?? 'Not enrolled' }}</small></td><td>{{ $guest->complaints_count }}</td><td><a class="btn small" href="{{ route('crm.guests.show', $guest) }}">Open profile</a></td></tr>
        @empty<tr><td colspan="6" class="empty">No guests found.</td></tr>@endforelse
    </tbody></table>{{ $guests->links() }}</div>
    <style>
        .guest-search-form{display:flex;align-items:end;gap:.75rem;flex-wrap:nowrap}
        .guest-search-field{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
        .guest-search-form input{flex:1 1 auto;min-width:0}
        @media(max-width:480px){.guest-search-form{gap:.5rem}.guest-search-form .btn{flex:0 0 auto}}
    </style>
@endsection
