<?php

namespace App\Console\Commands;

use App\Models\User;
use Defuse\Crypto\KeyProtectedByPassword;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateUser extends Command
{
    protected $signature = 'user:create';

    /**
     * Prompt user for details to create user.
     */
    public function handle(): int
    {
        if (config()->boolean('app.readonly')) {
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
            return self::FAILURE;
        }

        $protected_key = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);

        $user['protected_key_encoded'] = $protected_key->saveToAsciiSafeString();

        User::create($user);

        $this->info('Done.');

        return self::SUCCESS;
    }
}
