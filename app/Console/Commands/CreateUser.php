<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Validator;
use App\Models\User;
use Defuse\Crypto\KeyProtectedByPassword;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * Prompt user for details to create user.
     *
     * @return mixed
     */
    public function handle()
    {
        if (config('app.readonly')) {
            throw new \RuntimeException('This system is in read only mode and cannot be altered.');
        }

        $name = $this->ask('Users full name?');

        $email = $this->ask('Users email address?');

        $password = $this->secret('Password for user?');

        $user = compact('name', 'email', 'password');

        $validator = Validator::make($user, User::getValidationRules('create'));

        if ($validator->fails()) {
            $this->info('Unable to create user');
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $protected_key = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);

        $user['protected_key_encoded'] = $protected_key->saveToAsciiSafeString();

        User::create($user);

        $this->info('Done.');
    }
}
