<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Clear stale BeyondTech Site Content overrides so admin + public pages
 * fall back to the current OGERA schema defaults.
 */
class ResetSiteContentToOgeraDefaults extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        DB::table('site_settings')
            ->where('key', 'like', 'content.%')
            ->where(function ($q) {
                $q->where('value', 'like', '%Beyond Enterprise%')
                    ->orWhere('value', 'like', '%BeyondTech%')
                    ->orWhere('value', 'like', '%beyondtech%')
                    ->orWhere('value', 'like', '%Technology Bridge%')
                    ->orWhere('value', 'like', '%IT Consultancy%')
                    ->orWhere('value', 'like', '%Norrsken%')
                    ->orWhere('value', 'like', '%Nasrah%');
            })
            ->delete();
    }

    public function down()
    {
        //
    }
}
