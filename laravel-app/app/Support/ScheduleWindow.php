<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Guards scheduled senders against flushing a backlog.
 *
 * Every scheduled sender in this app selects its work with "due_at <= now()".
 * That is correct while the scheduler runs every minute, but the moment cron
 * stops — a migration, a host problem, a site that had no cron entry yet —
 * the query keeps matching. When the scheduler comes back, one run delivers
 * everything that piled up, so customers receive reminders for bookings that
 * ended months ago.
 *
 * A sender should therefore:
 *   1. deliver only what is due inside the grace window (`between()`), and
 *   2. retire whatever is older (`stale()`), marking it handled without
 *      sending, so it stops being selected on every subsequent run.
 */
class ScheduleWindow
{
    /** Fallback when the config value is missing or nonsensical. */
    const DEFAULT_GRACE_MINUTES = 360;

    public static function graceMinutes()
    {
        $minutes = (int) config('ogera.scheduler.grace_minutes', self::DEFAULT_GRACE_MINUTES);

        return $minutes > 0 ? $minutes : self::DEFAULT_GRACE_MINUTES;
    }

    /** Work due before this moment is too old to send. */
    public static function cutoff()
    {
        return Carbon::now()->subMinutes(self::graceMinutes());
    }

    public static function isStale($dueAt)
    {
        if (empty($dueAt)) {
            return false;
        }

        try {
            return Carbon::parse($dueAt)->lt(self::cutoff());
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Constrain a query to work that is due but not yet stale.
     * Use for the pass that actually sends.
     */
    public static function due($query, $column)
    {
        return $query->where($column, '<=', Carbon::now())
            ->where($column, '>=', self::cutoff());
    }

    /**
     * Constrain a query to work that is too old to send.
     * Use for the pass that retires the backlog.
     */
    public static function stale($query, $column)
    {
        return $query->where($column, '<', self::cutoff());
    }

    /**
     * Record that a backlog was retired rather than delivered, so the reason
     * for the silence is discoverable later.
     */
    public static function logRetired($feature, $count, array $context = [])
    {
        if ($count <= 0) {
            return;
        }

        Log::info("Skipped {$count} stale {$feature} (overdue by more than " . self::graceMinutes() . ' minutes; not sent).', $context);
    }
}
