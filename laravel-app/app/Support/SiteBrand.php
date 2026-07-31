<?php

namespace App\Support;

use App\GeneralSetting;

class SiteBrand
{
    /**
     * Public URL for the logo uploaded in Settings → General.
     * Always prefers general_settings.site_logo when the file is present.
     */
    public static function logoUrl($generalSetting = null)
    {
        $setting = $generalSetting ?: GeneralSetting::latest()->first();
        if ($setting && ! empty($setting->site_logo)) {
            $filename = $setting->site_logo;
            foreach ([
                base_path('public/logo/'.$filename),
                public_path('logo/'.$filename),
            ] as $path) {
                if (is_file($path)) {
                    return url('public/logo/'.$filename);
                }
            }
        }

        foreach (['ogera-logo.png', 'ogera-logo.jpg'] as $fallback) {
            if (is_file(base_path('public/branding/'.$fallback))) {
                return url('public/branding/'.$fallback);
            }
        }

        // Last resort only — never preferred over General Settings.
        if (is_file(base_path('public/branding/beyond-logo.png'))) {
            return url('public/branding/beyond-logo.png');
        }

        return url('public/branding/ogera-logo.png');
    }

    public static function siteTitle($generalSetting = null)
    {
        $setting = $generalSetting ?: GeneralSetting::latest()->first();

        return ($setting && ! empty($setting->site_title))
            ? $setting->site_title
            : 'Ogera';
    }
}
