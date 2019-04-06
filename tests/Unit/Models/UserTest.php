<?php
namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Token;
use App\Helpers\Encryption;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    /**
     * Prepare for testing user by creating one
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    /**
     * Test that a user has a function tokens relationship
     *
     * @return void
     */
    public function testTokensRelationship()
    {
        $testtoken = new Token();
        $testtoken->title = 'hereiam';

        $this->user->tokens()->save($testtoken);

        $this->assertNotEmpty($this->user->tokens);

        $foundtoken = $this->user->tokens()->first();
        $this->assertSame($testtoken->title, $foundtoken->title);
    }
}
