<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class UpdateFromGit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updatefromgit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulls from git and does the appropriate updates and cleanup';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /* Step 1 - update code to latest from git */

        // todo: detect any changes and warn

        exec('git reset --hard && git pull');

        $this->info('Reset to latest git');

        /* Step 2 - align with latest composer file */

        exec('composer install --no-dev');

        $this->info('Composer up to date');

        /* Step 3 - post update tidyup */

        exec('composer dump-autoload'); // to pick up the helpers file if not already

        Artisan::call('config:cache'); // clear any existing cache and re-cache

        $this->info('Tidyup complete');
    }
}
