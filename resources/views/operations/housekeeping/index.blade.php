@extends('layouts.app')
@section('title', 'Housekeeping')
@section('page_title', 'Housekeeping')
@section('content')
    <div class="page-head"><div><h1>Housekeeping Board</h1><p>Dirty → Assigned → Cleaning → Clean → Inspected → Available.</p></div><button class="btn primary" type="button" data-modal="newHousekeeping">＋ New Task</button></div>

    <div class="card table-wrap"><table><thead><tr><th>Room</th><th>Task</th><th>Status</th><th>Priority</th><th>Assigned</th><th>Due</th><th>Update</th></tr></thead><tbody>
        @forelse($tasks as $task)
            <tr>
                <td><b>{{ $task->room?->room_number }}</b><small>{{ $task->room?->housekeeping_status }}</small></td>
                <td>{{ ucwords(str_replace('_', ' ', $task->type)) }}</td>
                <td><span class="badge {{ $task->priority === 'urgent' ? 'danger' : '' }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span></td>
                <td>{{ ucfirst($task->priority) }}</td><td>{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                <td>{{ $task->due_at?->format('d M, h:i A') ?? '—' }}</td>
                <td><button class="btn small" type="button" data-modal="updateHousekeeping{{ $task->id }}">Update</button></td>
            </tr>
        @empty<tr><td colspan="7" class="empty">No housekeeping tasks.</td></tr>@endforelse
    </tbody></table></div>{{ $tasks->links() }}

    @foreach($tasks as $task)
        <div class="modal" id="updateHousekeeping{{ $task->id }}"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Update task · Room {{ $task->room?->room_number }}</h2>
            <form method="POST" action="{{ route('operations.housekeeping.status', $task) }}" class="form-grid">@csrf
                <label>Status<select name="status" required>@foreach(['pending','assigned','cleaning','clean','inspected','cancelled'] as $status)<option value="{{ $status }}" @selected($task->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
                <label>Assigned staff<select name="assigned_to"><option value="">Unassigned</option>@foreach($staff as $user)<option value="{{ $user->id }}" @selected($task->assigned_to == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                <div class="actions"><button class="btn primary" type="submit">Save update</button></div>
            </form>
        </div></div>
    @endforeach

    <div class="modal" id="newHousekeeping"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>New Housekeeping Task</h2>
        <form method="POST" action="{{ route('operations.housekeeping.store') }}" class="form-grid">@csrf
            <label>Room<select name="room_id" required>@foreach($rooms as $room)<option value="{{ $room->id }}">{{ $room->room_number }} · {{ ucfirst($room->housekeeping_status) }}</option>@endforeach</select></label>
            <label>Type<select name="type"><option value="checkout_clean">Checkout clean</option><option value="stayover">Stayover</option><option value="deep_clean">Deep clean</option><option value="inspection">Inspection</option><option value="turn_down">Turn down</option><option value="other">Other</option></select></label>
            <label>Priority<select name="priority"><option>normal</option><option>high</option><option>urgent</option><option>low</option></select></label>
            <label>Assign to<select name="assigned_to"><option value="">Unassigned</option>@foreach($staff as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></label>
            <label>Due at<input type="datetime-local" name="due_at"></label><label class="span-2">Notes<textarea name="notes"></textarea></label>
            <div class="span-2 actions end"><button class="btn primary">Create Task</button></div>
        </form>
    </div></div>
@endsection
