<?php
namespace App\Console\Commands\Csv;

use Illuminate\Console\Command;
use Validator;
use App\Models\User;
use App\Models\Token;
use App\Helpers\Encryption;
use DB;

class Import extends Command
{
    /**
     * @inheritdoc
     */
    protected $signature = 'csv:import {source? : the file to output to}';

    /**
     * @inheritdoc
     */
    protected $description = 'Import user tokens as CSV';

    /**
     * Authorise user and import token secrets
     *
     * @return mixed
     */
    public function handle()
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
            return 1;
        }

        if (! auth()->guard()->validate($user)) {
            $this->info('Unable to login');
            $this->error('Unable to match details');
            return 1;
        }

        session()->put('encryptionkey', Encryption::makeKey($user['password']));

        $user = User::where('email', $user['email'])
            ->with('tokens')
            ->first();

        // read the entire file into an array
        $csv = array_map('str_getcsv', file($this->argument('source') ?? 'output.csv'));

        // the first row of the CSV is the keys for the array
        array_walk($csv, function(&$row) use ($csv) {
            $row = array_combine($csv[0], $row);
        });

        // now remove the column headers
        array_shift($csv);

        DB::beginTransaction();

        foreach ($csv as $newToken) {
            $token = new Token(array(
                'user_id' => $user->id,
                'path' => $newToken['path'],
                'title' => $newToken['title'],
                'secret' => Encryption::encrypt($newToken['secret']),
            ));

            try {
                $test = $token->getTOTPCode();
            } catch (\Exception $e) {
                DB::rollBack();

                throw new \RuntimeException('Problem with secret for ' . $newToken['path']);
            }

            $token->save();
        }

        DB::commit();

        $this->info('Done.');
    }
}
