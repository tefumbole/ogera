<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Contract templates, announcement defaults and contract settings were seeded
 * with the forked project's name. Rewrites them to the configured site title so
 * client-facing documents and messages match Settings → General.
 */
class ReplaceLegacyBrandNameInStoredContent extends Migration
{
    const LEGACY_NAME = 'Beyond Enterprise';

    public function up()
    {
        $siteTitle = $this->siteTitle();
        if ($siteTitle === '' || $siteTitle === self::LEGACY_NAME) {
            return;
        }

        foreach ($this->targets() as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }
                DB::table($table)
                    ->where($column, 'like', '%'.self::LEGACY_NAME.'%')
                    ->update([$column => DB::raw(
                        "REPLACE(`{$column}`, ".DB::getPdo()->quote(self::LEGACY_NAME).', '.DB::getPdo()->quote($siteTitle).')'
                    )]);
            }
        }
    }

    public function down()
    {
        // Rebranding stored content is not reversible.
    }

    private function siteTitle()
    {
        if (! Schema::hasTable('general_settings')) {
            return '';
        }

        return (string) DB::table('general_settings')->orderByDesc('id')->value('site_title');
    }

    private function targets()
    {
        return [
            'contract_settings' => ['value'],
            'contract_types' => ['default_party_a_label', 'default_party_b_label'],
            'contract_template_versions' => ['content_html'],
            'contract_clauses' => ['body_html'],
            'event_contract_templates' => ['header', 'body', 'footer'],
            'announcement_settings' => ['company_name', 'default_header', 'default_footer'],
            'wa_announcement_settings' => ['company_name', 'default_header', 'default_footer'],
        ];
    }
}
