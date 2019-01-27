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
        $user = factory(User::class)->make();
        $user->save();

        $response = $this->post(route('session.store'), array(
            '_token' => csrf_token(),
            'email' => $user->email,
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
        $user = factory(User::class)->make();
        $user->save();

        $response = $this->post(route('session.store'), array(
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
        $user = factory(User::class)->make();
        $user->save();

        $response = $this->actingAs($user)
            ->withSession(array(
                'encryptionkey' => 'somethingunused'
            ))
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
        $user = factory(User::class)->make();
        $user->save();

        $response = $this->actingAs($user)
            ->withSession(array(
                'encryptionkey' => 'somethingunused'
            ))
            ->get(route('session.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('sessions.form');
    }
}
