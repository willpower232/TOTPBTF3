<?php
namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Token;
use App\Helpers\Encryption;
use App\Models\User;

class ReadOnlyTokensTest extends TestCase
{
    use RefreshDatabase;

    private static $encryptionsalt = 'lsngmym1nd';
    private $token;

    /**
     * Prepare to test the token pages that should block edits in read only mode
     *
     * @return void
     */
    public function setUp()
    {
        // alter env before running setup
        putenv('ENCRYPTION_SALT=' . self::$encryptionsalt);
        putenv('READ_ONLY=true');

        parent::setUp();

        $this->token = factory(Token::class)->create();

        $this->testinguser = $this->token->user;
    }

    /**
     * Make sure new tokens cannot be imported
     *
     * @return void
     */
    public function testCreateToken()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.create'));

        $response->assertStatus(404);
    }

    /**
     * Make sure existing tokens cannot be edited
     *
     * @return void
     */
    public function testTokenEditPage()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.edit', [$this->token->id_hash]));

        $response->assertStatus(404);
    }

    /**
     * Make sure existing tokens cannot be deleted
     *
     * @return void
     */
    public function testTokenDeletePage()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.delete', [$this->token->id_hash]));

        $response->assertStatus(404);
    }
}
