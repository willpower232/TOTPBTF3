<?php

namespace Tests\Unit\Console\Commands\Csv;

use App\Models\Token;
use Illuminate\Testing\PendingCommand;
use Tests\DatabaseTestCase;

class ExportTest extends DatabaseTestCase
{
    public function testHandle(): void
    {
        $token = Token::factory()
            ->for($this->getTestingUser())
            ->create([
                'title' => 'TestToken', // no space so don't have to filter out quotes
            ]);

        $file = tempnam(sys_get_temp_dir(), 'textCsvExport');

        /** @var PendingCommand $command */
        $command = $this->artisan("csv:export {$file}");

        $command
            ->expectsQuestion('Users email address?', $this->getTestingUser()->email)
            ->expectsQuestion('Password for user?', 'password')
            ->assertOk()
            ->execute(); // actually execute the command and assertions before destruct

        // now the command has executed, the file contents are a string
        $file = file_get_contents($file);
        $this->assertIsString($file);
        $this->assertNotEmpty($file);

        // headers, content, stray newline
        $file = explode(PHP_EOL, $file);
        $this->assertCount(3, $file);

        // just the good stuff
        $file = array_filter($file);
        $this->assertCount(2, $file);

        // check headers
        $this->assertSame('path,title,secret', $file[0]);

        // should be three parts of the useful line
        $tokenInfo = explode(',', $file[1]);
        $this->assertCount(3, $tokenInfo);

        // double check contents
        $this->assertSame($token->path, $tokenInfo[0]);
        $this->assertSame($token->title, $tokenInfo[1]);
        $this->assertSame($token->getDecryptedSecret(), $tokenInfo[2]);
    }

    public function testHandleBadUserDetails(): void
    {
        /** @var PendingCommand $command */
        $command = $this->artisan("csv:export whatever");

        $command
            ->expectsQuestion('Users email address?', $this->getTestingUser()->email)
            ->expectsQuestion('Password for user?', 'wrong')
            ->assertFailed();
    }

    public function testHandleMissingUserDetails(): void
    {
        /** @var PendingCommand $command */
        $command = $this->artisan("csv:export whatever");

        $command
            ->expectsQuestion('Users email address?', '')
            ->expectsQuestion('Password for user?', '')
            ->assertFailed();
    }
}
