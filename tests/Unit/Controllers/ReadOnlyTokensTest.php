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
    private static $totpsecret = '74ZHUVE5JW4Y3HKX'; // secret generated by totp provider
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

        // this session put applies to all acting as calls below
        session()->put('encryptionkey', Encryption::makeKey('wish somebody would'));

        $this->token = factory(Token::class)->make();
        $this->token->secret = Encryption::encrypt(self::$totpsecret);
        $this->token->save(); // for hashed id test
    }

    /**
     * Make sure new tokens cannot be imported
     *
     * @return void
     */
    public function testCreateToken()
    {
        $response = $this->actingAs($this->token->user)
            ->get('/import');

        $response->assertStatus(404);
    }

    /**
     * Make sure existing tokens cannot be edited
     *
     * @return void
     */
    public function testTokenEditPage()
    {
        $response = $this->actingAs($this->token->user)
            ->get('/tokens/' . $this->token->getIdHashAttribute() . '/edit');

        $response->assertStatus(404);
    }

    /**
     * Make sure existing tokens cannot be deleted
     *
     * @return void
     */
    public function testTokenDeletePage()
    {
        $response = $this->actingAs($this->token->user)
            ->get('/tokens/' . $this->token->getIdHashAttribute() . '/delete');

        $response->assertStatus(404);
    }
}
