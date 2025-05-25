<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Token;

class SessionsControllerWriteableTest extends SessionsControllerTests
{
    /**
     * Ensure a user update requires fields
     */
    public function testProfileUpdateMissingInput(): void
    {
        $this->actingAsTestingUser()
            ->post(route('session.update'), [
                // omitting expected fields
            ])
            ->assertRedirect(route('session.edit'))
            ->assertSessionHas('message', 'Please check your input')
            ->assertSessionHasNoErrors();
    }

    /**
     * Ensure a user update requires the users current password
     */
    public function testProfileUpdateBadPassword(): void
    {
        $this->actingAsTestingUser()
            ->post(route('session.update'), [
                'currentpassword' => 'not password', // an empty string would trigger the validator
                'name' => $this->getTestingUser()->name,
                'email' => $this->getTestingUser()->email,
            ])
            ->assertRedirect(route('session.edit'))
            ->assertSessionHasErrors(['currentpassword']);
    }

    /**
     * Ensure a users new password has been confirmed
     */
    public function testUpdateUserMismatchedPassword(): void
    {
        $newpassword = "something that isn't password";

        $this->actingAsTestingUser()
            ->post(route('session.update'), [
                'currentpassword' => 'password',
                'newpassword' => $newpassword,
                'newpassword_confirmation' => $newpassword . ' problem',
            ])
            ->assertRedirect(route('session.edit'))
            ->assertSessionHas('message', 'Please check your input')
            ->assertSessionHasNoErrors();
    }

    /**
     * Ensure a users password change was successful
     */
    public function testUpdateUserPassword(): void
    {
        $newpassword = "something that isn't password";

        $this->actingAsTestingUser()
            ->post(route('session.update'), [
                'currentpassword' => 'password',
                'name' => $this->getTestingUser()->name,
                'email' => $this->getTestingUser()->email,
                'newpassword' => $newpassword,
                'newpassword_confirmation' => $newpassword,
            ])
            ->assertRedirect(route('login'));

        $success = auth()->guard()->validate([
            'email' => $this->getTestingUser()->email,
            'password' => $newpassword,
        ]);

        $this->assertTrue($success);
    }

    /**
     * Verify that a users tokens are re-encrypted with the new details
     */
    public function testUserUpdatedSecret(): void
    {
        $token = Token::factory()->create();

        $this->assertNotNull($token->user);

        $this->setTestingUser($token->user);
        session()->put('encryptionkey', $this->encryptionkey);
        $initialprotectedkey = $token->user->protected_key_encoded;
        $initialencryptionkey = $this->encryptionkey;
        $initialdecryptedsecret = $token->getDecryptedSecret();

        $newpassword = "something that isn't password";

        // update user password, re encrypting token secret with new password
        $this->actingAsTestingUser()
            ->post(route('session.update'), [
                'currentpassword' => 'password',
                'name' => $this->getTestingUser()->name,
                'email' => $this->getTestingUser()->email,
                'newpassword' => $newpassword,
                'newpassword_confirmation' => $newpassword,
            ])
            ->assertRedirect()
            ->assertSessionMissing('encryptionkey');


            $token->refresh();

        $this->assertNotNull($token->user);

        $this->setTestingUser($token->user, $newpassword);

        // store new encryptionkey in session to successfully decrypt
        session()->put('encryptionkey', $this->encryptionkey);

        $updatedprotectedkey = $token->user->protected_key_encoded;
        $updatedencryptionkey = $this->encryptionkey;
        $updateddecryptedsecret = $token->getDecryptedSecret();

        $this->assertNotSame($initialprotectedkey, $updatedprotectedkey);
        $this->assertSame($initialencryptionkey, $updatedencryptionkey);
        $this->assertSame($initialdecryptedsecret, $updateddecryptedsecret);
    }
}
