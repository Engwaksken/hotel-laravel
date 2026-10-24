@extends('layouts.app')
@section('title', 'Promotions')
@section('page_title', 'Promotions & Promo Codes')
@section('content')
    <div class="page-head"><div><h1>Promotions &amp; Promo Codes</h1><p>Manage offers and track redemption activity.</p></div>
        @if(auth()->user()?->canManageEntries())<button class="btn primary" type="button" data-modal="newPromotion">Create promotion</button>@endif
    </div>
    <div class="card table-wrap"><table><thead><tr><th>Name / Code</th><th>Offer</th><th>Period</th><th>Usage</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @forelse($promotions as $promotion)<tr><td><b>{{ $promotion->name }}</b><br><small>{{ $promotion->code }}</small></td><td>{{ $promotion->type === 'percentage' ? $promotion->value.'%' : 'UGX '.number_format($promotion->value) }}</td><td>{{ $promotion->starts_at->format('d M') }} – {{ $promotion->ends_at->format('d M Y') }}</td><td>{{ $promotion->used_count }} / {{ $promotion->usage_limit ?? '∞' }}</td><td><form method="POST" action="{{ route('crm.promotions.toggle', $promotion) }}">@csrf<button class="btn small {{ $promotion->is_active ? 'secondary' : '' }}">{{ $promotion->is_active ? 'Active' : 'Inactive' }}</button></form></td><td><x-entry-actions resource="promotions" :id="$promotion->id" label="promotion" /></td></tr>
        @empty<tr><td colspan="6" class="empty">No promotions found.</td></tr>@endforelse
    </tbody></table>{{ $promotions->links() }}</div>
    @if(auth()->user()?->canManageEntries())<div class="modal" id="newPromotion"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Create promotion</h2>
        <form method="POST" action="{{ route('crm.promotions.store') }}" class="form-grid">@csrf
            <label>Name<input name="name" placeholder="Promotion name" required></label><label>Promo code<input name="code"></label>
            <label>Type<select name="type"><option value="percentage">Percentage</option><option value="fixed">Fixed UGX</option></select></label>
            <label>Value<input type="number" step="0.01" min="0" name="value" required></label>
            <label>Minimum spend<input type="number" step="0.01" min="0" name="minimum_spend"></label><label>Maximum discount<input type="number" step="0.01" min="0" name="maximum_discount"></label>
            <label>Starts<input type="datetime-local" name="starts_at" required></label><label>Ends<input type="datetime-local" name="ends_at" required></label>
            <label>Total use limit<input type="number" min="1" name="usage_limit"></label><label>Per guest limit<input type="number" min="1" name="per_guest_limit"></label>
            <div class="actions"><button class="btn primary">Create promotion</button></div>
        </form>
    </div></div>@endif
@endsection
