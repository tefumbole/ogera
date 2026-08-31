<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Shared Wasender send pacing. Controller texts/documents and
 * BeyondWasenderService texts must share one clock or account-protection
 * rate limits kill PDF attaches that follow stakeholder texts.
 */
class WhatsAppThrottle
{
    const CACHE_KEY = 'whatsapp.last_send_at';

    public static function wait()
    {
        $intervalMs = max(1000, (int) config('services.whatsapp.min_send_interval_ms', 6000));
        $interval = $intervalMs / 1000;
        $last = (float) Cache::get(self::CACHE_KEY, 0);
        $now = microtime(true);

        if ($last > 0) {
            $wait = $interval - ($now - $last);
            if ($wait > 0) {
                usleep((int) round($wait * 1000000));
            }
        }

        Cache::put(self::CACHE_KEY, microtime(true), 300);
    }

    /**
     * Extra pause after a text before a document (Wasender is stricter there).
     */
    public static function waitForDocumentAfterText($lastType)
    {
        if ($lastType !== 'text') {
            return;
        }
        $delayMs = max(0, (int) config('services.whatsapp.text_to_document_delay_ms', 6000));
        if ($delayMs > 0) {
            usleep($delayMs * 1000);
            Cache::put(self::CACHE_KEY, microtime(true), 300);
        }
    }
}
