@extends('layouts.app')
@section('title', 'Attendance')
@section('page_title', 'Attendance')
@section('content')
    <div class="page-head">
        <div>
            <h1>Attendance</h1>
            <p>Record and review employee attendance by date.</p>
        </div>
        @if($employees->isNotEmpty() && auth()->user()?->canManageEntries())
            <button class="btn primary" type="button" data-modal="attendanceModal">
                <i class="fas fa-plus"></i> Record attendance
            </button>
        @endif
    </div>

    @if($employees->isEmpty())
        <div class="alert">Add active employees before recording attendance.</div>
    @endif

    <div class="card table-wrap">
        <table>
            <thead>
                <tr><th>Date</th><th>Employee</th><th>Clock in</th><th>Clock out</th><th>Hours</th><th>Status</th><th>Notes</th></tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->attendance_date?->format('d M Y') }}</td>
                        <td>{{ $record->employee?->full_name ?? 'Employee record unavailable' }}</td>
                        <td>{{ $record->clock_in?->format('h:i A') ?? '—' }}</td>
                        <td>{{ $record->clock_out?->format('h:i A') ?? '—' }}</td>
                        <td>{{ number_format((float) $record->hours_worked, 2) }}</td>
                        <td>{{ ucfirst($record->status) }}</td>
                        <td>{{ $record->notes ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No attendance records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $records->links() }}
    </div>

    @if($employees->isNotEmpty() && auth()->user()?->canManageEntries())
        <div class="modal {{ $errors->any() ? 'open' : '' }}" id="attendanceModal">
            <div class="modal-box">
                <button class="modal-close" type="button" aria-label="Close">&times;</button>
                <h2>Record attendance</h2>
                <form method="POST" action="{{ route('hr.attendance.store') }}">
                    @csrf
                    <div class="form-grid">
                        <label>Employee
                            <select name="employee_id" required>
                                <option value="">Select employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Date<input type="date" name="attendance_date" value="{{ old('attendance_date', today()->toDateString()) }}" required></label>
                        <label>Clock in<input type="datetime-local" name="clock_in" value="{{ old('clock_in') }}"></label>
                        <label>Clock out<input type="datetime-local" name="clock_out" value="{{ old('clock_out') }}"></label>
                        <label>Status
                            <select name="status" required>
                                @foreach(['present', 'absent', 'late', 'off', 'leave'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', 'present') === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="span-2">Notes<textarea name="notes" maxlength="1000">{{ old('notes') }}</textarea></label>
                    </div>
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                    @endif
                    <div class="actions"><button class="btn primary" type="submit">Save attendance</button></div>
                </form>
            </div>
        </div>
    @endif
@endsection
