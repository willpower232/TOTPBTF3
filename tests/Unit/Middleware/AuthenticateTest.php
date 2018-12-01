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
        $user = factory(User::class)->make();
        $user->save();

        $response = $this->actingAs($user)
            ->get('/codes');

        $response->assertRedirect('/login');
    }
}