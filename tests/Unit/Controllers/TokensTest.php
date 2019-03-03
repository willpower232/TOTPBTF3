<?php
namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Token;
use App\Helpers\Encryption;
use App\Models\User;

class TokensTest extends TestCase
{
    use RefreshDatabase;

    private static $encryptionsalt = 'lsngmym1nd';
    private $token;

    /**
     * Prepare to test token pages
     *
     * @return void
     */
    public function setUp()
    {
        // alter env before running setup
        putenv('ENCRYPTION_SALT=' . self::$encryptionsalt);
        putenv('READ_ONLY=false'); //avoid conflict
        putenv('ALLOW_EXPORT=true');

        parent::setUp();

        $this->token = $this->makeFakeToken();

        $this->testinguser = $this->token->user;
    }

    private function makeFakeToken()
    {
        return factory(Token::class)->create();
    }

    /**
     * Make sure a user can see a list of their tokens
     *
     * @return void
     */
    public function testListTokens()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.code'));

        $response->assertStatus(200);
        $response->assertViewIs('tokens.list');
    }

    /**
     * Make sure a user can see the totp code for a token
     *
     * @return void
     */
    public function testViewToken()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.code', [$this->token->path]));

        $response->assertStatus(200);
        $response->assertViewIs('tokens.code');
    }

    /**
     * Make sure a user gets a 404 page if a token cannot be found
     *
     * @return void
     */
    public function testViewNotAToken()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.code', ['notatoken']));

        $response->assertStatus(404);
    }

    /**
     * Make sure a user can see export a token
     *
     * @return void
     */
    public function testExportToken()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.export', [$this->token->path]));

        $response->assertStatus(200);
        $response->assertViewIs('tokens.export');
    }

    /**
     * Make sure a user is redirected to the codes list
     * if they try and export a folder
     *
     * @return void
     */
    public function testExportTokenRedirect()
    {
        // set a specific path to avoid random url encoding problem
        $path = Token::formatPath('testexporttokenredirect');

        $secondtoken = factory(Token::class)->create(array(
            'path' => $path . '/Alpha',
        ));
        $thirdtoken = factory(Token::class)->create(array(
            'path' => $path . '/Beta'
        ));

        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.export', [$path]));

        $response->assertRedirect(route('tokens.code', [$path]));
    }

    /**
     * Ensure a user has a chance to create a new token
     *
     * @return void
     */
    public function testCreateToken()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tokens.form');
    }

    /**
     * Ensure a user can see the details of a token
     *
     * @return void
     */
    public function testTokenViewPage()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.show', [$this->token->id_hash]));

        $response->assertStatus(200);
        $response->assertViewIs('tokens.show');
    }

    /**
     * Ensure a user has a chance to edit the token details
     *
     * @return void
     */
    public function testTokenEditPage()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.edit', [$this->token->id_hash]));

        $response->assertStatus(200);
        $response->assertViewIs('tokens.form');
    }

    public function testTokenDeletePage()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.delete', [$this->token->id_hash]));

        $response->assertStatus(200);
        $response->assertViewIs('tokens.delete');
    }

    public function testTokenDelete()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->delete(route('tokens.destroy', [$this->token->id_hash]));

        $this->assertFalse(Token::where('id', $this->token->id)->exists());

        $response->assertRedirect(route('tokens.code'));
    }
}
