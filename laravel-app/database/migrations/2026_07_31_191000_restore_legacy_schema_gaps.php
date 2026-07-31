<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Several legacy migrations ship with their bodies commented out because the
 * original database already had the columns. Ogera was built from migrations
 * alone, so those columns never existed and the Orders, Services, Assets and
 * Vendor screens all threw "Unknown column" 500s.
 */
class RestoreLegacySchemaGaps extends Migration
{
    public function up()
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $this->addIfMissing($table, 'orders', 'is_service', function ($t) {
                    $t->tinyInteger('is_service')->default(0);
                });
                $this->addIfMissing($table, 'orders', 'is_approve', function ($t) {
                    $t->tinyInteger('is_approve')->default(1);
                });
                $this->addIfMissing($table, 'orders', 'vendor_id', function ($t) {
                    $t->integer('vendor_id')->default(1);
                });
                $this->addIfMissing($table, 'orders', 'payment_request', function ($t) {
                    $t->integer('payment_request')->default(0);
                });
                foreach ([
                    'customer_doc', 'result_doc', 'service_type', 'academic_year', 'spacing',
                    'subject', 'project_title', 'project_guide_lines', 'citation_sytle',
                    'font_style', 'language', 'sample_doc', 'citation_style',
                ] as $column) {
                    $this->addIfMissing($table, 'orders', $column, function ($t) use ($column) {
                        $t->string($column)->nullable();
                    });
                }
                foreach (['variant_id', 'number_of_pages', 'word_count', 'references'] as $column) {
                    $this->addIfMissing($table, 'orders', $column, function ($t) use ($column) {
                        $t->integer($column)->nullable();
                    });
                }
                foreach ([
                    'quality_double_checker', 'abstract_page', 'one_page_summary',
                    'grammar_checker', 'preferred_expert',
                ] as $column) {
                    $this->addIfMissing($table, 'orders', $column, function ($t) use ($column) {
                        $t->tinyInteger($column)->default(0);
                    });
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                foreach (['can_donation', 'can_service', 'can_booking'] as $column) {
                    $this->addIfMissing($table, 'users', $column, function ($t) use ($column) {
                        $t->tinyInteger($column)->default(0);
                    });
                }
                $this->addIfMissing($table, 'users', 'commission', function ($t) {
                    $t->integer('commission')->default(10);
                });
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $this->addIfMissing($table, 'products', 'vendor_id', function ($t) {
                    $t->integer('vendor_id')->default(1);
                });
            });
        }

        if (Schema::hasTable('asset_expenses')) {
            Schema::table('asset_expenses', function (Blueprint $table) {
                $this->addIfMissing($table, 'asset_expenses', 'type', function ($t) {
                    $t->string('type')->nullable();
                });
                $this->addIfMissing($table, 'asset_expenses', 'activity_type', function ($t) {
                    $t->string('activity_type')->nullable();
                });
            });
        }

        if (! Schema::hasTable('payment_requests')) {
            Schema::create('payment_requests', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('vendor_id');
                $table->integer('order_id');
                $table->integer('amount');
                $table->integer('status')->default(0);
                $table->integer('payed_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // Restoring baseline schema is not reversible.
    }

    private function addIfMissing(Blueprint $table, $tableName, $column, callable $definition)
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $definition($table);
        }
    }
}
