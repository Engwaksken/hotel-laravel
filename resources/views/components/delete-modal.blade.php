@props([
    'id',
    'action',
    'title' => 'Delete Record',
    'message' => 'Are you sure you want to delete this record? This action cannot be undone.',
    'method' => 'DELETE',
])

<x-hms-modal
    :id="$id"
    :title="$title"
    size="sm"
>
    <div class="hms-delete-warning">
        <span class="hms-delete-icon">
            <i class="fas fa-triangle-exclamation"></i>
        </span>

        <div>
            <strong>Confirm deletion</strong>
            <p>{{ $message }}</p>
        </div>
    </div>

    <form
        method="POST"
        action="{{ $action }}"
    >
        @csrf

        @if(strtoupper($method) !== 'POST')
            @method($method)
        @endif

        <div class="hms-modal-actions">
            <button
                type="button"
                class="btn btn-secondary"
                data-modal-close
            >
                Cancel
            </button>

            <button
                type="submit"
                class="btn btn-danger"
            >
                <i class="fas fa-trash"></i>
                Delete
            </button>
        </div>
    </form>
</x-hms-modal>
