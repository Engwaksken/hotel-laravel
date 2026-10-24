@props([
    'module',
    'addModal' => null,
    'addLabel' => 'Add Record',
    'showAdd' => true,
])

<div class="hms-page-tools">
    @if($showAdd && $addModal)
        <button
            type="button"
            class="btn primary"
            data-modal-open="{{ $addModal }}"
        >
            <i class="fas fa-plus"></i>
            <span>{{ $addLabel }}</span>
        </button>
    @endif

    <x-data-export-buttons :module="$module" />
</div>
