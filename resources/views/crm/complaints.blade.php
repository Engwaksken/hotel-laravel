@extends('layouts.app')
@section('title', 'Complaints')
@section('page_title', 'Guest Complaints')
@section('content')
    <div class="page-head"><div><h1>Guest complaints</h1><p>Record, assign and resolve guest concerns.</p></div><button class="btn primary" type="button" data-modal="recordComplaint">Record complaint</button></div>
    <div class="card table-wrap"><table><thead><tr><th>Reference</th><th>Guest</th><th>Category</th><th>Priority</th><th>Assigned</th><th>Due</th><th>Status</th><th>Action</th></tr></thead><tbody>
        @forelse($complaints as $complaint)<tr><td>{{ $complaint->reference }}</td><td>{{ $complaint->guest?->full_name ?? '—' }}</td><td>{{ $complaint->category }}</td><td>{{ ucfirst($complaint->priority) }}</td><td>{{ $complaint->assignee?->name ?? 'Unassigned' }}</td><td>{{ $complaint->due_at?->format('d M, h:i A') ?? '—' }}</td><td>{{ ucfirst($complaint->status) }}</td><td>@if($complaint->status !== 'resolved')<form method="POST" action="{{ route('crm.complaints.resolve', $complaint) }}">@csrf<input name="resolution" placeholder="Resolution" required><button class="btn small">Resolve</button></form>@else{{ $complaint->resolution }}@endif</td></tr>
        @empty<tr><td colspan="8" class="empty">No complaints recorded.</td></tr>@endforelse
    </tbody></table>{{ $complaints->links() }}</div>
    <div class="modal {{ $errors->any() ? 'open' : '' }}" id="recordComplaint"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Record complaint</h2>
        <form method="POST" action="{{ route('crm.complaints.store') }}" class="form-grid">@csrf
            <label>Guest (optional)<select name="guest_id"><option value="">Select guest</option>@foreach($guests as $guest)<option value="{{ $guest->id }}">{{ $guest->full_name }}</option>@endforeach</select></label>
            <label>Category<input name="category" placeholder="Room, food, service…" required></label>
            <label>Priority<select name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></label>
            <label>Assign staff<select name="assigned_to"><option value="">Unassigned</option>@foreach($staff as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></label>
            <label>Due at<input type="datetime-local" name="due_at"></label><label class="span-2">Complaint details<textarea name="description" required></textarea></label>
            <div class="actions"><button class="btn primary">Save complaint</button></div>
        </form>
    </div></div>
@endsection
