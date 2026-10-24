@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card p-4 text-center mx-auto" style="max-width:520px">
        <h1 class="h4">{{ $record->full_name ?? $record->name ?? 'Guest' }}</h1>
        <p class="text-muted">Scan this code to verify the guest record.</p>
        <img class="img-fluid mx-auto" style="max-width:320px" src="{{ route('guests.qr.svg', $record->id) }}" alt="Guest QR Code">
        <div class="mt-3">
            <button class="btn btn-outline-primary" onclick="window.print()">Print QR</button>
        </div>
    </div>
</div>
@endsection
