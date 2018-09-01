<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MakeSqliteBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'makesqlitebackup';

    /**
     * Create SQLite backup of MySQL database.
     *
     * This requires the mysqldump command from the mysql-client package and the sqlite3 package.
     *
     * @return mixed
     */
    public function handle()
    {
        $databasefile = database_path(env('DB_SQLITE_DATABASE', 'database.sqlite'));
        if (file_exists($databasefile)) {
            unlink($databasefile);
        }

        $process = new Process(sprintf('sh mysql2sqlite.sh -h %s -u %s -p%s %s | sqlite3 %s', env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), $databasefile));

        // $process->start();

        // foreach ($process as $type => $data) {
        //     if ($process::OUT === $type) {
        //         echo "\nRead from stdout: ".$data;
        //     } else { // $process::ERR === $type
        //         echo "\nRead from stderr: ".$data;
        //     }
        // }

        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // I have no idea why this says memory
        echo $process->getOutput();
    }
}
