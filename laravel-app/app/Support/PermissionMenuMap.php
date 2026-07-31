<?php

namespace App\Support;

/**
 * Maps every admin sidebar menu to the permissions that unlock it.
 *
 * Settings → Role Permission uses this to render one checkbox per menu header:
 * ticking a header grants (or revokes) every permission behind that menu.
 */
class PermissionMenuMap
{
    /** menu key => ['label' => string, 'permissions' => [name => label]] */
    public static function groups()
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'permissions' => ['dashboard' => 'Dashboard'],
            ],
            'site-content' => [
                'label' => 'Site Content & About Us Leaders',
                'permissions' => ['site_content' => 'Manage site menu, pages and leaders'],
            ],
            'product' => [
                'label' => 'Product',
                'permissions' => [
                    'products-index' => 'View', 'products-add' => 'Add', 'products-edit' => 'Edit',
                    'products-delete' => 'Delete', 'category' => 'Category', 'print_barcode' => 'Print Barcode',
                    'stock_count' => 'Stock Count', 'adjustment' => 'Adjustment',
                ],
            ],
            'purchase' => [
                'label' => 'Purchase',
                'permissions' => [
                    'purchases-index' => 'View', 'purchases-add' => 'Add',
                    'purchases-edit' => 'Edit', 'purchases-delete' => 'Delete',
                ],
            ],
            'sale' => [
                'label' => 'Sale',
                'permissions' => [
                    'sales-index' => 'View', 'sales-add' => 'Add', 'sales-edit' => 'Edit',
                    'sales-delete' => 'Delete', 'gift_card' => 'Gift Card', 'coupon' => 'Coupon',
                    'delivery' => 'Delivery',
                ],
            ],
            'booking' => [
                'label' => 'Rental Module',
                'permissions' => [
                    'booking_module' => 'Rental Module', 'booking_index' => 'View', 'booking_create' => 'Create',
                    'booking_edit' => 'Edit', 'booking_delete' => 'Delete', 'booking_report' => 'Report',
                    'booking_return' => 'Return', 'booking_pending_review' => 'Pending Review',
                    'booking_awaiting_signature' => 'Awaiting Signature', 'booking_signed_contracts' => 'Signed Contracts',
                    'booking_contract_approve' => 'Approve Contract', 'booking_goods_received' => 'Goods Received',
                ],
            ],
            'events' => [
                'label' => 'Events',
                'permissions' => [
                    'events_module' => 'Events Module', 'events.view' => 'View', 'events.create' => 'Create',
                    'events.update' => 'Update', 'events.delete' => 'Delete', 'events.approve' => 'Approve',
                    'events.manage_workforce' => 'Manage Workforce', 'events.manage_budget' => 'Manage Budget',
                    'events.change_status' => 'Change Status', 'events.manage_publication' => 'Manage Publication',
                    'events.publish' => 'Publish', 'events.unpublish' => 'Unpublish', 'events.settings' => 'Settings',
                    'event_workers.view' => 'View Workers', 'event_workers.create' => 'Create Workers',
                    'event_workers.update' => 'Update Workers', 'event_contracts.view' => 'View Contracts',
                    'event_contracts.create' => 'Create Contracts', 'event_contracts.send' => 'Send Contracts',
                    'event_contracts.approve' => 'Approve Contracts', 'event_reminders.view' => 'View Reminders',
                    'event_reminders.create' => 'Create Reminders', 'event_reminders.send' => 'Send Reminders',
                    'event_timesheets.view' => 'View Timesheets', 'event_timesheets.manage' => 'Manage Timesheets',
                    'event_timesheets.approve' => 'Approve Timesheets', 'event_payments.view' => 'View Payments',
                    'event_payments.create' => 'Create Payments', 'event_payments.approve' => 'Approve Payments',
                ],
            ],
            'invitations' => [
                'label' => 'Digital Invitations',
                'permissions' => [
                    'invitations_module' => 'Invitations Module', 'invitations.view' => 'View',
                    'invitations.create' => 'Create', 'invitations.edit' => 'Edit',
                    'invitations.delete' => 'Delete', 'invitations.check_in' => 'Check-in',
                ],
            ],
            'tasks' => [
                'label' => 'Task Manager',
                'permissions' => [
                    'tasks_module' => 'Task Module', 'tasks.view' => 'View', 'tasks.create' => 'Create',
                    'tasks.update' => 'Update', 'tasks.delete' => 'Delete', 'tasks.settings' => 'Settings',
                ],
            ],
            'jobs' => [
                'label' => 'Job Board',
                'permissions' => [
                    'jobs_module' => 'Job Board Module', 'jobs.view' => 'View', 'jobs.manage' => 'Manage',
                ],
            ],
            'contracts' => [
                'label' => 'Contracts',
                'permissions' => [
                    'contracts_module' => 'Contracts Module', 'contracts.dashboard' => 'Dashboard',
                    'contracts.view' => 'View', 'contracts.create' => 'Create', 'contracts.edit' => 'Edit',
                    'contracts.send' => 'Send', 'contracts.sign_admin' => 'Countersign', 'contracts.cancel' => 'Cancel',
                    'contracts.bulk' => 'Bulk Engagement', 'contracts.templates' => 'Templates',
                    'contracts.clauses' => 'Clauses', 'contracts.settings' => 'Settings', 'contracts.report' => 'Report',
                ],
            ],
            'permissions' => [
                'label' => 'Staff Permissions (Leave)',
                'permissions' => [
                    'permissions_module' => 'Permissions Module', 'permissions.view' => 'View',
                    'permissions.manage' => 'Manage',
                ],
            ],
            'announcements' => [
                'label' => 'Announcements',
                'permissions' => [
                    'announcements_module' => 'Announcements Module', 'announcements.view' => 'View',
                    'announcements.create' => 'Create', 'announcements.delete' => 'Delete',
                    'announcements.settings' => 'Settings', 'announcement_index' => 'List (legacy)',
                    'announcement_add' => 'Add (legacy)', 'announcement_edit' => 'Edit (legacy)',
                    'announcement_delete' => 'Delete (legacy)',
                ],
            ],
            'courses' => [
                'label' => 'Courses',
                'permissions' => [
                    'courses_module' => 'Courses Module', 'courses.view' => 'View', 'courses.create' => 'Create',
                    'courses.update' => 'Update', 'courses.delete' => 'Delete',
                ],
            ],
            'timesheets' => [
                'label' => 'TimeSheets',
                'permissions' => [
                    'timesheets_module' => 'TimeSheets Module', 'timesheets.employee' => 'Employee TimeSheet',
                    'timesheets.view' => 'View', 'timesheets.admin' => 'Admin', 'timesheets.manage' => 'Manage',
                ],
            ],
            'shop' => [
                'label' => 'Shops',
                'permissions' => [
                    'shops-index' => 'View', 'shops-add' => 'Add', 'shops-edit' => 'Edit', 'shops-delete' => 'Delete',
                ],
            ],
            'order' => [
                'label' => 'Online Order',
                'permissions' => [
                    'orders-index' => 'View', 'orders-add' => 'Add', 'orders-edit' => 'Edit', 'orders-delete' => 'Delete',
                ],
            ],
            'payments' => [
                'label' => 'Payments',
                'permissions' => [
                    'payments-index' => 'View', 'payments-add' => 'Add', 'payments-edit' => 'Edit',
                    'payments-delete' => 'Delete',
                ],
            ],
            'letter' => [
                'label' => 'Letters',
                'permissions' => [
                    'letter_module' => 'Letter Module', 'letter_index' => 'View', 'letter_create' => 'Create',
                    'letter_edit' => 'Edit', 'letter_delete' => 'Delete', 'letter_send' => 'Send',
                    'letter_send_index' => 'Sent List', 'letter_sign' => 'Sign', 'letter_sign_index' => 'Signature List',
                    'letter_approve' => 'Approve', 'letter_approve_index' => 'Approval List',
                    'letter_edited_index' => 'Edited List', 'letter_awaiting_edit' => 'Awaiting Edit',
                    'letter_rejected' => 'Rejected', 'letter_template' => 'Templates', 'letter_category' => 'Categories',
                    'forward_letter' => 'Forward',
                ],
            ],
            'expense' => [
                'label' => 'Expense',
                'permissions' => [
                    'expenses-index' => 'View', 'expenses-add' => 'Add', 'expenses-edit' => 'Edit',
                    'expenses-delete' => 'Delete',
                ],
            ],
            'quotation' => [
                'label' => 'Quotation',
                'permissions' => [
                    'quotes-index' => 'View', 'quotes-add' => 'Add', 'quotes-edit' => 'Edit', 'quotes-delete' => 'Delete',
                ],
            ],
            'assets' => [
                'label' => 'Fixed Assets',
                'permissions' => [
                    'fixed_assets' => 'Fixed Assets', 'asset-index' => 'View Assets', 'asset-add' => 'Add Asset',
                    'asset-edit' => 'Edit Asset', 'asset-delete' => 'Delete Asset', 'asset-sale' => 'Sell Asset',
                    'asset-transfer' => 'Transfer Asset', 'asset-disppose' => 'Dispose Asset',
                    'asset-type-index' => 'View Types', 'asset-type-add' => 'Add Type', 'asset-type-edit' => 'Edit Type',
                    'asset-type-delete' => 'Delete Type', 'asset-expense-index' => 'View Expenses',
                    'asset-expense-add' => 'Add Expense', 'asset-expense-edit' => 'Edit Expense',
                    'asset-expense-delete' => 'Delete Expense', 'fixed_assets_report' => 'Assets Report',
                ],
            ],
            'transfer' => [
                'label' => 'Transfer',
                'permissions' => [
                    'transfers-index' => 'View', 'transfers-add' => 'Add', 'transfers-edit' => 'Edit',
                    'transfers-delete' => 'Delete',
                ],
            ],
            'return' => [
                'label' => 'Return',
                'permissions' => [
                    'returns-index' => 'View Sale Return', 'returns-add' => 'Add Sale Return',
                    'returns-edit' => 'Edit Sale Return', 'returns-delete' => 'Delete Sale Return',
                    'purchase-return-index' => 'View Purchase Return', 'purchase-return-add' => 'Add Purchase Return',
                    'purchase-return-edit' => 'Edit Purchase Return', 'purchase-return-delete' => 'Delete Purchase Return',
                ],
            ],
            'account' => [
                'label' => 'Accounting',
                'permissions' => [
                    'account-index' => 'Accounts', 'balance-sheet' => 'Balance Sheet',
                    'account-statement' => 'Account Statement', 'money-transfer' => 'Money Transfer',
                    'JE-method' => 'Journal Entry',
                ],
            ],
            'hrm' => [
                'label' => 'HRM',
                'permissions' => [
                    'hrm' => 'HRM', 'department' => 'Department', 'attendance' => 'Attendance',
                    'payroll' => 'Payroll', 'holiday' => 'Holiday', 'hrm_setting' => 'HRM Setting',
                ],
            ],
            'people' => [
                'label' => 'People',
                'permissions' => [
                    'users-index' => 'View Users', 'users-add' => 'Add Users', 'users-edit' => 'Edit Users',
                    'users-delete' => 'Delete Users', 'customers-index' => 'View Customers',
                    'customers-add' => 'Add Customers', 'customers-edit' => 'Edit Customers',
                    'customers-delete' => 'Delete Customers', 'billers-index' => 'View Billers',
                    'billers-add' => 'Add Billers', 'billers-edit' => 'Edit Billers', 'billers-delete' => 'Delete Billers',
                    'suppliers-index' => 'View Suppliers', 'suppliers-add' => 'Add Suppliers',
                    'suppliers-edit' => 'Edit Suppliers', 'suppliers-delete' => 'Delete Suppliers',
                    'employees-index' => 'View Employees', 'employees-add' => 'Add Employees',
                    'employees-edit' => 'Edit Employees', 'employees-delete' => 'Delete Employees',
                ],
            ],
            'report' => [
                'label' => 'Reports',
                'permissions' => [
                    'profit-loss' => 'Profit & Loss', 'best-seller' => 'Best Seller', 'daily-sale' => 'Daily Sale',
                    'monthly-sale' => 'Monthly Sale', 'daily-purchase' => 'Daily Purchase',
                    'monthly-purchase' => 'Monthly Purchase', 'sale-report' => 'Sale Report',
                    'purchase-report' => 'Purchase Report', 'customer-report' => 'Customer Report',
                    'supplier-report' => 'Supplier Report', 'due-report' => 'Due Report', 'user-report' => 'User Report',
                    'warehouse-report' => 'Warehouse Report', 'warehouse-stock-report' => 'Warehouse Stock Report',
                    'product-report' => 'Product Report', 'product-qty-alert' => 'Product Quantity Alert',
                    'average-report' => 'Average Report',
                ],
            ],
            'setting' => [
                'label' => 'Settings',
                'permissions' => [
                    'role_permission' => 'Role Permission', 'send_notification' => 'Send Notification',
                    'warehouse' => 'Warehouse', 'customer_group' => 'Customer Group', 'brand' => 'Brand',
                    'unit' => 'Unit', 'currency' => 'Currency', 'tax' => 'Tax',
                    'backup_database' => 'Backup Database', 'empty_database' => 'Empty Database',
                    'general_setting' => 'General Setting', 'env_setting' => '.env Settings',
                    'mail_setting' => 'Mail Setting', 'sms_setting' => 'SMS Setting',
                    'reward_point_setting' => 'Reward Point Setting', 'pos_setting' => 'POS Settings',
                    'activity_logs' => 'Activity Logs',
                ],
            ],
        ];
    }

    /** Menus whose permissions the Admin role must not receive. */
    public static function restrictedGroupKeys()
    {
        return ['setting', 'site-content'];
    }

    /** Every permission name referenced by any menu. */
    public static function allPermissions()
    {
        $names = [];
        foreach (self::groups() as $group) {
            foreach (array_keys($group['permissions']) as $name) {
                $names[$name] = true;
            }
        }

        return array_keys($names);
    }

    /** Permissions belonging to the Settings and Site Content menus. */
    public static function restrictedPermissions()
    {
        $groups = self::groups();
        $names = [];
        foreach (self::restrictedGroupKeys() as $key) {
            if (isset($groups[$key])) {
                foreach (array_keys($groups[$key]['permissions']) as $name) {
                    $names[$name] = true;
                }
            }
        }

        return array_keys($names);
    }

    /**
     * Module permissions that had no checkbox on the Role Permission screen and
     * are therefore rendered — and saved — generically.
     */
    public static function modulePermissions()
    {
        $groups = self::groups();
        $keys = ['tasks', 'contracts', 'announcements', 'courses', 'timesheets', 'jobs', 'permissions'];
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = [
                'label' => $groups[$key]['label'],
                'permissions' => $groups[$key]['permissions'],
            ];
        }

        // Legacy announcement_* checkboxes already exist further down the page.
        foreach (['announcement_index', 'announcement_add', 'announcement_edit', 'announcement_delete'] as $legacy) {
            unset($out['announcements']['permissions'][$legacy]);
        }

        return $out;
    }

    /** Names saved through the generic loop in RoleController@setPermission. */
    public static function genericallySavedPermissions()
    {
        $names = ['role_permission', 'site_content', 'activity_logs'];
        foreach (self::modulePermissions() as $group) {
            foreach (array_keys($group['permissions']) as $name) {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }
}
