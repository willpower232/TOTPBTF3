<?php
namespace Tests\Unit\Console\Commands;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure that user create command runs successfully
     *
     * @return void
     */
    public function testCreateUser()
    {
        $this->artisan('user:create')
            ->expectsQuestion('Users full name?', 'Fred')
            ->expectsQuestion('Users email address?', 'fred@example.com')
            ->expectsQuestion('Password for user?', 'secret')
            ->assertExitCode(0);
    }

    /**
     * Ensure that user create command errors appropriately
     *
     * @return void
     */
    public function testCreateUserErrors()
    {
        $this->artisan('user:create')
            ->expectsQuestion('Users full name?', '')
            ->expectsQuestion('Users email address?', 'fred@example.com')
            ->expectsQuestion('Password for user?', 'secret')
            ->assertExitCode(1);

        $this->artisan('user:create')
            ->expectsQuestion('Users full name?', 'Fred')
            ->expectsQuestion('Users email address?', '')
            ->expectsQuestion('Password for user?', 'secret')
            ->assertExitCode(1);

        $this->artisan('user:create')
            ->expectsQuestion('Users full name?', 'Fred')
            ->expectsQuestion('Users email address?', 'fred@example.com')
            ->expectsQuestion('Password for user?', '')
            ->assertExitCode(1);

        $this->artisan('user:create')
            ->expectsQuestion('Users full name?', 'Fred')
            ->expectsQuestion('Users email address?', 'notanemailaddress')
            ->expectsQuestion('Password for user?', 'secret')
            ->assertExitCode(1);
    }
}
