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
     * Create SQLite backup of MySQL database. Too difficult to trigger the exception to include in code coverage
     *
     * This requires the mysqldump command from the mysql-client package and the sqlite3 package.
     *
     * @codeCoverageIgnore
     *
     * @return mixed
     */
    public function handle()
    {
        $databasefile = config('database.connections.sqlite.database');
        if (file_exists($databasefile)) {
            unlink($databasefile);
        }

        $process = new Process(sprintf('sh mysql2sqlite.sh -h %s -u %s -p%s %s | sqlite3 %s', config('database.connections.mysql.host'), config('database.connections.mysql.username'), config('database.connections.mysql.password'), config('database.connections.mysql.database'), $databasefile));

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
