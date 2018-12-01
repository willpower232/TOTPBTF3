<?php
namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

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

        $response->assertRedirect('/codes');
    }

    /**
     * Ensure that logged out users are forced to the login page
     *
     * @return void
     */
    public function testNoAuth()
    {
        $response = $this->get('/codes');

        $response->assertRedirect('/login');
    }

    /**
     * Ensure a login form is viewable
     *
     * @return void
     */
    public function testLoginForm()
    {
        $response = $this->get('/login');

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
        $user = factory(User::class)->make();
        $user->save();

        $response = $this->post('/login', array(
            '_token' => csrf_token(),
            'email' => $user->email,
            'password' => 'secret',
        ));

        $response->assertRedirect('/codes');
    }

    /**
     * Ensure a failed login results in a redirect
     *
     * @return void
     */
    public function testFailedLogin()
    {
        $user = factory(User::class)->make();
        $user->save();

        $response = $this->post('/login', array(
            '_token' => csrf_token(),
            'email' => $user->email,
            'password' => 'not secret', // an empty string would trigger the validator
        ));

        $response->assertRedirect('/');
    }

    /**
     * Ensure users can logout
     *
     * @return void
     */
    public function testLogout()
    {
        $user = factory(User::class)->make();
        $user->save();

        $response = $this->actingAs($user)
            ->withSession(array(
                'encryptionkey' => 'somethingunused'
            ))
            ->get('/logout');

        $response->assertRedirect('/login');
    }

    /**
     * Make sure a user can see their details
     *
     * @return void
     */
    public function testProfilePage()
    {
        $user = factory(User::class)->make();
        $user->save();

        $response = $this->actingAs($user)
            ->withSession(array(
                'encryptionkey' => 'somethingunused'
            ))
            ->get('/profile');

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
        $user = factory(User::class)->make();
        $user->save();

        $response = $this->actingAs($user)
            ->withSession(array(
                'encryptionkey' => 'somethingunused'
            ))
            ->get('/profile/edit');

        $response->assertStatus(200);
        $response->assertViewIs('sessions.form');
    }
}
