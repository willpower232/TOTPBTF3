<?php
namespace Tests\Unit\Controllers\Api;

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
    public function setUp() : void
    {
        // alter env before running setup
        putenv('READ_ONLY=false'); // avoid conflict

        parent::setUp();
    }

    /**
     * Ensure that a users light mode can be changed through the API
     *
     * @return void
     */
    public function testChangingLightMode()
    {
        $lightmode = $this->getTestingUser()->light_mode;

        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->json('POST', '/api/profile/setLightMode', array(
                'light_mode' => ! $lightmode
            ));

        $response
            ->assertStatus(200)
            ->assertJson([
                'current_state' => ! $lightmode,
            ]);

        $this->refreshTestingUser();

        // don't use triple equals because of hilarious type juggling
        $success = ($this->getTestingUser()->light_mode == ! $lightmode);

        $this->assertTrue($success);
    }
}
