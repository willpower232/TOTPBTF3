<?php
namespace Tests\Feature\Helpers;

use Tests\TestCase;
use App\Models\Token;

class TwigBridgeExtensionTest extends TestCase
{
    /**
     * The critical CSS extension requires two or more page loads to be fully used
     *
     * @return void
     */
    public function testCriticalCSS()
    {
        for ($x = 1; $x <= 2; $x++) {
            $response = $this->get(route('session.create'));
            $response->assertOk();
            $response->assertStatus(200);
            $response->assertViewIs('sessions.create');
        }
    }
}
