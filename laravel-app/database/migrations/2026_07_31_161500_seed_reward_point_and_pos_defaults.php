<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedRewardPointAndPosDefaults extends Migration
{
    public function up()
    {
        if (Schema::hasTable('reward_point_settings') && DB::table('reward_point_settings')->count() === 0) {
            DB::table('reward_point_settings')->insert([
                'per_point_amount' => 0,
                'minimum_amount' => 0,
                'duration' => 1,
                'type' => 'Year',
                'is_active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('pos_setting') && DB::table('pos_setting')->count() === 0) {
            $customerId = DB::table('customers')->where('is_active', 1)->orderBy('id')->value('id');
            $warehouseId = DB::table('warehouses')->where('is_active', 1)->orderBy('id')->value('id');
            $billerId = DB::table('billers')->where('is_active', 1)->orderBy('id')->value('id');

            DB::table('pos_setting')->insert([
                'id' => 1,
                'customer_id' => $customerId ?: 0,
                'warehouse_id' => $warehouseId ?: 0,
                'biller_id' => $billerId ?: 0,
                'product_number' => 20,
                'keybord_active' => 0,
                'stripe_public_key' => '',
                'stripe_secret_key' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        //
    }
}
