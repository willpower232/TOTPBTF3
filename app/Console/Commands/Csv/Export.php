<?php
namespace App\Console\Commands\Csv;

use Illuminate\Console\Command;
use Validator;
use App\Models\User;
use Defuse\Crypto\KeyProtectedByPassword;

class Export extends Command
{
    /**
     * @inheritdoc
     */
    protected $signature = 'csv:export {destination? : the file to output to}';

    /**
     * @inheritdoc
     */
    protected $description = 'Export user tokens as CSV';

    /**
     * Authorise user and export token secrets
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

        $user = User::where('email', $email)
            ->with('tokens')
            ->first();

        $user->putEncryptionKeyInSession($password);

        $cache = '';

        if (($line = $this->makeCSVLine(array('path', 'title', 'secret'))) === false) {
            $this->info('Unable to build export');
            return 1;
        }

        $cache .= $line . PHP_EOL;

        foreach ($user->tokens as $token) {
            $secret = $token->getDecryptedSecret();

            if (($line = $this->makeCSVLine(array($token->path, $token->title, $secret))) === false) {
                $this->info('Problem building export');
                return 1;
            }

            $cache .= $line . PHP_EOL;
        }

        file_put_contents($this->argument('destination') ?? 'output.csv', $cache);

        $this->info('Done.');
    }

    /**
     * Converts an array to a line of CSV-formatted data and returns
     *
     * @param array<string> $fields list of fields
     *
     * @return bool|string CSV-formatted string or false on failure
     */
    private function makeCSVLine(array $fields)
    {
        $f = fopen('php://memory', 'r+');
        if (fputcsv($f, $fields) === false) {
            return false;
        }
        rewind($f);
        $csv_line = stream_get_contents($f);
        return rtrim($csv_line);
    }
}
