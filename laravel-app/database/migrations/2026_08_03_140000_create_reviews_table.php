<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    public function up()
    {
        // The legacy Stock Manager already owns a "reviews" table (product
        // ratings). Ours are site-wide client reviews, so they live in
        // "site_reviews" to avoid the collision.
        if (Schema::hasTable('site_reviews')) {
            return;
        }

        Schema::create('site_reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('country', 100)->nullable();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('message');
            // Which customer, booking, sale, quotation or letter led to this
            // review. Everything except rating and message is optional, so a
            // submission from an anonymous public visitor is still valid.
            $table->unsignedInteger('customer_id')->nullable()->index();
            $table->string('source', 40)->nullable();
            $table->string('reference', 100)->nullable();
            $table->text('admin_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            // Under the auto-publish rating, reviews wait for admin approval
            // before appearing on the public page.
            $table->boolean('is_public')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_reviews');
    }
}
