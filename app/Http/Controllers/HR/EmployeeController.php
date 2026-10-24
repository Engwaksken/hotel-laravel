<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobPosition;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $propertyId = auth()->user()->property_id;

        return view('hr.employees', [
            'employees' => Employee::with(['department', 'position'])
                ->where('property_id', $propertyId)
                ->latest()
                ->paginate(25),
            'departments' => Department::where('property_id', $propertyId)->where('is_active', true)->get(),
            'positions' => JobPosition::where('property_id', $propertyId)->where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $propertyId = (int) $request->user()->property_id;
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:190'],
            'department_id' => ['nullable', 'integer'],
            'job_position_id' => ['nullable', 'integer'],
            'hire_date' => ['required', 'date'],
            'employment_type' => ['required', 'string', 'max:30'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'tax_residency' => ['required', 'in:resident,non_resident'],
            'nssf_eligible' => ['nullable', 'boolean'],
        ]);

        $data['property_id'] = $propertyId;
        $data['nssf_eligible'] = $request->boolean('nssf_eligible', true);
        $data['employee_no'] = 'EMP-'.str_pad(
            (string) (Employee::where('property_id', $propertyId)->count() + 1),
            5,
            '0',
            STR_PAD_LEFT,
        );

        Employee::create($data);

        return back()->with('success', 'Employee added.');
    }
}
