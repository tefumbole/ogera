<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChangeToPaymentsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('payments', 'change')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->double('change')->nullable()->after('amount');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('payments', 'change')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('change');
            });
        }
    }
}
