@extends('layouts.app')
@section('title', 'Leave')
@section('page_title', 'Leave Management')
@section('content')
    <div class="page-head"><div><h1>Leave management</h1><p>Review employee leave requests and their approval status.</p></div><button class="btn primary" type="button" data-modal="newLeaveRequest">New leave request</button></div>
    <div class="card table-wrap"><table><thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th>Action</th></tr></thead><tbody>
        @forelse($requests as $request)<tr><td>{{ $request->employee?->full_name }}</td><td>{{ $request->type?->name }}</td><td>{{ $request->start_date->format('d M') }} – {{ $request->end_date->format('d M Y') }}</td><td>{{ $request->days }}</td><td>{{ ucfirst($request->status) }}</td><td>@if($request->status === 'pending')<form method="POST" action="{{ route('hr.leave.decision', $request) }}" style="display:inline">@csrf<input type="hidden" name="status" value="approved"><button class="btn small">Approve</button></form> <form method="POST" action="{{ route('hr.leave.decision', $request) }}" style="display:inline">@csrf<input type="hidden" name="status" value="rejected"><button class="btn small danger">Reject</button></form>@endif</td></tr>
        @empty<tr><td colspan="6" class="empty">No leave requests.</td></tr>@endforelse
    </tbody></table>{{ $requests->links() }}</div>
    <div class="modal" id="newLeaveRequest"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>New leave request</h2><form method="POST" action="{{ route('hr.leave.request') }}" class="form-grid">@csrf
        <label>Employee<select name="employee_id" required>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->full_name }}</option>@endforeach</select></label><label>Leave type<select name="leave_type_id" required>@foreach($types as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach</select></label>
        <label>Start<input type="date" name="start_date" required></label><label>End<input type="date" name="end_date" required></label><label class="span-2">Reason<input name="reason"></label><div class="actions"><button class="btn primary">Submit request</button></div>
    </form></div></div>
@endsection
