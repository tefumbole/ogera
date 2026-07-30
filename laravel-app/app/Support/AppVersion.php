<?php

namespace App\Support;

use App\GeneralSetting;
use Illuminate\Support\Facades\Schema;

class AppVersion
{
    public static function label()
    {
        $path = base_path('VERSION');
        if (is_file($path)) {
            $fromFile = trim((string) file_get_contents($path));
            if ($fromFile !== '') {
                return self::normalizeSemver($fromFile);
            }
        }

        $configured = config('app.version');
        if (! empty($configured)) {
            return self::normalizeSemver($configured);
        }

        return '1.0.1';
    }

    /**
     * Canonical ERP display: OGERA_ERP_V1.0.1
     * Bump scheme: patch 1–9 → next minor (1.0.9 → 1.1.0);
     *              minor 0–9 → next major (1.9.9 → 2.0.1).
     */
    public static function erp()
    {
        return 'OGERA_ERP_V'.self::label();
    }

    /**
     * Same as erp() — login/portals and settings stay consistent.
     */
    public static function bcl()
    {
        return self::erp();
    }

    public static function build()
    {
        $build = config('app.version_build');
        if (! empty($build)) {
            return $build;
        }

        if (! is_dir(base_path('.git'))) {
            return null;
        }

        $sha = @trim((string) @shell_exec('git -C '.escapeshellarg(base_path()).' rev-parse --short HEAD 2>/dev/null'));

        return $sha !== '' ? $sha : null;
    }

    public static function display()
    {
        return self::erp();
    }

    /**
     * Persist laravel-app/VERSION into general_settings.app_version (after each deploy/push).
     *
     * @return string The ERP version string written
     */
    public static function syncToSettings()
    {
        $version = self::erp();

        try {
            if (! Schema::hasTable('general_settings') || ! Schema::hasColumn('general_settings', 'app_version')) {
                return $version;
            }
        } catch (\Throwable $e) {
            return $version;
        }

        $row = GeneralSetting::query()->orderByDesc('id')->first();
        if ($row && (string) $row->app_version !== $version) {
            $row->app_version = $version;
            $row->save();
        }

        return $version;
    }

    protected static function normalizeSemver($value)
    {
        $value = trim((string) $value);
        $value = preg_replace('/^OGERA_ERP_V\.?/i', '', $value);
        $value = preg_replace('/^BCL_ERP_V\.?/i', '', $value);
        $value = preg_replace('/^ABT_ERP_V\.?/i', '', $value);
        $value = preg_replace('/^BCL\s*V\.?\s*/i', '', $value);
        $value = ltrim($value, 'vV');

        return $value !== '' ? $value : '1.0.1';
    }
}
