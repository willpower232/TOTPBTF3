<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Token;

class ExportBlockedTest extends TestCase
{
    use RefreshDatabase;

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
