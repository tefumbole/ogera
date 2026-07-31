<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ogera started from a bare database, so several roles and permissions the ERP
 * code refers to by name (or by the legacy id 5 = customer) never existed here.
 * Missing rows make Spatie throw PermissionDoesNotExist and make Role::find()
 * return null, which surfaced as 500s across People, Sales and Booking.
 */
class EnsureBaselineRolesAndPermissions extends Migration
{
    /** Legacy id the whole codebase uses for client/portal accounts. */
    const CUSTOMER_ROLE_ID = 5;

    public function up()
    {
        $this->ensureRoles();
        $this->ensurePermissions();
        $this->grantEverythingToOwner();
        $this->repairOrphanUserRoles();
    }

    public function down()
    {
        // Seeding baseline access data is not reversible.
    }

    private function ensureRoles()
    {
        $now = now();

        $owner = DB::table('roles')->where('name', 'Owner')->first();
        if (! $owner) {
            DB::table('roles')->insert([
                'id' => 1,
                'name' => 'Owner',
                'is_active' => 1,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $customer = DB::table('roles')->where('name', 'Customer')->first();
        if (! $customer) {
            $idTaken = DB::table('roles')->where('id', self::CUSTOMER_ROLE_ID)->exists();
            $row = [
                'name' => 'Customer',
                'description' => 'Client portal access',
                'is_active' => 1,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (! $idTaken) {
                $row['id'] = self::CUSTOMER_ROLE_ID;
            }
            DB::table('roles')->insert($row);
        }
    }

    private function ensurePermissions()
    {
        $existing = DB::table('permissions')->pluck('name')->all();
        $missing = array_diff($this->permissionNames(), $existing);
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

    private function grantEverythingToOwner()
    {
        $ownerId = DB::table('roles')->where('name', 'Owner')->value('id');
        if (! $ownerId) {
            return;
        }

        $held = DB::table('role_has_permissions')->where('role_id', $ownerId)->pluck('permission_id')->all();
        $missing = DB::table('permissions')->whereNotIn('id', $held ?: [0])->pluck('id')->all();
        if (empty($missing)) {
            return;
        }

        $rows = array_map(function ($permissionId) use ($ownerId) {
            return ['permission_id' => $permissionId, 'role_id' => $ownerId];
        }, $missing);

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('role_has_permissions')->insert($chunk);
        }

        app()['cache']->forget('spatie.permission.cache');
    }

    /**
     * Client accounts created before the Customer role existed point at a role
     * row that is not there; every admin view then fails on a null role.
     */
    private function repairOrphanUserRoles()
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $customerRoleId = DB::table('roles')->where('name', 'Customer')->value('id');
        if (! $customerRoleId) {
            return;
        }

        $roleIds = DB::table('roles')->pluck('id')->all();
        DB::table('users')
            ->whereNotIn('role_id', $roleIds ?: [0])
            ->update(['role_id' => $customerRoleId]);
    }

    private function permissionNames()
    {
        return [
            'JE-method', 'account-index', 'account-statement', 'activity-add', 'activity-delete',
            'activity-edit', 'activity-index', 'adjustment', 'announcement-index', 'announcement_add',
            'announcement_delete', 'announcement_edit', 'announcement_index', 'asset-add', 'asset-delete',
            'asset-disppose', 'asset-edit', 'asset-expense-add', 'asset-expense-delete', 'asset-expense-edit',
            'asset-expense-index', 'asset-index', 'asset-sale', 'asset-transfer', 'asset-type-add',
            'asset-type-delete', 'asset-type-edit', 'asset-type-index', 'attendance', 'average-report',
            'backup_database', 'balance-sheet', 'best-seller', 'billers-add', 'billers-delete',
            'billers-edit', 'billers-index', 'booking_add', 'booking_awaiting_signature', 'booking_contract_approve',
            'booking_create', 'booking_delete', 'booking_edit', 'booking_goods_received', 'booking_index',
            'booking_module', 'booking_pending_review', 'booking_report', 'booking_return', 'booking_signed_contracts',
            'brand', 'category', 'coupon', 'create_sms', 'currency',
            'customer-report', 'customer_group', 'customers-add', 'customers-delete', 'customers-edit',
            'customers-index', 'daily-purchase', 'daily-sale', 'dashboard', 'delivery',
            'department', 'developed_by', 'donations-add', 'donations-delete', 'donations-edit',
            'donations-index', 'donor-add', 'donor-delete', 'donor-edit', 'donor-index',
            'due-report', 'employees-add', 'employees-delete', 'employees-edit', 'employees-index',
            'empty_database', 'env_setting', 'events_module', 'expenses-add', 'expenses-delete',
            'expenses-edit', 'expenses-index', 'fixed_assets', 'fixed_assets_report', 'forward_letter',
            'general_setting', 'gift_card', 'holiday', 'hrm', 'hrm_setting',
            'invitations_module', 'letter_approve', 'letter_approve_index', 'letter_awaiting_edit', 'letter_category',
            'letter_create', 'letter_delete', 'letter_edit', 'letter_edited_index', 'letter_index',
            'letter_module', 'letter_rejected', 'letter_send', 'letter_send_index', 'letter_sign',
            'letter_sign_index', 'letter_template', 'mail_setting', 'money-transfer', 'monthly-purchase',
            'monthly-sale', 'multiple_batch', 'one_time_otp', 'orders-add', 'orders-delete',
            'orders-edit', 'orders-index', 'payment-report', 'payments-add', 'payments-delete',
            'payments-edit', 'payments-index', 'payroll', 'permissions_module', 'pos_setting',
            'price-change', 'print_barcode', 'product-qty-alert', 'product-report', 'products-add',
            'products-delete', 'products-edit', 'products-index', 'profit-loss', 'purchase-report',
            'purchase-return-add', 'purchase-return-delete', 'purchase-return-edit', 'purchase-return-index', 'purchases-add',
            'purchases-delete', 'purchases-edit', 'purchases-index', 'quotes-add', 'quotes-delete',
            'quotes-edit', 'quotes-index', 'region-add', 'region-delete', 'region-edit',
            'region-index', 'registration-fees-add', 'registration-fees-delete', 'returns-add', 'returns-delete',
            'returns-edit', 'returns-index', 'reward_point_setting', 'sale-report', 'sales-add',
            'sales-delete', 'sales-edit', 'sales-index', 'search_all_products', 'search_on_click',
            'send_notification', 'services-add', 'services-delete', 'services-edit', 'services-index',
            'shops-add', 'shops-delete', 'shops-edit', 'shops-index', 'sms_setting',
            'star_product', 'station-add', 'station-delete', 'station-edit', 'station-index',
            'stock_count', 'supplier-report', 'suppliers-add', 'suppliers-delete', 'suppliers-edit',
            'suppliers-index', 'tasks_module', 'tax', 'today_profit', 'today_sale',
            'transfers-add', 'transfers-delete', 'transfers-edit', 'transfers-index', 'unit',
            'user-report', 'users-add', 'users-delete', 'users-edit', 'users-index',
            'visits-add', 'warehouse', 'warehouse-report', 'warehouse-stock-report', 'zero_stock',
        ];
    }
}
