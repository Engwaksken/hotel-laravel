@extends('layouts.app')

@section('title', 'Bulk Data Tools')
@section('page_title', 'Bulk Data Tools')

@section('content')
<div class="page-head">
    <h1>CSV bulk upload & exports</h1>
    <p>
        Download templates, upload records and export hotel data.
    </p>
</div>

<div class="cards-3">
@foreach($modules as $key => $module)
    <div class="card">
        <h3>{{ $module['label'] }}</h3>

        <x-page-data-tools
            :module="$key"
            :can-import="true"
            :show-template="true"
        />
    </div>
@endforeach
</div>
@endsection
