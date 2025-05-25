<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Token;
use Hashids\Hashids;

class TokensControllerWriteableTest extends TokensControllerTests
{
    public function testStoreTokenOtpAuthUrl(): void
    {
        $newFakeToken = Token::factory()
            ->for($this->getTestingUser()) // make sure encryption secret matches
            ->make();

        $this->assertDatabaseCount('tokens', 0);

        $response = $this->actingAsTestingUser()
            ->post(route('tokens.store'), [
                'path' => $newFakeToken->path,
                'title' => $newFakeToken->title,
                'secret' => "otpauth://i-am-lovely/?secret={$newFakeToken->getDecryptedSecret()}",
            ])
            ->assertSessionMissing('message')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('tokens', 1);

        $token = Token::first();

        $this->assertNotNull($token);

        $response->assertRedirect(route('tokens.code', $token->path));
    }

    public function testStoreTokenBadSecret(): void
    {
        $this->assertDatabaseCount('tokens', 0);

        $this->actingAsTestingUser()
            ->post(route('tokens.store'), [
                'path' => 'hello',
                'title' => 'hello',
                'secret' => '0189', // the only characters missing from TwoFactorAuth::$_base32dict
            ])
            ->assertSessionMissing('message')
            ->assertSessionHasErrors([
                'secret',
            ]);

        $this->assertDatabaseCount('tokens', 0);
    }

    public function testTokenShowBadCode(): void
    {
        $this->actingAsTestingUser()
            ->get(route('tokens.show', 'abc'))
            ->assertNotFound();

        $this->assertDatabaseMissing('tokens', ['id' => 9999]);

        $this->actingAsTestingUser()
            ->get(route('tokens.show', app(Hashids::class)->encode(9999)))
            ->assertNotFound();
    }
}
