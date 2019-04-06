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
    public function setUp() : void
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

    /**
     * Ensure token cannot be added whilst app in read only mode
     *
     * @return void
     */
    public function testAddTokenFailure()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->postWithCsrf(route('tokens.store'));

        $response->assertStatus(404);
    }

    /**
     * Ensure token cannot be edited whilst app in read only mode
     *
     * @return void
     */
    public function testEditTokenFailure()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->postWithCsrf(route('tokens.update', [$this->token->id_hash]));

        $response->assertStatus(404);
    }

    /**
     * Ensure token cannot be deleted whilst app in read only mode
     *
     * @return void
     */
    public function testDeleteTokenFailure()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->delete(route('tokens.destroy', [$this->token->id_hash]));

        $response->assertStatus(404);
    }
}
