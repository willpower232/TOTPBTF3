<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Tests\DatabaseTestCase;

class LoginControllerTest extends DatabaseTestCase
{
    /**
     * Ensure a login form is viewable
     */
    public function testLoginForm(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertViewIs('auth.login');
    }

    /**
     * Ensure a login is possible
     */
    public function testSuccessfulLogin(): void
    {
        $this->assertGuest();

        $this->assertFalse(session()->has('encryptionkey'));

        $this->post(route('login'), [
            'email' => $this->getTestingUser()->email,
            'password' => 'password',
        ])
            ->assertRedirect()
            ->assertSessionHas('encryptionkey'); // AddEncryptionKeyToSession listener

        $this->assertAuthenticated();

        $this->get('/')
            ->assertRedirect(route('tokens.code'));
    }

    /**
     * Ensure a failed login results in a redirect
     */
    public function testFailedLogin(): void
    {
        $this->post(route('login'), [
            'email' => $this->getTestingUser()->email,
            'password' => 'not password', // an empty string would trigger the validator
        ])
            ->assertSessionHasErrors([
                'email',
            ])
            ->assertRedirect();
    }

    /**
     * Ensure missing input on login handled safely
     */
    public function testMissingInputLogin(): void
    {
        $this->post(route('login'), [
            // omitting expected fields
        ])
            ->assertSessionHasErrors([
                'email',
                'password',
            ])
            ->assertRedirect();
    }

    /**
     * Ensure users can logout
     */
    public function testLogout(): void
    {
        $this->actingAsTestingUser()
            ->get(route('logout'))
            ->assertMethodNotAllowed();

        $this->assertTrue(session()->has('encryptionkey'));

        $this->actingAsTestingUser()
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertFalse(session()->has('encryptionkey')); // session destroyed by laravel

        $this->get('/')
            ->assertRedirect(route('tokens.code'));

        $this->get(route('tokens.code'))
            ->assertRedirect(route('login'));
    }
}
