<?php

namespace Tests\Unit\Console\Commands\Csv;

use App\Models\Token;
use Illuminate\Testing\PendingCommand;
use Tests\DatabaseTestCase;

class ImportTest extends DatabaseTestCase
{
    public function testHandle(): void
    {
        $tokenInfo = Token::factory()
            ->make([
                'title' => 'TestToken', // no space so don't have to filter out quotes
            ]);

        $file = tempnam(sys_get_temp_dir(), 'textCsvImport');

        file_put_contents($file, <<<CSV
        path,title,secret
        {$tokenInfo->path},{$tokenInfo->title},{$tokenInfo->getDecryptedSecret()}
        CSV);

        $this->assertDatabaseCount('tokens', 0);

        /** @var PendingCommand $command */
        $command = $this->artisan("csv:import {$file}");

        $command
            ->expectsQuestion('Users email address?', $this->getTestingUser()->email)
            ->expectsQuestion('Password for user?', 'password')
            ->assertOk()
            ->execute(); // actually execute the command and assertions before destruct

        $this->assertDatabaseCount('tokens', 1);

        $token = Token::firstOrFail();

        $this->assertNotNull($token->user);
        $this->assertTrue($token->user->is($this->getTestingUser()));
        $this->assertSame($tokenInfo->title, $token->title);
    }

    public function testHandleBadFile(): void
    {
        $tokenInfo = Token::factory()
            ->make([
                'title' => 'TestToken', // no space so don't have to filter out quotes
            ]);

        $file = tempnam(sys_get_temp_dir(), 'textCsvImport');

        file_put_contents($file, <<<CSV
        path,title,secret
        {$tokenInfo->path},{$tokenInfo->title},notasecret
        CSV);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Problem with secret for {$tokenInfo->path}");

        /** @var PendingCommand $command */
        $command = $this->artisan("csv:import {$file}");

        $command
            ->expectsQuestion('Users email address?', $this->getTestingUser()->email)
            ->expectsQuestion('Password for user?', 'password')
            ->execute();
    }

    public function testHandleBadUserDetails(): void
    {
        /** @var PendingCommand $command */
        $command = $this->artisan("csv:import whatever");

        $command
            ->expectsQuestion('Users email address?', $this->getTestingUser()->email)
            ->expectsQuestion('Password for user?', 'wrong')
            ->assertFailed();
    }

    public function testHandleMissingUserDetails(): void
    {
        /** @var PendingCommand $command */
        $command = $this->artisan("csv:import whatever");

        $command
            ->expectsQuestion('Users email address?', '')
            ->expectsQuestion('Password for user?', '')
            ->assertFailed();
    }
}
