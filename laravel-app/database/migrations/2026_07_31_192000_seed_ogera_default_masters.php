<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sales, quotations and bookings all require a warehouse, a biller, a customer
 * group, a category and a unit before a document can be created. Seed Ogera's
 * own defaults so a clean database is immediately usable.
 */
class SeedOgeraDefaultMasters extends Migration
{
    const PHONE = '+250786887936';
    const EMAIL = 'info@ogeragency.com';
    const ADDRESS = 'Mövenpick Hotel, KN 4 Avenue, Kigali';
    const CITY = 'Kigali';
    const COUNTRY = 'Rwanda';

    public function up()
    {
        $now = now();

        $warehouseId = $this->firstOrCreate('warehouses', ['name' => 'Ogera'], [
            'phone' => self::PHONE,
            'email' => self::EMAIL,
            'address' => self::ADDRESS,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $billerId = $this->firstOrCreate('billers', ['name' => 'Ogera'], [
            'company_name' => 'OGERA Agency',
            'email' => self::EMAIL,
            'phone_number' => self::PHONE,
            'address' => self::ADDRESS,
            'city' => self::CITY,
            'country' => self::COUNTRY,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->firstOrCreate('customer_groups', ['name' => 'General'], [
            'percentage' => '0',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $categoryId = $this->firstOrCreate('categories', ['name' => 'General'], [
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->firstOrCreate('categories', ['name' => 'SERVICES'], [
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->firstOrCreate('categories', ['name' => 'Equipment Rental'], [
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $unitId = $this->firstOrCreate('units', ['unit_code' => 'pc'], [
            'unit_name' => 'Piece',
            'operator' => '*',
            'operation_value' => 1,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->firstOrCreate('units', ['unit_code' => 'service'], [
            'unit_name' => 'Service',
            'operator' => '*',
            'operation_value' => 1,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->firstOrCreate('taxes', ['name' => 'VAT 18%'], [
            'rate' => 18,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->pointGeneralSettingsAtDefaults($warehouseId, $billerId, $categoryId, $unitId);
    }

    public function down()
    {
        // Seed data is left in place.
    }

    private function firstOrCreate($table, array $match, array $attributes)
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $existing = DB::table($table)->where($match)->value('id');
        if ($existing) {
            return $existing;
        }

        return DB::table($table)->insertGetId(array_merge($match, $attributes));
    }

    private function pointGeneralSettingsAtDefaults($warehouseId, $billerId, $categoryId, $unitId)
    {
        if (! Schema::hasTable('general_settings')) {
            return;
        }

        $settings = DB::table('general_settings')->first();
        if (! $settings) {
            return;
        }

        $updates = [];
        if ($warehouseId && ! DB::table('warehouses')->where('id', $settings->default_warehouse_id)->exists()) {
            $updates['default_warehouse_id'] = $warehouseId;
        }
        if ($billerId && ! DB::table('billers')->where('id', $settings->default_biller_id)->exists()) {
            $updates['default_biller_id'] = $billerId;
        }
        if ($categoryId && ! DB::table('categories')->where('id', $settings->category)->exists()) {
            $updates['category'] = $categoryId;
        }
        if ($unitId && ! DB::table('units')->where('id', $settings->unit)->exists()) {
            $updates['unit'] = $unitId;
        }

        if ($updates) {
            DB::table('general_settings')->where('id', $settings->id)->update($updates);
        }
    }
}
