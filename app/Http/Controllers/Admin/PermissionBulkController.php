<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PermissionBulkController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorise($request);

        if (
            ! Schema::hasTable('roles')
            || ! Schema::hasTable('permissions')
            || ! Schema::hasTable('role_permission')
        ) {
            return view('admin.permissions.bulk', [
                'roles' => collect(),
                'permissions' => collect(),
                'selectedRoleId' => 0,
                'selectedPermissionIds' => collect(),
                'schemaWarning' =>
                    'The role/permission tables have not been installed yet. '
                    .'Run the HMS RBAC migration and seeder.',
                'pivotTable' => null,
            ]);
        }

        $roles = DB::table('roles')
            ->orderBy('display_name')
            ->orderBy('name')
            ->get();

        $permissions = DB::table('permissions')
            ->orderBy('module')
            ->orderBy('display_name')
            ->orderBy('name')
            ->get();

        $selectedRoleId = (int) $request->query(
            'role_id',
            old(
                'role_id',
                $roles->first()->id ?? 0
            )
        );

        $selectedPermissionIds = $selectedRoleId > 0
            ? DB::table('role_permission')
                ->where('role_id', $selectedRoleId)
                ->pluck('permission_id')
                ->map(fn ($id) => (int) $id)
                ->values()
            : collect();

        return view('admin.permissions.bulk', [
            'roles' => $roles,
            'permissions' => $permissions,
            'selectedRoleId' => $selectedRoleId,
            'selectedPermissionIds' => $selectedPermissionIds,
            'schemaWarning' => null,
            'pivotTable' => 'role_permission',
        ]);
    }

    public function sync(Request $request): RedirectResponse
    {
        $this->authorise($request);

        if (
            ! Schema::hasTable('roles')
            || ! Schema::hasTable('permissions')
            || ! Schema::hasTable('role_permission')
        ) {
            return back()->with(
                'error',
                'The role and permission tables are not installed.'
            );
        }

        $data = $request->validate([
            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],
            'permission_ids' => [
                'nullable',
                'array',
            ],
            'permission_ids.*' => [
                'integer',
                'distinct',
                'exists:permissions,id',
            ],
        ]);

        $roleId = (int) $data['role_id'];

        $permissionIds = collect(
            $data['permission_ids'] ?? []
        )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $role = DB::table('roles')
            ->where('id', $roleId)
            ->first();

        abort_if(! $role, 404, 'Role not found.');

        // Protect Super Admin from accidentally losing all access.
        if (
            strtolower((string) $role->name)
            === 'super_admin'
        ) {
            $permissionIds = DB::table('permissions')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();
        }

        DB::transaction(function () use (
            $roleId,
            $permissionIds
        ): void {
            DB::table('role_permission')
                ->where('role_id', $roleId)
                ->delete();

            if ($permissionIds->isEmpty()) {
                return;
            }

            $now = now();

            DB::table('role_permission')->insert(
                $permissionIds
                    ->map(fn (int $permissionId): array => [
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                    ->all()
            );
        });

        return redirect()
            ->route(
                'admin.permissions.bulk',
                ['role_id' => $roleId]
            )
            ->with(
                'success',
                $permissionIds->count()
                .' permission(s) saved successfully.'
            );
    }

    private function authorise(
        Request $request
    ): void {
        $role = strtolower(
            (string) (
                $request->user()->role ?? ''
            )
        );

        abort_unless(
            in_array(
                $role,
                [
                    'super_admin',
                    'admin',
                ],
                true
            ),
            403,
            'You do not have permission to manage role permissions.'
        );
    }
}
