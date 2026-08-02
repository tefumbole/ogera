<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a user be asked to draw their own signature from a link, so an admin
 * creating an account for someone else does not have to obtain the image first.
 */
class AddSignatureRequestToUsers extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'signature_token')) {
                $table->string('signature_token', 64)->nullable()->unique()->after('approve');
            }
            if (! Schema::hasColumn('users', 'signature_requested_at')) {
                $table->timestamp('signature_requested_at')->nullable()->after('signature_token');
            }
            if (! Schema::hasColumn('users', 'signature_signed_at')) {
                $table->timestamp('signature_signed_at')->nullable()->after('signature_requested_at');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['signature_signed_at', 'signature_requested_at'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
            if (Schema::hasColumn('users', 'signature_token')) {
                $table->dropUnique(['signature_token']);
                $table->dropColumn('signature_token');
            }
        });
    }
}
