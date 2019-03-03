<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Token;
use App\Helpers\Encryption;

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
        putenv('ALLOW_EXPORT=false');

        parent::setUp();

        $this->token = $this->makeFakeToken();

        $this->testinguser = $this->token->user;
    }

    private function makeFakeToken()
    {
        $token = factory(Token::class)->make();
        $token->save(); // for valid path

        return $token;
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
