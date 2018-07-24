<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

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
        $name = $this->ask('Users full name?');

        $email = $this->ask('Users email address?');

        $password = Hash::make($this->secret('Password for user?'));

        User::create(compact(array(
            'name',
            'email',
            'password',
        )));

        $this->info('Done.');
    }
}
