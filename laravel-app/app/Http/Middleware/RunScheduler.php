<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Hostinger does not expose crontab over SSH on this account, so due scheduled
 * work (reminders, announcements, etc.) is dispatched after a web response —
 * the same pattern as RunDailyDatabaseBackup. At most once per minute.
 *
 * Set OGERA_SCHEDULER_AUTO=false in .env once a real cron entry runs
 * `php artisan schedule:run` every minute.
 */
class RunScheduler
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        if (! config('ogera.scheduler.auto', true)) {
            return;
        }

        if ($request->is('public/*', 'css/*', 'js/*', 'vendor/*', 'storage/*')) {
            return;
        }

        try {
            // Cache::add is atomic enough on the file driver; Cache::lock is not.
            if (! Cache::add('ogera.scheduler.auto', 1, 1)) {
                return;
            }

            Artisan::call('schedule:run');
        } catch (\Throwable $e) {
            Log::warning('Automatic schedule:run skipped: ' . $e->getMessage());
        }
    }
}
