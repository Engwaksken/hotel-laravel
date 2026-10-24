@extends('layouts.app')
@section('title', 'Staff Shifts')
@section('page_title', 'Staff Shifts')
@section('content')
    <div class="page-head"><div><h1>Staff shifts</h1><p>Create shift patterns and assign employees to upcoming dates.</p></div><div class="actions"><button class="btn" type="button" data-modal="createShift">Create shift</button><button class="btn primary" type="button" data-modal="assignShift">Assign shift</button></div></div>
    <div class="card table-wrap"><table><thead><tr><th>Date</th><th>Employee</th><th>Shift</th><th>Time</th></tr></thead><tbody>
        @forelse($assignments as $assignment)<tr><td>{{ $assignment->shift_date->format('d M Y') }}</td><td>{{ $assignment->employee?->full_name }}</td><td>{{ $assignment->shift?->name }}</td><td>{{ $assignment->shift?->starts_at }} – {{ $assignment->shift?->ends_at }}</td></tr>
        @empty<tr><td colspan="4" class="empty">No upcoming shift assignments.</td></tr>@endforelse
    </tbody></table></div>
    <div class="modal" id="createShift"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Create shift</h2><form method="POST" action="{{ route('hr.shifts.store') }}" class="stack">@csrf
        <label>Name<input name="name" required></label><label>Starts<input type="time" name="starts_at" required></label><label>Ends<input type="time" name="ends_at" required></label><label>Break minutes<input type="number" min="0" name="break_minutes" value="0"></label><button class="btn primary">Create shift</button>
    </form></div></div>
    <div class="modal" id="assignShift"><div class="modal-box"><button class="modal-close" type="button" aria-label="Close">&times;</button><h2>Assign shift</h2><form method="POST" action="{{ route('hr.shifts.assign') }}" class="stack">@csrf
        <label>Employee<select name="employee_id" required>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->full_name }}</option>@endforeach</select></label><label>Shift<select name="staff_shift_id" required>@foreach($shifts as $shift)<option value="{{ $shift->id }}">{{ $shift->name }}</option>@endforeach</select></label><label>Date<input type="date" name="shift_date" required></label><button class="btn primary">Assign shift</button>
    </form></div></div>
@endsection
