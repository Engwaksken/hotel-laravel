@extends('layouts.app')
@section('title', 'Edit '.$config['title'])
@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-pen-to-square"></i> Edit {{ $config['title'] }}</h1>
        <p>Update this record. Access is restricted by role and hotel branch.</p>
    </div>
    <a class="btn" href="{{ url()->previous() }}"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<div class="card">
    <form method="POST" action="{{ route('managed-entries.update',[$resource,$record->getKey()]) }}" class="form-grid">
        @csrf @method('PUT')
        <input type="hidden" name="_return" value="{{ url()->previous() }}">
        @foreach($config['fields'] as $field)
            @php
                $value = old($field, $record->{$field} ?? '');
                $label = ucwords(str_replace('_',' ',$field));
                $isLong = in_array($field,['description','address','notes','requirements'],true);
                $isBool = $field === 'is_active';
                $isDate = str_contains($field,'date') || in_array($field,['starts_at','ends_at','hire_date'],true);
                $isNumber = preg_match('/(rate|amount|limit|salary|spend|discount|guests|days|occupancy|adults|children|value)/',$field);
            @endphp
            @if($isBool)
                <label class="check span-2"><input type="hidden" name="{{ $field }}" value="0"><input type="checkbox" name="{{ $field }}" value="1" @checked((bool)$value)> {{ $label }}</label>
            @elseif($isLong)
                <label class="span-2">{{ $label }}<textarea name="{{ $field }}">{{ $value }}</textarea></label>
            @else
                <label>{{ $label }}<input name="{{ $field }}" value="{{ $value instanceof \DateTimeInterface ? $value->format('Y-m-d\TH:i') : $value }}" @if($isDate) type="datetime-local" @elseif($isNumber) type="number" step="0.01" @endif></label>
            @endif
        @endforeach
        <div class="actions span-2">
            <button class="btn primary"><i class="fas fa-floppy-disk"></i> Save Changes</button>
        </div>
    </form>
</div>
@endsection
