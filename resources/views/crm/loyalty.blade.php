@extends('layouts.app')
@section('title', 'Loyalty')
@section('page_title', 'Loyalty Programme')
@section('content')
    <div class="page-head"><div><h1>Loyalty programme</h1><p>Manage membership tiers and review guest point balances.</p></div><button class="btn primary" type="button" data-modal="addLoyaltyTier">Add tier</button></div>
    <div class="cards-3">
        @forelse($tiers as $tier)<div class="card"><h2>{{ $tier->name }}</h2><p>{{ number_format($tier->minimum_points) }} points to qualify</p><p>{{ $tier->discount_percent }}% discount · ×{{ $tier->points_multiplier }} points multiplier</p>@if($tier->benefits)<small>{{ $tier->benefits }}</small>@endif</div>
        @empty<div class="card empty">No membership tiers configured.</div>@endforelse
    </div>
    <div class="card table-wrap"><h2>Members</h2><table><thead><tr><th>Member</th><th>Guest</th><th>Tier</th><th>Balance</th><th>Lifetime</th></tr></thead><tbody>
        @forelse($accounts as $account)<tr><td>{{ $account->member_number }}</td><td>{{ $account->guest?->full_name ?? 'Guest unavailable' }}</td><td>{{ $account->tier?->name ?? '—' }}</td><td>{{ number_format($account->points_balance) }}</td><td>{{ number_format($account->lifetime_points) }}</td></tr>
        @empty<tr><td colspan="5" class="empty">No loyalty members found.</td></tr>@endforelse
    </tbody></table>{{ $accounts->links() }}</div>
    <div class="modal {{ $errors->any() ? 'open' : '' }}" id="addLoyaltyTier"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Add membership tier</h2>
        <form method="POST" action="{{ route('crm.loyalty.tiers.store') }}" class="form-grid">@csrf
            <label>Tier name<input name="name" placeholder="Tier name" required></label><label>Minimum points<input type="number" min="0" name="minimum_points" value="0" required></label>
            <label>Discount percent<input type="number" min="0" max="100" step="0.01" name="discount_percent" value="0" required></label><label>Points multiplier<input type="number" min="0" step="0.01" name="points_multiplier" value="1" required></label>
            <label class="span-2">Benefits<input name="benefits" placeholder="Benefits, comma separated"></label><div class="actions"><button class="btn primary">Create tier</button></div>
        </form>
    </div></div>
@endsection
