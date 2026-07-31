<?php

use App\Support\PermissionMenuMap;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renames the Owner role to Super Admin and introduces an editable Admin role
 * that holds every permission except the Settings menu and Site Content menu.
 *
 * Menu visibility is now driven purely by permissions, so this also seeds the
 * permissions the sidebar needs but which never had a checkbox: the module
 * flags (tasks, contracts, courses, timesheets, jobs, announcements, staff
 * permissions) plus role_permission, site_content and activity_logs.
 */
class RenameOwnerRoleAndSeedAdminRole extends Migration
{
    const SUPER_ADMIN_ROLE_ID = 1;
    const ADMIN_ROLE_ID = 2;

    public function up()
    {
        $this->ensurePermissions();
        $superAdminId = $this->renameOwnerToSuperAdmin();
        $this->grantEverything($superAdminId);
        $this->ensureAdminRole();
        $this->flushPermissionCache();
    }

    public function down()
    {
        DB::table('roles')->where('id', self::SUPER_ADMIN_ROLE_ID)->where('name', 'Super Admin')
            ->update(['name' => 'Owner', 'updated_at' => now()]);
        $this->flushPermissionCache();
    }

    private function ensurePermissions()
    {
        $existing = DB::table('permissions')->pluck('name')->all();
        $missing = array_diff(PermissionMenuMap::allPermissions(), $existing);
        if (empty($missing)) {
            return;
        }

        $now = now();
        $rows = [];
        foreach ($missing as $name) {
            $rows[] = [
                'name' => $name,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('permissions')->insert($chunk);
        }
    }

    private function renameOwnerToSuperAdmin()
    {
        $existing = DB::table('roles')->where('name', 'Super Admin')->value('id');
        if ($existing) {
            return (int) $existing;
        }

        $owner = DB::table('roles')->where('name', 'Owner')->first();
        if ($owner) {
            DB::table('roles')->where('id', $owner->id)->update([
                'name' => 'Super Admin',
                'description' => 'Full, unrestricted access to every module and setting.',
                'updated_at' => now(),
            ]);

            return (int) $owner->id;
        }

        $now = now();
        DB::table('roles')->updateOrInsert(
            ['id' => self::SUPER_ADMIN_ROLE_ID],
            [
                'name' => 'Super Admin',
                'description' => 'Full, unrestricted access to every module and setting.',
                'is_active' => 1,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return self::SUPER_ADMIN_ROLE_ID;
    }

    private function ensureAdminRole()
    {
        $now = now();
        $adminId = DB::table('roles')->where('name', 'Admin')->value('id');

        if (! $adminId) {
            $row = [
                'name' => 'Admin',
                'description' => 'Everything except the Settings menu and Site Content menu.',
                'is_active' => 1,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (! DB::table('roles')->where('id', self::ADMIN_ROLE_ID)->exists()) {
                $row['id'] = self::ADMIN_ROLE_ID;
            }
            $adminId = DB::table('roles')->insertGetId($row);
        } else {
            DB::table('roles')->where('id', $adminId)->update([
                'is_active' => 1,
                'updated_at' => $now,
            ]);
        }

        $restricted = PermissionMenuMap::restrictedPermissions();
        $grantable = DB::table('permissions')->whereNotIn('name', $restricted)->pluck('id')->all();
        $held = DB::table('role_has_permissions')->where('role_id', $adminId)->pluck('permission_id')->all();

        $rows = [];
        foreach (array_diff($grantable, $held) as $permissionId) {
            $rows[] = ['permission_id' => $permissionId, 'role_id' => $adminId];
        }
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('role_has_permissions')->insert($chunk);
        }

        // Settings and Site Content stay with Super Admin only.
        $restrictedIds = DB::table('permissions')->whereIn('name', $restricted)->pluck('id')->all();
        if (! empty($restrictedIds)) {
            DB::table('role_has_permissions')
                ->where('role_id', $adminId)
                ->whereIn('permission_id', $restrictedIds)
                ->delete();
        }
    }

    private function grantEverything($roleId)
    {
        if (! $roleId) {
            return;
        }

        $held = DB::table('role_has_permissions')->where('role_id', $roleId)->pluck('permission_id')->all();
        $missing = DB::table('permissions')->whereNotIn('id', $held ?: [0])->pluck('id')->all();

        $rows = array_map(function ($permissionId) use ($roleId) {
            return ['permission_id' => $permissionId, 'role_id' => $roleId];
        }, $missing);

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('role_has_permissions')->insert($chunk);
        }
    }

    private function flushPermissionCache()
    {
        app()['cache']->forget('spatie.permission.cache');
    }
}
