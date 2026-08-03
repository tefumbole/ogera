<?php

return [

    /*
     * When true, due Artisan schedule work runs after a web response. Needed
     * on hosts that cannot set cron over SSH. Turn off once hPanel cron runs
     * `php artisan schedule:run` every minute.
     */
    'scheduler' => [
        'auto' => (bool) env('OGERA_SCHEDULER_AUTO', true),

        /*
         * How late a scheduled message may be and still be worth delivering.
         *
         * Every sender picks up work with "due_at <= now()". With no lower
         * bound, the first run after cron has been down delivers the entire
         * backlog at once — customers get reminders for bookings that ended
         * months ago. Anything overdue by more than this many minutes is
         * retired instead of sent: it is marked as handled so it stops being
         * picked up, but no message goes out.
         *
         * The window still absorbs normal interruptions (a deploy, a reboot,
         * an hour of downtime) — that work is delivered as usual.
         */
        'grace_minutes' => (int) env('OGERA_SCHEDULE_GRACE_MINUTES', 360),
    ],

    'backup' => [

        /*
         * Where dumps are written. Keep this outside the application directory:
         * deploys rsync with --delete, so anything inside the app is removed.
         */
        'path' => env('OGERA_BACKUP_PATH', storage_path('app/backups')),

        /*
         * How many daily dumps to keep, i.e. how far back you can roll.
         */
        'keep' => (int) env('OGERA_BACKUP_KEEP', 30),

        /*
         * The host offers no cron over SSH, so a backup that is due is taken
         * after a web request has already been answered. Set to false once a
         * real cron entry runs `artisan ogera:db-backup`.
         */
        'auto' => (bool) env('OGERA_BACKUP_AUTO', true),

        /*
         * A backup becomes due this many hours after the last successful one.
         */
        'interval_hours' => (int) env('OGERA_BACKUP_INTERVAL_HOURS', 24),

        /*
         * Absolute path to mysqldump, in case it is not on PATH.
         */
        'mysqldump' => env('OGERA_MYSQLDUMP', '/usr/bin/mysqldump'),
    ],

];
