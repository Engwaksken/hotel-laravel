<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HmsRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        if (
            ! Schema::hasTable('roles')
            || ! Schema::hasTable('permissions')
            || ! Schema::hasTable('role_permission')
        ) {
            $this->command?->warn(
                'RBAC tables are missing. Run migrations first.'
            );

            return;
        }

        $roles = [
            'super_admin' => 'Super Admin',
            'admin' => 'Administrator',
            'hotel_owner' => 'Hotel Owner',
            'general_manager' => 'General Manager',
            'branch_manager' => 'Branch Manager',
            'front_office' => 'Front Office',
            'receptionist' => 'Receptionist',
            'cashier' => 'Cashier',
            'accountant' => 'Accountant',
            'finance' => 'Finance',
            'housekeeping' => 'Housekeeping',
            'maintenance' => 'Maintenance',
            'restaurant' => 'Restaurant',
            'bar' => 'Bar',
            'chef' => 'Chef',
            'storekeeper' => 'Storekeeper',
            'procurement' => 'Procurement',
            'hr' => 'Human Resources',
            'sales' => 'Sales',
            'crm' => 'CRM',
            'staff' => 'Staff',
        ];

        foreach ($roles as $name => $displayName) {
            DB::table('roles')->updateOrInsert(
                ['name' => $name],
                [
                    'display_name' => $displayName,
                    'description' => $displayName.' role',
                    'is_system' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $permissions = [
            // Dashboard
            ['dashboard.view', 'View Dashboard', 'dashboard'],

            // Front office
            ['rooms.view', 'View Rooms', 'front_office'],
            ['rooms.create', 'Create Rooms', 'front_office'],
            ['rooms.edit', 'Edit Rooms', 'front_office'],
            ['rooms.delete', 'Delete Rooms', 'front_office'],
            ['reservations.view', 'View Reservations', 'front_office'],
            ['reservations.create', 'Create Reservations', 'front_office'],
            ['reservations.edit', 'Edit Reservations', 'front_office'],
            ['reservations.delete', 'Delete Reservations', 'front_office'],
            ['guests.view', 'View Guests', 'front_office'],
            ['guests.create', 'Create Guests', 'front_office'],
            ['guests.edit', 'Edit Guests', 'front_office'],
            ['guests.delete', 'Delete Guests', 'front_office'],
            ['checkin.manage', 'Manage Check-in', 'front_office'],
            ['checkout.manage', 'Manage Check-out', 'front_office'],
            ['folios.manage', 'Manage Folios', 'front_office'],

            // POS
            ['pos.view', 'View POS', 'pos'],
            ['pos.create_orders', 'Create POS Orders', 'pos'],
            ['pos.edit_orders', 'Edit POS Orders', 'pos'],
            ['pos.void_orders', 'Void POS Orders', 'pos'],
            ['pos.settle_orders', 'Settle POS Orders', 'pos'],
            ['kitchen.view', 'View Kitchen Display', 'pos'],
            ['menu.manage', 'Manage Menu', 'pos'],

            // Operations
            ['housekeeping.view', 'View Housekeeping', 'operations'],
            ['housekeeping.manage', 'Manage Housekeeping', 'operations'],
            ['maintenance.view', 'View Maintenance', 'operations'],
            ['maintenance.manage', 'Manage Maintenance', 'operations'],
            ['laundry.view', 'View Laundry', 'operations'],
            ['laundry.manage', 'Manage Laundry', 'operations'],
            ['guest_services.manage', 'Manage Guest Services', 'operations'],
            ['lost_found.manage', 'Manage Lost & Found', 'operations'],

            // Inventory / procurement
            ['inventory.view', 'View Inventory', 'inventory'],
            ['inventory.manage', 'Manage Inventory', 'inventory'],
            ['suppliers.manage', 'Manage Suppliers', 'procurement'],
            ['requisitions.manage', 'Manage Requisitions', 'procurement'],
            ['purchase_orders.manage', 'Manage Purchase Orders', 'procurement'],
            ['grn.manage', 'Manage Goods Received Notes', 'procurement'],
            ['stock_transfers.manage', 'Manage Stock Transfers', 'procurement'],
            ['stock_counts.manage', 'Manage Stock Counts', 'procurement'],
            ['procurement_reports.view', 'View Procurement Reports', 'procurement'],

            // Finance
            ['accounting.view', 'View Accounting', 'finance'],
            ['journals.manage', 'Manage Journals', 'finance'],
            ['night_audit.manage', 'Manage Night Audit', 'finance'],
            ['reconciliation.manage', 'Manage Reconciliation', 'finance'],
            ['financial_reports.view', 'View Financial Reports', 'finance'],
            ['invoices.manage', 'Manage Invoices', 'finance'],
            ['receipts.manage', 'Manage Receipts', 'finance'],
            ['cashier_shifts.manage', 'Manage Cashier Shifts', 'finance'],

            // HR
            ['employees.view', 'View Employees', 'hr'],
            ['employees.manage', 'Manage Employees', 'hr'],
            ['attendance.manage', 'Manage Attendance', 'hr'],
            ['shifts.manage', 'Manage Staff Shifts', 'hr'],
            ['leave.manage', 'Manage Leave', 'hr'],
            ['payroll.manage', 'Manage Payroll', 'hr'],
            ['performance.manage', 'Manage Performance', 'hr'],

            // Sales / CRM
            ['sales.view', 'View Sales', 'sales'],
            ['corporate_accounts.manage', 'Manage Corporate Accounts', 'sales'],
            ['events.manage', 'Manage Events', 'sales'],
            ['quotations.manage', 'Manage Quotations', 'sales'],
            ['crm.view', 'View CRM', 'crm'],
            ['loyalty.manage', 'Manage Loyalty', 'crm'],
            ['complaints.manage', 'Manage Complaints', 'crm'],
            ['feedback.manage', 'Manage Feedback', 'crm'],
            ['promotions.manage', 'Manage Promotions', 'crm'],
            ['marketing.manage', 'Manage Marketing', 'crm'],

            // Management
            ['analytics.view', 'View Management Analytics', 'management'],
            ['budgets.manage', 'Manage Budgets', 'management'],
            ['security.view', 'View Security & Audit', 'management'],
            ['backups.manage', 'Manage Backups', 'management'],
            ['system_health.view', 'View System Health', 'management'],

            // Administration
            ['users.manage', 'Manage Users', 'administration'],
            ['roles.manage', 'Manage Roles', 'administration'],
            ['permissions.manage', 'Manage Permissions', 'administration'],
            ['configuration.manage', 'Manage Hotel Configuration', 'administration'],
            ['branding.manage', 'Manage System Branding', 'administration'],
            ['imports.manage', 'Manage Bulk Imports', 'administration'],
            ['restore.manage', 'Manage Restore', 'administration'],
        ];

        foreach ($permissions as [$name, $displayName, $module]) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                [
                    'display_name' => $displayName,
                    'module' => $module,
                    'description' => $displayName,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // Super admin gets every permission.
        $superAdminRoleId = DB::table('roles')
            ->where('name', 'super_admin')
            ->value('id');

        if ($superAdminRoleId) {
            $permissionIds = DB::table('permissions')
                ->pluck('id');

            foreach ($permissionIds as $permissionId) {
                DB::table('role_permission')->updateOrInsert(
                    [
                        'role_id' => $superAdminRoleId,
                        'permission_id' => $permissionId,
                    ],
                    [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        // Admin starts with all permissions except protected platform controls.
        $adminRoleId = DB::table('roles')
            ->where('name', 'admin')
            ->value('id');

        if ($adminRoleId) {
            $adminPermissions = DB::table('permissions')
                ->whereNotIn('name', [
                    'branding.manage',
                    'restore.manage',
                ])
                ->pluck('id');

            foreach ($adminPermissions as $permissionId) {
                DB::table('role_permission')->updateOrInsert(
                    [
                        'role_id' => $adminRoleId,
                        'permission_id' => $permissionId,
                    ],
                    [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        $this->command?->info(
            'HMS roles and permissions seeded successfully.'
        );
    }
}
