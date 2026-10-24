@props([
    'module',
    'label' => null,
])

@php
    $query = request()->only([
        'search',
        'status',
        'from',
        'to',
        'property_id',
        'branch_id',
    ]);
@endphp

<div class="hms-export-actions">
    @if(\Illuminate\Support\Facades\Route::has('admin.exports.csv'))
        <a
            href="{{ route('admin.exports.csv', array_merge(['module' => $module], $query)) }}"
            class="btn btn-secondary btn-sm"
            title="Export CSV"
        >
            <i class="fas fa-file-csv"></i>
            <span>CSV</span>
        </a>
    @endif

    @if(\Illuminate\Support\Facades\Route::has('admin.exports.pdf'))
        <a
            href="{{ route('admin.exports.pdf', array_merge(['module' => $module], $query)) }}"
            class="btn btn-secondary btn-sm"
            title="Export PDF"
        >
            <i class="fas fa-file-pdf"></i>
            <span>PDF</span>
        </a>
    @endif
</div>
