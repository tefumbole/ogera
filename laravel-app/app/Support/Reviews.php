<?php

namespace App\Support;

use App\SiteReview;
use App\SiteSetting;

/**
 * Central switch for the customer reviews feature.
 *
 * All admin-configurable behaviour lives here so that WhatsApp, PDF and email
 * senders can drop a "leave a review" nudge in without knowing the storage
 * details. Settings are persisted through App\SiteSetting under the
 * "reviews." prefix so they survive deploys.
 */
class Reviews
{
    /** Reviews under this many stars wait for admin approval before going live. */
    const DEFAULT_HOLD_BELOW = 3;

    /** Master switch: is the whole reviews feature on? */
    public static function isEnabled()
    {
        $val = SiteSetting::getValue('reviews.enabled', '1');

        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Should outbound WhatsApp / email / PDF messages append a "review us" line?
     * Kept separate from isEnabled so an admin can accept reviews without
     * necessarily nagging every customer for one.
     */
    public static function outboundCtaEnabled()
    {
        if (! self::isEnabled()) {
            return false;
        }
        $val = SiteSetting::getValue('reviews.outbound_cta', '1');

        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    /** Ratings strictly below this stay pending until an admin publishes them. */
    public static function holdBelow()
    {
        $val = (int) SiteSetting::getValue('reviews.hold_below', self::DEFAULT_HOLD_BELOW);
        if ($val < 1) {
            $val = 1;
        }
        if ($val > 5) {
            $val = 5;
        }

        return $val;
    }

    /** True when a review of this rating should be published immediately. */
    public static function shouldAutoPublish($rating)
    {
        return (int) $rating >= self::holdBelow();
    }

    /** Public reviews page URL, with optional prefill for the form. */
    public static function publicUrl(array $params = [])
    {
        $url = url('reviews');
        $params = array_filter($params, function ($v) {
            return $v !== null && $v !== '';
        });
        if (! empty($params)) {
            $url .= '#form?'.http_build_query($params);
        }

        return $url;
    }

    /**
     * A short WhatsApp footer nudge. Returns '' when disabled so callers can
     * concatenate unconditionally.
     */
    public static function whatsappFooter()
    {
        if (! self::outboundCtaEnabled()) {
            return '';
        }

        return "\n\n⭐ *Loved our service?* Share a quick review:\n".self::publicUrl();
    }

    /** HTML block for emails and rendered documents. Returns '' when disabled. */
    public static function htmlBlock()
    {
        if (! self::outboundCtaEnabled()) {
            return '';
        }
        $url = e(self::publicUrl());

        return '<div style="margin-top:20px;padding:14px 18px;border:1px solid #e0d3a5;'
            .'background:#fdf7e6;border-radius:8px;font-size:13px;color:#4a3a10;">'
            .'<strong>Enjoyed working with us?</strong> Tell us in 30 seconds and '
            .'help others choose confidently. '
            .'<a href="'.$url.'" style="color:#8a6d1c;font-weight:600;text-decoration:underline;">Leave a review</a>.'
            .'</div>';
    }

    /** Store a scalar setting. */
    public static function setSetting($key, $value)
    {
        SiteSetting::setValue('reviews.'.$key, $value);
    }

    /** How many pending reviews are waiting for moderation. */
    public static function pendingCount()
    {
        try {
            return SiteReview::pending()->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
