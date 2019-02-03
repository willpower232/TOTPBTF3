<?php
namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SessionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prepare to test Session/User pages
     *
     * @return void
     */
    public function setUp()
    {
        // alter env before running setup
        putenv('READ_ONLY=false'); // avoid conflict

        parent::setUp();
    }

    /**
     * Ensure the homepage redirects to the codes list
     * - no extra code coverage but nice to have
     *
     * @return void
     */
    public function testNoRootPage()
    {
        $response = $this->get('/');

        $response->assertRedirect(route('tokens.code'));
    }

    /**
     * Ensure that logged out users are forced to the login page
     *
     * @return void
     */
    public function testNoAuth()
    {
        $response = $this->get(route('tokens.code'));

        $response->assertRedirect(route('session.create'));
    }

    /**
     * Ensure a login form is viewable
     *
     * @return void
     */
    public function testLoginForm()
    {
        $response = $this->get(route('session.create'));

        $response->assertStatus(200);
        $response->assertViewIs('sessions.create');
    }

    /**
     * Ensure a login is possible
     *
     * @return void
     */
    public function testSuccessfulLogin()
    {
        $response = $this->postWithCsrf(route('session.store'), array(
            'email' => $this->getTestingUser()->email,
            'password' => 'secret',
        ));

        $response->assertRedirect(route('tokens.code'));
    }

    /**
     * Ensure a failed login results in a redirect
     *
     * @return void
     */
    public function testFailedLogin()
    {
        $response = $this->postWithCsrf(route('session.store'), array(
            'email' => $this->getTestingUser()->email,
            'password' => 'not secret', // an empty string would trigger the validator
        ));

        $response->assertRedirect(route('session.create'));
    }

    /**
     * Ensure missing input on login handled safely
     *
     * @return void
     */
    public function testMissingInputLogin()
    {
        $response = $this->postWithCsrf(route('session.store'), array(
            // omitting expected fields
        ));

        $response->assertRedirect(route('session.create'));
    }

    /**
     * Ensure users can logout
     *
     * @return void
     */
    public function testLogout()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('session.destroy'));

        $response->assertRedirect(route('session.create'));
    }

    /**
     * Make sure a user can see their details
     *
     * @return void
     */
    public function testProfilePage()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('session.show'));

        $response->assertStatus(200);
        $response->assertViewIs('sessions.show');
    }

    /**
     * Make sure a user has a chance to edit their details
     *
     * @return void
     */
    public function testProfileEditPage()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('session.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('sessions.form');
    }

    /**
     * Ensure a user update requires fields
     *
     * @return void
     */
    public function testProfileUpdateMissingInput()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->postWithCsrf(route('session.update'), array(
                // omitting expected fields
            ));

        $response->assertRedirect(route('session.edit'));
    }

    /**
     * Ensure a user update requires the users current password
     *
     * @return void
     */
    public function testProfileUpdateBadPassword()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->postWithCsrf(route('session.update'), array(
                'currentpassword' => 'not secret', // an empty string would trigger the validator
                'name' => $this->getTestingUser()->name,
                'email' => $this->getTestingUser()->email,
            ));

        $response->assertRedirect(route('session.edit'));
    }

    /**
     * Ensure a users new password has been confirmed
     *
     * @return void
     */
    public function testUpdateUserMismatchedPassword()
    {
        $newpassword = "something that isn't secret";

        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->postWithCsrf(route('session.update'), array(
                'currentpassword' => 'secret',
                'newpassword' => $newpassword,
                'newpassword_confirmation' => $newpassword . ' problem',
            ));

        $response->assertRedirect(route('session.edit'));
    }

    /**
     * Ensure we can update user name and email address
     *
     * @return void
     */
    public function testProfileUpdateDetails()
    {
        $oldname = $this->getTestingUser()->name;
        $newname = $oldname . ' III';

        $oldemail = $this->getTestingUser()->email;
        $newemail = $oldemail . '.uk';

        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->postWithCsrf(route('session.update'), array(
                'currentpassword' => 'secret',
                'name' => $newname,
                'email' => $newemail,
            ));

        $response->assertRedirect(route('session.show'));

        $this->refreshTestingUser();

        $this->assertEquals($this->getTestingUser()->name, $newname);
        $this->assertEquals($this->getTestingUser()->email, $newemail);
    }

    /**
     * Ensure a users password change was successful
     *
     * @return void
     */
    public function testUpdateUserPassword()
    {
        $newpassword = "something that isn't secret";

        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->postWithCsrf(route('session.update'), array(
                'currentpassword' => 'secret',
                'name' => $this->getTestingUser()->name,
                'email' => $this->getTestingUser()->email,
                'newpassword' => $newpassword,
                'newpassword_confirmation' => $newpassword,
            ));

        $response->assertRedirect(route('session.create'));

        $success = auth()->validate(array(
            'email' => $this->getTestingUser()->email,
            'password' => $newpassword,
        ));

        $this->assertTrue($success);
    }
}
