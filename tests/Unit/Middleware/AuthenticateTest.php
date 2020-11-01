<?php
namespace Tests\Unit\Middleware;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AuthenticateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Make sure a logged in user is logged out if the encryption key is not set in their session
     *
     * @return void
     */
    public function testMissingEncryptionkey()
    {
        $response = $this->actingAsTestingUser();

        // getting the testing user sets an encryption key
        session()->forget('encryptionkey');

        $response = $response->get(route('tokens.code'));

        $response->assertRedirect(route('session.create'));
    }
}
