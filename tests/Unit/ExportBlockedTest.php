<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Token;

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
    public function setUp() : void
    {
        parent::setUp();

        config(array(
            'app.allowexport' => false,
            'app.encryptionsalt' => self::$encryptionsalt,
            'app.readonly' => false, // avoid conflict by confirming default
        ));

        $this->token = $this->makeFakeToken();

        $this->setTestingUser($this->token->user);
    }

    private function makeFakeToken()
    {
        return factory(Token::class)->create();
    }

    /**
     * Make sure a user can't export a token
     *
     * @return void
     */
    public function testExportToken()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('tokens.export', [$this->token->path]));

        $response->assertStatus(404);
    }
}
