<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToProductSales extends Migration
{
    public function up()
    {
        Schema::table('product_sales', function (Blueprint $table) {
            if (! Schema::hasColumn('product_sales', 'category_id')) {
                $table->integer('category_id')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('product_sales', 'multi_product_batch_id')) {
                // Stores JSON-encoded batch id lists from SaleController.
                $table->text('multi_product_batch_id')->nullable()->after('product_batch_id');
            }
            if (! Schema::hasColumn('product_sales', 'multi_product_batch_qty')) {
                $table->text('multi_product_batch_qty')->nullable()->after('multi_product_batch_id');
            }
        });
    }

    public function down()
    {
        Schema::table('product_sales', function (Blueprint $table) {
            foreach (['multi_product_batch_qty', 'multi_product_batch_id', 'category_id'] as $col) {
                if (Schema::hasColumn('product_sales', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
