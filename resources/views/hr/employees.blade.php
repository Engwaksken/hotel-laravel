@extends('layouts.app')
@section('title', 'Employees')
@section('page_title', 'Employees')
@section('content')
    @if(auth()->user()?->canManageEntries())
        <div class="page-actions">
            <button class="btn primary" type="button" data-modal="employeeModal">
                <i class="fas fa-plus"></i> Add Employee
            </button>
        </div>
    @endif

    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No.</th><th>Name</th><th>Department</th><th>Position</th><th>Phone</th>
                    <th>Hire date</th><th>Salary</th><th>Tax residency</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td>{{ $employee->employee_no }}</td>
                        <td>{{ $employee->full_name }}</td>
                        <td>{{ $employee->department?->name ?? '—' }}</td>
                        <td>{{ $employee->position?->title ?? '—' }}</td>
                        <td>{{ $employee->phone ?: '—' }}</td>
                        <td>{{ $employee->hire_date?->format('d M Y') }}</td>
                        <td>UGX {{ number_format((float) $employee->basic_salary) }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($employee->tax_residency ?? 'resident')) }}</td>
                        <td>{{ ucfirst($employee->status) }}</td>
                        <td><x-entry-actions resource="employees" :id="$employee->id" label="employee" /></td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="empty">No employees have been added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $employees->links() }}
    </div>

    @if(auth()->user()?->canManageEntries())
        <div class="modal" id="employeeModal">
            <div class="modal-box">
                <button class="modal-close" type="button" aria-label="Close">&times;</button>
                <h3>Add Employee</h3>
                <form method="POST" action="{{ route('hr.employees.store') }}">
                    @csrf
                    <div class="form-grid">
                        <label>First name<input name="first_name" required></label>
                        <label>Last name<input name="last_name" required></label>
                        <label>Phone<input name="phone"></label>
                        <label>Email<input type="email" name="email"></label>
                        <label>Department
                            <select name="department_id">
                                <option value="">Select department</option>
                                @foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
                            </select>
                        </label>
                        <label>Position
                            <select name="job_position_id">
                                <option value="">Select position</option>
                                @foreach($positions as $position)<option value="{{ $position->id }}">{{ $position->title }}</option>@endforeach
                            </select>
                        </label>
                        <label>Hire date<input type="date" name="hire_date" required></label>
                        <label>Employment type
                            <select name="employment_type">
                                <option value="permanent">Permanent</option><option value="contract">Contract</option>
                                <option value="casual">Casual</option><option value="intern">Intern</option>
                            </select>
                        </label>
                        <label>Basic monthly salary (UGX)<input type="number" step="0.01" min="0" name="basic_salary" value="0" required></label>
                        <label>Tax residency
                            <select name="tax_residency" required>
                                <option value="resident">Resident</option>
                                <option value="non_resident">Non-resident</option>
                            </select>
                        </label>
                        <label class="check-row">
                            <input type="checkbox" name="nssf_eligible" value="1" checked>
                            Subject to NSSF contributions
                        </label>
                    </div>
                    <button class="btn primary" type="submit">Save Employee</button>
                </form>
            </div>
        </div>
    @endif
@endsection
