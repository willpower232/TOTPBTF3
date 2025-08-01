<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MakeSqliteBackup extends Command
{
    protected $signature = 'makesqlitebackup';

    /**
     * Create SQLite backup of MySQL database.
     *
     * This requires the mysqldump command from the mysql-client package and the sqlite3 package.
     *
     * @codeCoverageIgnore
     */
    public function handle(): int
    {
        $databasefile = config()->string('database.connections.sqlite.database');

        // replacing the file would upset users if they weren't using change management
        // so create a new file first
        $newdatabasefile = substr_replace($databasefile, '_new.sqlite', -7);

        $process = Process::fromShellCommandline(sprintf(
            'sh mysql2sqlite.sh -h %s -u %s -p%s %s | sqlite3 %s',
            config()->string('database.connections.mysql.host'),
            config()->string('database.connections.mysql.username'),
            config()->string('database.connections.mysql.password'),
            config()->string('database.connections.mysql.database'),
            $newdatabasefile
        ));

        $process->run();

        // since the command pipes the output to sqlite3,
        // we will only get a non-zero exit code if sqlite3 fails,
        // i.e. the path to the file is wrong or the sqlite is badly generated
        // we will not get a non-zero exit code if mysqldump fails!

        // in order to detect any failed part of the process, we have to detect the presence of error text
        $erroroutput = $process->getErrorOutput();

        if (strlen($erroroutput) > 0) {
            // if the command succeeded enough to write a new database file
            // we should remove it before continuing to avoid further confusion
            if (file_exists($newdatabasefile)) {
                unlink($newdatabasefile);
            }
            $this->error(trim($erroroutput));
            return 1;
        }

        // todo: determine if its possible for the command to succeed and generate an empty file

        // this classic Symfony Process code is less useful than the above
        // if (! $process->isSuccessful()) {
        //     throw new ProcessFailedException($process);
        // }

        rename($newdatabasefile, $databasefile);
        $this->info('Database download and conversion successful');
        return self::SUCCESS;

        // I still have no idea why this says memory
        // echo $process->getOutput();
    }
}
