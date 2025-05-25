<?php

namespace Tests\Unit\Console\Commands;

use App\Models\User;
use Illuminate\Testing\PendingCommand;
use Tests\DatabaseTestCase;

class CreateUserTest extends DatabaseTestCase
{
    /**
     * Ensure that user create command runs successfully
     */
    public function testCreateUser(): void
    {
        $this->assertDatabaseCount('users', 0);

        /** @var PendingCommand $command */
        $command = $this->artisan('user:create');

        $command
            ->expectsQuestion('Users full name?', 'Fred')
            ->expectsQuestion('Users email address?', 'fred@example.com')
            ->expectsQuestion('Password for user?', 'password')
            ->assertOk()
            ->execute(); // actually execute the command and assertions before destruct

        $this->assertDatabaseCount('users', 1);

        $user = User::first();

        $this->assertNotNull($user);

        $this->assertSame('Fred', $user->name);
        $this->assertSame('fred@example.com', $user->email);
        $this->assertTrue(password_verify('password', $user->password));
    }

    /**
     * Ensure that user create command errors appropriately
     */
    public function testCreateUserErrors(): void
    {
        /** @var PendingCommand $command */
        $command = $this->artisan('user:create');

        $command
            ->expectsQuestion('Users full name?', '')
            ->expectsQuestion('Users email address?', 'fred@example.com')
            ->expectsQuestion('Password for user?', 'password')
            ->assertFailed()
            ->execute(); // actually execute the command and assertions before destruct;

        /** @var PendingCommand $command */
        $command = $this->artisan('user:create');

        $command
            ->expectsQuestion('Users full name?', 'Fred')
            ->expectsQuestion('Users email address?', '')
            ->expectsQuestion('Password for user?', 'password')
            ->assertFailed()
            ->execute(); // actually execute the command and assertions before destruct;

        /** @var PendingCommand $command */
        $command = $this->artisan('user:create');

        $command
            ->expectsQuestion('Users full name?', 'Fred')
            ->expectsQuestion('Users email address?', 'fred@example.com')
            ->expectsQuestion('Password for user?', '')
            ->assertFailed()
            ->execute(); // actually execute the command and assertions before destruct;

        /** @var PendingCommand $command */
        $command = $this->artisan('user:create');

        $command
            ->expectsQuestion('Users full name?', 'Fred')
            ->expectsQuestion('Users email address?', 'notanemailaddress')
            ->expectsQuestion('Password for user?', 'password')
            ->assertFailed()
            ->execute(); // actually execute the command and assertions before destruct;
    }

    /**
     * Ensure the artisan command for creating users will not run whilst app in read only mode
     */
    public function testCreateUserReadOnly(): void
    {
        config([
            'app.readonly' => true,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This system is in read only mode and cannot be altered.');

        $this->artisan('user:create');
    }
}
