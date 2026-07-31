<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Permission checks for the admin area.
 *
 * Roles are stored on users.role_id and their permissions live in Spatie's
 * role_has_permissions table. Role 1 (Super Admin) is the only implicit
 * bypass — every other role, including Admin, is driven by granted permissions.
 */
class RoleAccess
{
    const SUPER_ADMIN_ROLE_ID = 1;
    const SUPER_ADMIN_ROLE_NAMES = ['Super Admin', 'Owner'];

    public static function isSuperAdmin($user = null)
    {
        $user = $user ?: Auth::user();

        return $user && self::isSuperAdminRole((int) $user->role_id);
    }

    public static function isSuperAdminRole($roleId)
    {
        static $cache = [];

        $roleId = (int) $roleId;
        if (! array_key_exists($roleId, $cache)) {
            $name = DB::table('roles')->where('id', $roleId)->value('name');
            $cache[$roleId] = $name === null
                ? $roleId === self::SUPER_ADMIN_ROLE_ID
                : in_array($name, self::SUPER_ADMIN_ROLE_NAMES, true);
        }

        return $cache[$roleId];
    }

    public static function allows($permission, $user = null)
    {
        $user = $user ?: Auth::user();
        if (! $user) {
            return false;
        }
        if (self::isSuperAdmin($user)) {
            return true;
        }

        return self::roleHas((int) $user->role_id, $permission);
    }

    /** True when the role holds at least one of the given permissions. */
    public static function allowsAny(array $permissions, $user = null)
    {
        foreach ($permissions as $permission) {
            if (self::allows($permission, $user)) {
                return true;
            }
        }

        return false;
    }

    protected static function roleHas($roleId, $permission)
    {
        static $cache = [];

        $key = $roleId.'|'.$permission;
        if (! array_key_exists($key, $cache)) {
            $permissionId = DB::table('permissions')->where('name', $permission)->value('id');
            $cache[$key] = $permissionId
                ? DB::table('role_has_permissions')
                    ->where('permission_id', $permissionId)
                    ->where('role_id', $roleId)
                    ->exists()
                : false;
        }

        return $cache[$key];
    }
}
