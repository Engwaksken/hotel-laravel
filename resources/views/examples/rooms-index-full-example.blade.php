@extends('layouts.app')

@section('title', 'Rooms')
@section('page_title', 'Rooms')

@section('content')
<div class="page-head">
    <div>
        <h1>Rooms</h1>
        <p>
            Manage room inventory, operational state and
            housekeeping readiness.
        </p>
    </div>

    <x-page-crud-toolbar
        module="rooms"
        add-modal="room-modal"
        add-label="Add Room"
    />
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Building / Floor</th>
                    <th>Room Status</th>
                    <th>Housekeeping</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rooms as $room)
                    <tr>
                        <td>
                            <b>{{ $room->room_number }}</b>
                            <small>{{ $room->name }}</small>
                        </td>

                        <td>{{ $room->roomType->name }}</td>

                        <td>
                            {{ $room->building ?: '—' }}
                            /
                            {{ $room->floor ?: '—' }}
                        </td>

                        <td>
                            <span class="badge {{ $room->status->value === 'occupied' ? 'danger' : '' }}">
                                {{ ucwords(str_replace('_', ' ', $room->status->value)) }}
                            </span>
                        </td>

                        <td>
                            <span class="badge">
                                {{ ucwords($room->housekeeping_status) }}
                            </span>
                        </td>

                        <td>
                            <div class="table-actions">
                                <button
                                    type="button"
                                    class="btn small"
                                    data-modal-open="edit-{{ $room->id }}"
                                >
                                    <i class="fas fa-pen"></i>
                                    Edit
                                </button>

                                @if(\Illuminate\Support\Facades\Route::has('rooms.destroy'))
                                    <button
                                        type="button"
                                        class="btn small danger"
                                        data-modal-open="delete-{{ $room->id }}"
                                    >
                                        <i class="fas fa-trash"></i>
                                        Delete
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">
                            No rooms found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $rooms->links() }}
</div>
@endsection

@push('modals')
<x-hms-modal
    id="room-modal"
    title="Add Room"
>
    <form
        method="POST"
        action="{{ route('rooms.store') }}"
        class="form-grid"
    >
        @csrf

        @include(
            'rooms.partials.form',
            ['room' => null]
        )

        <div class="hms-modal-actions span-2">
            <button
                type="button"
                class="btn"
                data-modal-close
            >
                Cancel
            </button>

            <button
                type="submit"
                class="btn primary"
            >
                <i class="fas fa-floppy-disk"></i>
                Save Room
            </button>
        </div>
    </form>
</x-hms-modal>

@foreach($rooms as $room)
    <x-hms-modal
        id="edit-{{ $room->id }}"
        title="Edit Room {{ $room->room_number }}"
    >
        <form
            method="POST"
            action="{{ route('rooms.update', $room) }}"
            class="form-grid"
        >
            @csrf
            @method('PUT')

            @include(
                'rooms.partials.form',
                ['room' => $room]
            )

            <div class="hms-modal-actions span-2">
                <button
                    type="button"
                    class="btn"
                    data-modal-close
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn primary"
                >
                    Update Room
                </button>
            </div>
        </form>
    </x-hms-modal>

    @if(\Illuminate\Support\Facades\Route::has('rooms.destroy'))
        <x-delete-modal
            id="delete-{{ $room->id }}"
            :action="route('rooms.destroy', $room)"
            title="Delete Room"
            message="Delete room {{ $room->room_number }}? This action cannot be undone."
        />
    @endif
@endforeach
@endpush
