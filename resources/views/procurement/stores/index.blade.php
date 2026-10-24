@extends('layouts.app')
@section('page_title', 'Stores & Stock')
@section('content')
    <div class="page-head">
        <div><h1>Stores &amp; Stock</h1><p>Branch stores and their current inventory levels.</p></div>
        <button class="btn primary" type="button" data-modal="newStore">Add store</button>
    </div>
    <div class="tabs">
        @foreach($stores as $store)<a class="{{ $selected?->id === $store->id ? 'active' : '' }}" href="?store_id={{ $store->id }}">{{ $store->name }} ({{ $store->stocks_count }})</a>@endforeach
    </div>
    <div class="card">
        <h2>{{ $selected?->name ?? 'No store selected' }}</h2>
        @if($selected)
            <div class="table-wrap"><table><thead><tr><th>SKU</th><th>Item</th><th>Unit</th><th>Quantity</th><th>Reorder</th></tr></thead><tbody>
                @forelse($stocks as $stock)<tr><td>{{ $stock->item->sku }}</td><td>{{ $stock->item->name }}</td><td>{{ $stock->item->unit }}</td><td>{{ number_format($stock->quantity, 4) }}</td><td>{{ number_format($stock->item->reorder_level, 4) }}</td></tr>
                @empty<tr><td colspan="5" class="empty">No stock recorded in this store.</td></tr>@endforelse
            </tbody></table></div>{{ $stocks->links() }}
        @endif
    </div>
    <div class="modal" id="newStore"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Add store</h2>
        <form method="POST" action="{{ route('procurement.stores.store') }}" class="form-grid">@csrf
            <label>Store name<input name="name" required></label><label>Code<input name="code" required></label>
            <label>Type<select name="type">@foreach(['main','kitchen','bar','housekeeping','maintenance','other'] as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach</select></label>
            <label>Location<input name="location"></label><div class="actions"><button class="btn primary">Add store</button></div>
        </form>
    </div></div>
@endsection
