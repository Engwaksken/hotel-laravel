<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Department;
use App\Models\JobPosition;
use App\Models\LeaveType;
use App\Models\PayrollComponent;
use App\Models\Property;
use App\Models\StaffShift;
use Illuminate\Database\Seeder;

class HotelHrSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Property::all() as $property) {
            $departments = [
                'FRO' => 'Front Office', 'HOU' => 'Housekeeping', 'FNB' => 'Food & Beverage',
                'KIT' => 'Kitchen', 'FIN' => 'Finance', 'HR' => 'Human Resources',
                'ENG' => 'Engineering & Maintenance', 'SEC' => 'Security',
            ];

            foreach ($departments as $code => $name) {
                Department::firstOrCreate(['property_id' => $property->id, 'code' => $code], ['name' => $name]);
            }

            $positions = [
                ['Receptionist', 'REC', 'FRO'], ['Housekeeper', 'HSK', 'HOU'],
                ['Waiter', 'WTR', 'FNB'], ['Chef', 'CHF', 'KIT'], ['Accountant', 'ACC', 'FIN'],
                ['HR Officer', 'HRO', 'HR'], ['Maintenance Technician', 'MNT', 'ENG'],
            ];
            foreach ($positions as [$title, $code, $departmentCode]) {
                JobPosition::firstOrCreate(
                    ['property_id' => $property->id, 'code' => $code],
                    [
                        'title' => $title,
                        'department_id' => Department::where('property_id', $property->id)
                            ->where('code', $departmentCode)
                            ->value('id'),
                    ],
                );
            }

            foreach ([['Morning', '07:00', '15:00'], ['Evening', '15:00', '23:00'], ['Night', '23:00', '07:00']] as [$name, $start, $end]) {
                StaffShift::firstOrCreate(
                    ['property_id' => $property->id, 'name' => $name],
                    ['starts_at' => $start, 'ends_at' => $end, 'is_overnight' => $end <= $start],
                );
            }

            foreach ([['Annual Leave', 21, true], ['Sick Leave', 0, true], ['Maternity Leave', 0, true], ['Paternity Leave', 0, true], ['Unpaid Leave', 0, false]] as [$name, $days, $paid]) {
                LeaveType::firstOrCreate(
                    ['property_id' => $property->id, 'name' => $name],
                    ['days_per_year' => $days, 'is_paid' => $paid],
                );
            }

            $components = [
                ['Transport Allowance', 'TRANSPORT', 'earning', 'fixed', 0, false, true],
                ['Housing Allowance', 'HOUSING', 'earning', 'fixed', 0, false, true],
                ['PAYE (URA progressive)', 'PAYE', 'deduction', 'fixed', 0, true, false],
                ['NSSF employee contribution', 'PENSION_EE', 'deduction', 'percentage', 5, true, false],
                ['NSSF employer contribution', 'PENSION_ER', 'employer_cost', 'percentage', 10, true, false],
            ];

            foreach ($components as [$name, $code, $type, $calculation, $value, $statutory, $taxable]) {
                PayrollComponent::firstOrCreate(
                    ['property_id' => $property->id, 'code' => $code],
                    [
                        'name' => $name,
                        'type' => $type,
                        'calculation' => $calculation,
                        'default_value' => $value,
                        'statutory' => $statutory,
                        'taxable' => $taxable,
                    ],
                );
            }

            foreach ([
                ['2250', 'Payroll Payable'],
                ['2260', 'Payroll Deductions Payable'],
                ['2270', 'Employer Statutory Contributions Payable'],
            ] as [$code, $name]) {
                Account::firstOrCreate(
                    ['property_id' => $property->id, 'code' => $code],
                    ['name' => $name, 'type' => 'liability', 'is_control' => true, 'is_active' => true],
                );
            }
        }
    }
}
