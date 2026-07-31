<?php

return [

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
