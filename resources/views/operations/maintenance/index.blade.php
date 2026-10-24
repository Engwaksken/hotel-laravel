@extends('layouts.app')
@section('title', 'Maintenance')
@section('page_title', 'Maintenance')
@section('content')
    <div class="page-head"><div><h1>Maintenance &amp; Engineering</h1><p>Report faults, assign technicians and block unsafe rooms from sale.</p></div><button class="btn primary" type="button" data-modal="newMaintenance">＋ Report Issue</button></div>

    <div class="card table-wrap"><table><thead><tr><th>Issue</th><th>Room</th><th>Priority</th><th>Status</th><th>Room Block</th><th>Due</th><th>Cost</th><th>Action</th></tr></thead><tbody>
        @forelse($requests as $request)
            <tr>
                <td><b>{{ $request->title }}</b><small>{{ ucfirst($request->category) }} · {{ $request->assignee?->name ?? 'Unassigned' }}</small></td>
                <td>{{ $request->room?->room_number ?? 'General' }}</td>
                <td><span class="badge {{ $request->priority === 'emergency' ? 'danger' : ($request->priority === 'high' ? 'warning' : '') }}">{{ ucfirst($request->priority) }}</span></td>
                <td>{{ str_replace('_', ' ', ucfirst($request->status)) }}</td><td>{{ $request->blocks_room ? 'Yes' : 'No' }}</td>
                <td>{{ $request->due_at?->format('d M, h:i A') ?? '—' }}</td><td>{{ number_format($request->actual_cost ?: $request->estimated_cost, 0) }}</td>
                <td><button class="btn small" type="button" data-modal="updateMaintenance{{ $request->id }}">Update</button></td>
            </tr>
        @empty<tr><td colspan="8" class="empty">No maintenance requests.</td></tr>@endforelse
    </tbody></table></div>{{ $requests->links() }}

    @foreach($requests as $request)
        <div class="modal" id="updateMaintenance{{ $request->id }}"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Update maintenance issue</h2>
            <form class="form-grid" method="POST" action="{{ route('operations.maintenance.status', $request) }}">@csrf
                <label>Status<select name="status" required>@foreach(['open','assigned','in_progress','waiting_parts','resolved','closed','cancelled'] as $status)<option value="{{ $status }}" @selected($request->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></label>
                <label>Assigned technician<select name="assigned_to"><option value="">Unassigned</option>@foreach($staff as $user)<option value="{{ $user->id }}" @selected($request->assigned_to == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                <label>Due at<input type="datetime-local" name="due_at" value="{{ $request->due_at?->format('Y-m-d\TH:i') }}"></label>
                <label>Actual cost<input type="number" min="0" step="0.01" name="actual_cost" value="{{ $request->actual_cost }}"></label>
                <label class="span-2">Resolution notes<textarea name="resolution_notes">{{ $request->resolution_notes }}</textarea></label>
                <div class="span-2 actions end"><button class="btn primary" type="submit">Save update</button></div>
            </form>
        </div></div>
    @endforeach

    <div class="modal" id="newMaintenance"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Report Maintenance Issue</h2>
        <form method="POST" action="{{ route('operations.maintenance.store') }}" class="form-grid">@csrf
            <label>Room<select name="room_id"><option value="">General / no room</option>@foreach($rooms as $room)<option value="{{ $room->id }}">{{ $room->room_number }}</option>@endforeach</select></label>
            <label>Category<select name="category"><option>Electrical</option><option>Plumbing</option><option>Air Conditioning</option><option>TV</option><option>Door Lock</option><option>Furniture</option><option>Wi-Fi</option><option>Bathroom</option><option>Other</option></select></label>
            <label class="span-2">Title<input name="title" required></label><label>Priority<select name="priority"><option>normal</option><option>high</option><option>emergency</option><option>low</option></select></label>
            <label>Assign to<select name="assigned_to"><option value="">Unassigned</option>@foreach($staff as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></label>
            <label>Due at<input type="datetime-local" name="due_at"></label><label>Estimated cost<input type="number" min="0" step="1" name="estimated_cost"></label>
            <label class="check"><input type="checkbox" name="blocks_room" value="1"> Block room from sale</label><label class="span-2">Description<textarea name="description"></textarea></label>
            <div class="span-2 actions end"><button class="btn primary">Create Request</button></div>
        </form>
    </div></div>
@endsection
