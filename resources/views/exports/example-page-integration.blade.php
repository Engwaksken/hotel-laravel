{{-- Example index-page integration.

1. Add/export toolbar:

<div class="actions">
    <button
        type="button"
        class="btn btn-primary"
        data-modal-open="addRoomModal"
    >
        <i class="fas fa-plus"></i>
        Add Room
    </button>

    <x-data-export-buttons module="rooms" />
</div>


2. Add modal:

@push('modals')
<x-hms-modal
    id="addRoomModal"
    title="Add Room"
>
    <form method="POST" action="{{ route('rooms.store') }}">
        @csrf

        <div class="form-grid">
            <label>
                Room Number
                <input
                    type="text"
                    name="room_number"
                    required
                >
            </label>

            ...
        </div>

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
                class="btn btn-primary"
            >
                Save Room
            </button>
        </div>
    </form>
</x-hms-modal>
@endpush


3. Edit trigger:

<button
    type="button"
    class="btn btn-sm"
    data-modal-open="editRoomModal"
    data-edit-action="{{ route('rooms.update', $room) }}"
    data-edit-record='@json($room)'
>
    Edit
</button>


4. Edit modal:

@push('modals')
<x-hms-modal
    id="editRoomModal"
    title="Edit Room"
>
    <form
        method="POST"
        action=""
        data-edit-form
    >
        @csrf
        @method('PUT')

        <label>
            Room Number
            <input
                name="room_number"
                data-edit-field="room_number"
                required
            >
        </label>

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
                class="btn btn-primary"
            >
                Update
            </button>
        </div>
    </form>
</x-hms-modal>
@endpush


5. Delete trigger + modal:

<button
    type="button"
    class="btn btn-danger btn-sm"
    data-modal-open="deleteRoom{{ $room->id }}"
>
    Delete
</button>

@push('modals')
    <x-delete-modal
        id="deleteRoom{{ $room->id }}"
        :action="route('rooms.destroy', $room)"
        title="Delete Room"
        message="Delete room {{ $room->room_number }}?"
    />
@endpush
--}}
