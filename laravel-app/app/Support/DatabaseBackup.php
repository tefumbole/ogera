<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Compressed mysqldump of Ogera's own database, with day-based rotation.
 *
 * shell_exec/exec are disabled on the host, but proc_open (and therefore
 * Symfony's Process) is allowed, so dumps run the same way from the CLI and
 * from a web request.
 */
class DatabaseBackup
{
    const PREFIX = 'ogera-';
    const SUFFIX = '.sql.gz';
    const SUCCESS_MARKER = '.last-success';
    const ATTEMPT_MARKER = '.last-attempt';
    const LOCK = '.lock';

    /**
     * Take a backup and prune anything beyond the retention window.
     *
     * @return string Absolute path of the archive that was written.
     */
    public function run()
    {
        $connection = $this->connection();
        $this->assertOwnDatabase($connection['database']);

        $directory = $this->directory();
        $lock = $directory . '/' . self::LOCK;

        if (! $this->acquireLock($lock)) {
            throw new RuntimeException('Another backup is already running.');
        }

        try {
            $target = $directory . '/' . self::PREFIX . date('Ymd-His') . self::SUFFIX;
            $plain = $target . '.sql.partial';
            $partial = $target . '.partial';

            try {
                $this->dump($connection, $plain);
                $this->assertComplete($plain);
                $this->compress($plain, $partial);
            } finally {
                @unlink($plain);
            }

            $this->assertUsable($partial);
            rename($partial, $target);
            @chmod($target, 0600);

            $this->prune();
            touch($directory . '/' . self::SUCCESS_MARKER);

            return $target;
        } finally {
            @rmdir($lock);
        }
    }

    /**
     * Newest first.
     *
     * @return array
     */
    public function archives()
    {
        $files = glob($this->directory() . '/' . self::PREFIX . '*' . self::SUFFIX) ?: [];
        rsort($files);

        return $files;
    }

    public function lastSuccessAt()
    {
        $marker = $this->directory() . '/' . self::SUCCESS_MARKER;

        return is_file($marker) ? filemtime($marker) : null;
    }

    public function isDue()
    {
        $last = $this->lastSuccessAt();
        if ($last === null) {
            return true;
        }

        $hours = max(1, (int) config('ogera.backup.interval_hours', 24));

        return $last <= time() - ($hours * 3600);
    }

    /**
     * Records that an attempt is being made, so a failing backup cannot be
     * retried on every single request.
     *
     * @return bool True when this caller should proceed.
     */
    public function claimAttempt()
    {
        $marker = $this->directory() . '/' . self::ATTEMPT_MARKER;
        if (is_file($marker) && filemtime($marker) > time() - 3600) {
            return false;
        }

        return touch($marker);
    }

    public function directory()
    {
        $path = (string) config('ogera.backup.path');
        if ($path === '') {
            $path = storage_path('app/backups');
        }

        if (! is_dir($path) && ! @mkdir($path, 0700, true) && ! is_dir($path)) {
            throw new RuntimeException('Backup directory is not writable: ' . $path);
        }

        return rtrim($path, '/');
    }

    private function connection()
    {
        $config = config('database.connections.' . config('database.default'));

        if (empty($config['database'])) {
            throw new RuntimeException('No database configured.');
        }

        return $config;
    }

    /**
     * This account also hosts unrelated sites; never dump another project's data.
     */
    private function assertOwnDatabase($database)
    {
        foreach (['beyondtech', 'alphabridge', 'alpha_bridge', 'beyondcompany'] as $foreign) {
            if (stripos($database, $foreign) !== false) {
                throw new RuntimeException("Refusing to back up '{$database}': it belongs to another project.");
            }
        }

        if (stripos($database, 'ogera') === false) {
            throw new RuntimeException("Refusing to back up '{$database}': not an Ogera database.");
        }
    }

    private function acquireLock($lock)
    {
        if (@mkdir($lock, 0700)) {
            return true;
        }

        // A lock left behind by a killed process would otherwise block forever.
        if (is_dir($lock) && filemtime($lock) < time() - 3600) {
            @rmdir($lock);

            return (bool) @mkdir($lock, 0700);
        }

        return false;
    }

    private function dump(array $connection, $target)
    {
        $binary = (string) config('ogera.backup.mysqldump', 'mysqldump');
        if (! is_executable($binary)) {
            $binary = 'mysqldump';
        }

        $host = (string) ($connection['host'] ?: 'localhost');

        $base = [
            $binary,
            '--host=' . $host,
            '--user=' . (string) $connection['username'],
            '--single-transaction',
            '--quick',
            '--no-tablespaces',
            '--default-character-set=utf8mb4',
            '--result-file=' . $target,
        ];

        // Passing a port alongside "localhost" makes the client use TCP, where
        // the grant does not apply; the socket is the only route that works.
        if ($host !== 'localhost' && ! empty($connection['port'])) {
            $base[] = '--port=' . (string) $connection['port'];
        }

        // Stored routines need privileges a shared-hosting user may not have,
        // so fall back to a plain dump rather than losing the backup entirely.
        $error = 'mysqldump did not run';
        foreach ([['--routines', '--triggers'], []] as $extra) {
            $arguments = array_merge($base, $extra, [(string) $connection['database']]);

            // The password goes through the environment so it stays out of the
            // process list.
            $process = new Process($arguments, base_path(), ['MYSQL_PWD' => (string) $connection['password']], null, 600);
            $process->run();

            if ($process->isSuccessful()) {
                return;
            }

            $error = trim($process->getErrorOutput()) ?: 'mysqldump exited with ' . $process->getExitCode();
            @unlink($target);
        }

        throw new RuntimeException('mysqldump failed: ' . $error);
    }

    /**
     * mysqldump writes its own file, so the dump is checked before it is
     * compressed and before anything older is rotated away.
     */
    private function assertComplete($dump)
    {
        if (! is_file($dump) || filesize($dump) < 1024) {
            throw new RuntimeException('Dump is empty.');
        }

        $handle = fopen($dump, 'rb');
        fseek($handle, -200, SEEK_END);
        $tail = (string) fread($handle, 200);
        fclose($handle);

        if (strpos($tail, 'Dump completed') === false) {
            throw new RuntimeException('Dump is incomplete: mysqldump did not finish.');
        }
    }

    private function compress($dump, $archive)
    {
        $in = fopen($dump, 'rb');
        $out = gzopen($archive, 'wb9');

        if (! $in || ! $out) {
            throw new RuntimeException('Could not compress the dump.');
        }

        while (! feof($in)) {
            $chunk = fread($in, 262144);
            if ($chunk === false || gzwrite($out, $chunk) === false) {
                fclose($in);
                gzclose($out);
                @unlink($archive);
                throw new RuntimeException('Could not compress the dump.');
            }
        }

        fclose($in);
        gzclose($out);
    }

    /**
     * A truncated dump is more dangerous than no dump, because it would rotate
     * a good one out.
     */
    private function assertUsable($archive)
    {
        if (! is_file($archive) || filesize($archive) < 1024) {
            @unlink($archive);
            throw new RuntimeException('Backup archive is empty.');
        }

        $handle = @gzopen($archive, 'rb');
        if (! $handle) {
            @unlink($archive);
            throw new RuntimeException('Backup archive could not be read.');
        }

        $tail = '';
        while (! gzeof($handle)) {
            $chunk = gzread($handle, 65536);
            if ($chunk === false) {
                gzclose($handle);
                @unlink($archive);
                throw new RuntimeException('Backup archive is corrupt.');
            }
            $tail = substr($tail . $chunk, -400);
        }
        gzclose($handle);

        if (strpos($tail, 'Dump completed') === false) {
            @unlink($archive);
            throw new RuntimeException('Backup is incomplete: mysqldump did not finish.');
        }
    }

    private function prune()
    {
        $keep = max(1, (int) config('ogera.backup.keep', 30));

        foreach (array_slice($this->archives(), $keep) as $stale) {
            if (@unlink($stale)) {
                Log::info('Pruned old database backup: ' . basename($stale));
            }
        }
    }
}
