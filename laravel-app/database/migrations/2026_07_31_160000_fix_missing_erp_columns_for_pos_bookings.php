<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Production Ogera DB was missing several columns that POS, bookings,
 * products and payments always read/write. Also seed a Cash account when
 * accounts is empty so POS default debit/credit selects do not null-deref.
 */
class FixMissingErpColumnsForPosBookings extends Migration
{
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('accounts', 'is_default_debit')) {
                $table->boolean('is_default_debit')->nullable()->after('is_default');
            }
            if (! Schema::hasColumn('accounts', 'department_id')) {
                $table->unsignedInteger('department_id')->nullable()->after('account_no');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'location')) {
                $table->string('location')->nullable()->after('code');
            }
            if (! Schema::hasColumn('products', 'vendor_id')) {
                $table->unsignedInteger('vendor_id')->nullable()->default(1)->after('is_active');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'debit_sale_id')) {
                $table->unsignedInteger('debit_sale_id')->nullable()->after('sale_id');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'coupon_id')) {
                $table->unsignedInteger('coupon_id')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'coupon_discount')) {
                $table->double('coupon_discount')->nullable();
            }
        });

        // POS / bookings require at least one active default account.
        if (DB::table('accounts')->count() === 0) {
            DB::table('accounts')->insert([
                'account_no' => '1000',
                'name' => 'Cash',
                'initial_balance' => 0,
                'total_balance' => 0,
                'note' => 'Default account seeded for POS / payments',
                'is_default' => 1,
                'is_default_debit' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $hasDefault = DB::table('accounts')->where('is_default', 1)->exists();
            $hasDefaultDebit = DB::table('accounts')->where('is_default_debit', 1)->exists();
            $firstId = DB::table('accounts')->where('is_active', 1)->orderBy('id')->value('id')
                ?: DB::table('accounts')->orderBy('id')->value('id');

            if ($firstId && ! $hasDefault) {
                DB::table('accounts')->where('id', $firstId)->update(['is_default' => 1]);
            }
            if ($firstId && ! $hasDefaultDebit) {
                DB::table('accounts')->where('id', $firstId)->update(['is_default_debit' => 1]);
            }
        }
    }

    public function down()
    {
        // Non-destructive: keep columns; production may already rely on them.
    }
}
