<?php

namespace App\Http\Middleware;

use App\Support\DatabaseBackup;
use Closure;
use Illuminate\Support\Facades\Log;

/**
 * The host gives no cron access over SSH, so a due backup is taken once the
 * response has already been sent. Nothing here can affect the request itself.
 */
class RunDailyDatabaseBackup
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        if (! config('ogera.backup.auto', true)) {
            return;
        }

        try {
            $backup = app(DatabaseBackup::class);

            if (! $backup->isDue() || ! $backup->claimAttempt()) {
                return;
            }

            $archive = $backup->run();
            Log::info('Database backup written: ' . basename($archive));
        } catch (\Throwable $e) {
            Log::warning('Automatic database backup skipped: ' . $e->getMessage());
        }
    }
}
