<?php

namespace Database\Seeders;

use App\Models\HmsPermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HotelBranchManagerSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'branch.manage' => ['branch', 'Manage assigned hotel branch'],
            'dashboard.view' => ['dashboard', 'View branch dashboard'],
            'reservations.manage' => ['reservations', 'Manage branch reservations'],
            'rooms.manage' => ['rooms', 'Manage branch rooms and room types'],
            'guests.manage' => ['guests', 'Manage branch guests and folios'],
            'payments.manage' => ['payments', 'Manage branch guest payments'],
            'pos.manage' => ['pos', 'Manage branch restaurant and bar POS'],
            'inventory.manage' => ['inventory', 'Manage branch inventory'],
            'procurement.manage' => ['procurement', 'Manage branch procurement'],
            'housekeeping.manage' => ['housekeeping', 'Manage branch housekeeping'],
            'maintenance.manage' => ['maintenance', 'Manage branch maintenance'],
            'staff.branch.manage' => ['staff', 'Manage staff assigned to the branch'],
            'attendance.manage' => ['attendance', 'Manage branch attendance'],
            'leave.manage' => ['leave', 'Manage branch leave'],
            'crm.manage' => ['crm', 'Manage branch guest CRM'],
            'events.manage' => ['events', 'Manage branch events and corporate sales'],
            'night_audit.manage' => ['accounting', 'Run branch night audit'],
            'accounting.branch' => ['accounting', 'View and operate branch accounting'],
            'reports.branch' => ['reports', 'View branch operational and financial reports'],
            'budget.branch' => ['management', 'Manage branch budgets'],
            'frontdesk.override_balance' => ['frontdesk', 'Authorise checkout with approved outstanding balance'],
            'staff.operations' => ['staff', 'Use staff operations mobile tools'],
            'management.view' => ['management', 'View branch management analytics'],
        ];

        foreach ($permissions as $name => [$module, $label]) {
            HmsPermission::updateOrCreate(
                ['name' => $name],
                ['module' => $module, 'label' => $label]
            );
        }

        foreach (array_keys($permissions) as $name) {
            $permissionId = HmsPermission::where('name', $name)->value('id');

            DB::table('hms_role_permissions')->updateOrInsert(
                [
                    'role' => 'branch_manager',
                    'permission_id' => $permissionId,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
