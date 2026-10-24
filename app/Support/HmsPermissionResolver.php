<?php

namespace App\Support;

use App\Models\HmsPermission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HmsPermissionResolver
{
    public function allows(User $user, string $permission): bool
    {
        if (!$user->is_active) {
            return false;
        }

        if ($user->role === 'super_admin') {
            return true;
        }

        $permissionId = HmsPermission::where('name', $permission)->value('id');
        if (!$permissionId) {
            return false;
        }

        $override = DB::table('hms_user_permissions')
            ->where('user_id', $user->id)
            ->where('permission_id', $permissionId)
            ->value('allowed');

        if ($override !== null) {
            return (bool) $override;
        }

        return DB::table('hms_role_permissions')
            ->where('role', $user->role)
            ->where('permission_id', $permissionId)
            ->exists();
    }
}
