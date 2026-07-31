<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommissionToGeneralSettings extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('general_settings', 'commission')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->integer('commission')->default(0);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('general_settings', 'commission')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->dropColumn('commission');
            });
        }
    }
}
