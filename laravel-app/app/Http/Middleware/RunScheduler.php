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
            // 60s matches cron's once-a-minute cadence: without it every request
            // spawns a full schedule:run (~10 subprocesses) and starves PHP-FPM.
            if (! Cache::add('ogera.scheduler.auto', 1, 60)) {
                return;
            }

            Artisan::call('schedule:run');

            // Heartbeat so "is the scheduler alive?" is answerable without guessing.
            Cache::put('ogera.scheduler.last_run', time(), 86400);
        } catch (\Throwable $e) {
            Log::warning('Automatic schedule:run skipped: ' . $e->getMessage());
        }
    }
}
