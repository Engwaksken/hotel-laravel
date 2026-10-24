@extends('layouts.app')
@section('title', 'Guest Services')
@section('page_title', 'Guest Services')
@section('content')
    <div class="page-head"><div><h1>Guest Service Requests</h1><p>Track requests from reception, housekeeping, phone or the future Flutter guest app.</p></div><button class="btn primary" type="button" data-modal="newGuestRequest">＋ New Request</button></div>

    <div class="card table-wrap"><table><thead><tr><th>Guest / Room</th><th>Request</th><th>Priority</th><th>Assigned</th><th>Due</th><th>Charge</th><th>Status</th><th>Update</th></tr></thead><tbody>
        @forelse($requests as $request)
            <tr>
                <td><b>{{ $request->guest?->full_name ?? 'Guest' }}</b><small>Room {{ $request->room?->room_number ?? '—' }} · {{ $request->requested_at->format('d M h:i A') }}</small></td>
                <td>{{ ucwords(str_replace('_', ' ', $request->request_type)) }}<small>{{ $request->details }}</small></td>
                <td>{{ ucfirst($request->priority) }}</td><td>{{ $request->assignee?->name ?? 'Unassigned' }}</td>
                <td>{{ $request->due_at?->format('d M, h:i A') ?? '—' }}</td><td>{{ number_format($request->charge_amount, 0) }}</td>
                <td><span class="badge">{{ str_replace('_', ' ', ucfirst($request->status)) }}</span></td>
                <td><button class="btn small" type="button" data-modal="updateGuestRequest{{ $request->id }}">Update</button></td>
            </tr>
        @empty<tr><td colspan="8" class="empty">No guest service requests.</td></tr>@endforelse
    </tbody></table></div>{{ $requests->links() }}

    @foreach($requests as $request)
        <div class="modal" id="updateGuestRequest{{ $request->id }}"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Update guest request</h2>
            <form class="form-grid" method="POST" action="{{ route('operations.guest-services.status', $request) }}">@csrf
                <label>Status<select name="status" required>@foreach(['new','accepted','in_progress','completed','cancelled'] as $status)<option value="{{ $status }}" @selected($request->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></label>
                <label>Assigned staff<select name="assigned_to"><option value="">Unassigned</option>@foreach($staff as $user)<option value="{{ $user->id }}" @selected($request->assigned_to == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                <label>Due at<input type="datetime-local" name="due_at" value="{{ $request->due_at?->format('Y-m-d\TH:i') }}"></label>
                <div class="span-2 actions end"><button class="btn primary" type="submit">Save update</button></div>
            </form>
        </div></div>
    @endforeach

    <div class="modal" id="newGuestRequest"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>New Guest Request</h2>
        <form method="POST" action="{{ route('operations.guest-services.store') }}" class="form-grid">@csrf
            <label>In-house guest<select name="stay_id"><option value="">No linked stay</option>@foreach($stays as $stay)<option value="{{ $stay->id }}">Room {{ $stay->room?->room_number }} · {{ $stay->guest?->full_name }}</option>@endforeach</select></label>
            <label>Request<select name="request_type"><option value="extra_towels">Extra towels</option><option value="water">Drinking water</option><option value="room_cleaning">Room cleaning</option><option value="wake_up_call">Wake-up call</option><option value="iron">Iron / ironing board</option><option value="extra_bed">Extra bed</option><option value="airport_transfer">Airport transfer</option><option value="maintenance">Maintenance</option><option value="other">Other</option></select></label>
            <label>Priority<select name="priority"><option>normal</option><option>high</option><option>urgent</option><option>low</option></select></label><label>Optional charge<input type="number" name="charge_amount" value="0" min="0"></label>
            <label>Due at<input type="datetime-local" name="due_at"></label><label>Assign to<select name="assigned_to"><option value="">Unassigned</option>@foreach($staff as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></label>
            <label class="span-2">Details<textarea name="details"></textarea></label><div class="span-2 actions end"><button class="btn primary">Create Request</button></div>
        </form>
    </div></div>
@endsection
