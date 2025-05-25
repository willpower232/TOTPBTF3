<?php

namespace App\Console\Commands\Csv;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class Export extends Command
{
    protected $signature = 'csv:export {destination? : the file to output to}';

    protected $description = 'Export user tokens as CSV';

    /**
     * Authorise user and export token secrets
     */
    public function handle(): int
    {
        $email = $this->ask('Users email address?');

        $password = $this->secret('Password for user?');

        $user = compact('email', 'password');

        $validator = Validator::make($user, User::getValidationRules('login'));

        if ($validator->fails()) {
            $this->info('Unable to login');
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        if (! auth()->guard()->validate($user)) {
            $this->info('Unable to login');
            $this->error('Unable to match details');
            return self::FAILURE;
        }

        $user = User::where('email', $email)
            ->with('tokens')
            ->firstOrFail();

        $user->putEncryptionKeyInSession($password);

        $cache = '';

        // @codeCoverageIgnoreStart
        if (($line = $this->makeCSVLine(['path', 'title', 'secret'])) === false) {
            $this->info('Unable to build export');
            return self::FAILURE;
        }
        // @codeCoverageIgnoreEnd

        $cache .= $line . PHP_EOL;

        foreach ($user->tokens as $token) {
            $secret = $token->getDecryptedSecret();

            // @codeCoverageIgnoreStart
            if (($line = $this->makeCSVLine([$token->path, $token->title, $secret])) === false) {
                $this->info('Problem building export');
                return self::FAILURE;
            }
            // @codeCoverageIgnoreEnd

            $cache .= $line . PHP_EOL;
        }

        $filename = $this->argument('destination') ?? 'output.csv';

        $this->info("Writing to {$filename}");

        file_put_contents($filename, $cache);

        $this->info('Done.');

        return self::SUCCESS;
    }

    /**
     * Converts an array to a line of CSV-formatted data and returns
     *
     * @param array<?string> $fields list of fields
     *
     * @return bool|string CSV-formatted string or false on failure
     *
     * @codeCoverageIgnore
     */
    private function makeCSVLine(array $fields): bool|string
    {
        $f = fopen('php://memory', 'r+');

        if ($f === false) {
            return false;
        }

        if (fputcsv($f, $fields) === false) {
            return false;
        }
        rewind($f);
        $csv_line = stream_get_contents($f);

        if ($csv_line === false) {
            return false;
        }

        return rtrim($csv_line);
    }
}
