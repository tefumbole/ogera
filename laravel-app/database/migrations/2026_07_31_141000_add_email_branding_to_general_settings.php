<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailBrandingToGeneralSettings extends Migration
{
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('general_settings', 'email_header')) {
                $table->string('email_header')->nullable();
            }
            if (! Schema::hasColumn('general_settings', 'email_footer')) {
                $table->string('email_footer')->nullable();
            }
            if (! Schema::hasColumn('general_settings', 'email_water_mark')) {
                $table->string('email_water_mark')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            foreach (['email_header', 'email_footer', 'email_water_mark'] as $col) {
                if (Schema::hasColumn('general_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
