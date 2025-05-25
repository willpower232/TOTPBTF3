<?php

namespace App\Console\Commands\Csv;

use App\Models\Token;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Import extends Command
{
    protected $signature = 'csv:import {source? : the file to read from}';

    protected $description = 'Import user tokens as CSV';

    /**
     * Authorise user and import token secrets
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

        // read the entire file into an array
        $file = file($this->argument('source') ?? 'output.csv');

        // @codeCoverageIgnoreStart
        if ($file === false) {
            throw new \RuntimeException('nothing to import');
        }
        // @codeCoverageIgnoreEnd

        $csv = array_map('str_getcsv', $file);

        // now remove the column headers
        /** @var array<string> $headers */
        $headers = array_shift($csv);

        // the first row of the CSV is the keys for the array
        array_walk($csv, function (&$row) use ($headers) {
            $row = array_combine($headers, $row);
        });

        DB::transaction(function () use ($csv, $user) {
            /** @var array<array<string,string>> $csv */
            foreach ($csv as $newToken) {
                $token = new Token([
                    'user_id' => $user->id,
                    'path' => $newToken['path'],
                    'title' => $newToken['title'],
                ]);

                $token->setSecret($newToken['secret']);

                try {
                    $test = $token->getTOTPCode();
                } catch (\Exception $e) {
                    throw new \RuntimeException('Problem with secret for ' . $newToken['path'], $e->getCode(), $e);
                }

                $token->save();
            }
        });

        $this->info('Done.');

        return self::SUCCESS;
    }
}
