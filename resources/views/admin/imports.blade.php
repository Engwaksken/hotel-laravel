@extends('layouts.app')
@section('title', 'Bulk Imports')
@section('page_title', 'Rooms & Rates Bulk Import')
@section('content')
    <div class="page-head"><div><h1>Rooms &amp; Rates Bulk Import</h1><p>Import room inventory or room rates from a prepared CSV file.</p></div><div class="actions"><button class="btn" type="button" data-modal="importRooms">Import rooms</button><button class="btn primary" type="button" data-modal="importRates">Import room rates</button></div></div>
    <div class="card table-wrap"><h2>Import history</h2><table><thead><tr><th>Date</th><th>Type</th><th>File</th><th>Imported</th><th>Failed</th></tr></thead><tbody>
        @forelse($runs as $run)<tr><td>{{ $run->created_at }}</td><td>{{ $run->type }}</td><td>{{ $run->original_name }}</td><td>{{ $run->rows_imported }}</td><td>{{ $run->rows_failed }}</td></tr>
        @empty<tr><td colspan="5" class="empty">No imports have been run.</td></tr>@endforelse
    </tbody></table></div>
    <div class="modal {{ old('_import_modal') === 'rooms' ? 'open' : '' }}" id="importRooms"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Import rooms CSV</h2>
        <p>Download the template, fill it in, then upload a CSV file.</p><a class="btn" href="{{ route('admin.imports.rooms-template') }}">Download rooms template</a>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.imports.rooms') }}" class="stack">@csrf<input type="hidden" name="_import_modal" value="rooms"><label>CSV file<input type="file" name="file" accept=".csv,text/csv" required></label><button class="btn primary">Import rooms</button></form>
    </div></div>
    <div class="modal {{ old('_import_modal') === 'rates' ? 'open' : '' }}" id="importRates"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Import room rates CSV</h2>
        <p>Download the template, fill it in, then upload a CSV file.</p><a class="btn" href="{{ route('admin.imports.rates-template') }}">Download rates template</a>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.imports.rates') }}" class="stack">@csrf<input type="hidden" name="_import_modal" value="rates"><label>CSV file<input type="file" name="file" accept=".csv,text/csv" required></label><button class="btn primary">Import room rates</button></form>
    </div></div>
@endsection
