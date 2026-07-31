<?php

namespace App\Support;

use App\GeneralSetting;

class SiteBrand
{
    /**
     * Public URL for the logo uploaded in Settings → General.
     * Always prefers general_settings.site_logo when set.
     */
    public static function logoUrl($generalSetting = null)
    {
        $setting = $generalSetting ?: GeneralSetting::latest()->first();
        if ($setting && ! empty($setting->site_logo)) {
            // Prefer the General Settings upload even if a local is_file()
            // check fails (shared-hosting path quirks). The web path is what
            // the browser needs.
            return url('public/logo/'.$setting->site_logo);
        }

        foreach (['ogera-logo.png', 'ogera-logo.jpg', 'beyond-logo.png'] as $fallback) {
            $path = base_path('public/branding/'.$fallback);
            if (is_file($path)) {
                return url('public/branding/'.$fallback);
            }
        }

        return url('public/branding/beyond-logo.png');
    }

    public static function siteTitle($generalSetting = null)
    {
        $setting = $generalSetting ?: GeneralSetting::latest()->first();

        return ($setting && ! empty($setting->site_title))
            ? $setting->site_title
            : 'Ogera';
    }
}
