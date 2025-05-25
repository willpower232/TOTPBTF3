<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\DatabaseTestCase;

class SessionsControllerTest extends DatabaseTestCase
{
    /**
     * Ensure that a users light mode can be changed through the API
     */
    public function testChangingLightMode(): void
    {
        $lightmode = $this->getTestingUser()->light_mode;

        $this->actingAsTestingUser()
            ->postJson('/api/profile/setLightMode', [
                'light_mode' => ! $lightmode
            ])
            ->assertOk()
            ->assertJson([
                'current_state' => ! $lightmode,
            ]);

        $this->refreshTestingUser();

        $success = ($this->getTestingUser()->light_mode === ! $lightmode);

        $this->assertTrue($success);
    }
}
