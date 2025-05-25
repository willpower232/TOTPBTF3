<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class UpdateFromGit extends Command
{
    protected $signature = 'updatefromgit';

    protected $description = 'Pulls from git and does the appropriate updates and cleanup';

    /**
     * @codeCoverageIgnore
     */
    public function handle(): int
    {
        /* Step 1 - update code to latest from git */

        exec(command: 'git diff --quiet && git diff --cached --quiet', result_code: $exitCode);

        if ($exitCode !== 0 && ! confirm('git diff is not empty, okay to reset?')) {
            error('Aborting');
            return Command::FAILURE;
        }

        exec('git reset --hard && git pull');

        info('Reset to latest git');

        /* Step 2 - align with latest composer file */

        exec('composer install --no-dev');

        info('Composer up to date');

        /* Step 3 - post update tidyup */

        exec('composer dump-autoload'); // to pick up the helpers file if not already

        $this->call('config:cache'); // re-cache the config to include the latest commit sha

        info('Tidyup complete');

        return Command::SUCCESS;
    }
}
