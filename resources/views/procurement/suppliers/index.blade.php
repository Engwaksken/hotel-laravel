@extends('layouts.app')
@section('page_title', 'Suppliers')
@section('content')
    <div class="page-head"><div><h1>Suppliers</h1><p>Manage supplier contacts and purchasing terms.</p></div>
        @if(auth()->user()?->canManageEntries())<button class="btn primary" type="button" data-modal="newSupplier">Add supplier</button>@endif
    </div>
    <div class="card table-wrap"><table><thead><tr><th>Name</th><th>Contact</th><th>Phone</th><th>Credit</th><th>Actions</th></tr></thead><tbody>
        @forelse($suppliers as $supplier)<tr><td>{{ $supplier->name }}</td><td>{{ $supplier->contact_person }}</td><td>{{ $supplier->phone }}</td><td>{{ $supplier->credit_days }} days</td><td><x-entry-actions resource="suppliers" :id="$supplier->id" label="supplier" /></td></tr>
        @empty<tr><td colspan="5" class="empty">No suppliers found.</td></tr>@endforelse
    </tbody></table>{{ $suppliers->links() }}</div>
    @if(auth()->user()?->canManageEntries())
        <div class="modal" id="newSupplier"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Add supplier</h2>
            <form method="POST" action="{{ route('procurement.suppliers.store') }}">@csrf<div class="form-grid">
                <label>Name<input name="name" required></label><label>Contact person<input name="contact_person"></label>
                <label>Phone<input name="phone"></label><label>Email<input name="email" type="email"></label>
                <label>TIN<input name="tin"></label><label>Credit days<input name="credit_days" type="number" min="0" value="0"></label>
            </div><label>Address<textarea name="address"></textarea></label><button class="btn primary">Save supplier</button></form>
        </div></div>
    @endif
@endsection
