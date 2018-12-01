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
}
