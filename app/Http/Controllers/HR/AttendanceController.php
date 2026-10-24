<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index()
    {
        $propertyId = auth()->user()->property_id;

        return view('hr.attendance', [
            'records' => AttendanceRecord::with('employee')
                ->where('property_id', $propertyId)
                ->latest('attendance_date')
                ->paginate(30),
            'employees' => Employee::where('property_id', $propertyId)
                ->where('status', 'active')
                ->orderBy('first_name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $propertyId = (int) $request->user()->property_id;
        $data = $request->validate([
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where('property_id', $propertyId)->where('status', 'active'),
            ],
            'attendance_date' => ['required', 'date'],
            'clock_in' => ['nullable', 'date'],
            'clock_out' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['present', 'absent', 'late', 'off', 'leave'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! empty($data['clock_in']) && ! empty($data['clock_out'])
            && strtotime($data['clock_out']) < strtotime($data['clock_in'])) {
            return back()->withErrors(['clock_out' => 'Clock-out must be after clock-in.'])->withInput();
        }

        $clockIn = $data['clock_in'] ?? null;
        $clockOut = $data['clock_out'] ?? null;
        $hours = $clockIn && $clockOut
            ? round((strtotime($clockOut) - strtotime($clockIn)) / 3600, 2)
            : 0;

        $data['property_id'] = $propertyId;
        $data['hours_worked'] = $hours;
        $data['recorded_by'] = $request->user()->id;

        AttendanceRecord::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'attendance_date' => $data['attendance_date']],
            $data,
        );

        return back()->with('success', 'Attendance saved.');
    }
}
