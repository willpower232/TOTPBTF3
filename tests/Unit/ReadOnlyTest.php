<?php
namespace Tests\Unit;

use Tests\TestCase;

class ReadOnlyTest extends TestCase
{
    /**
     * Set up to confirm that methods that would write to the database
     * do not do so when system in read only mode
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();

        config(array(
            'app.readonly' => true,
        ));
    }

    /**
     * Ensure the artisan command for creating users will not run whilst app in read only mode
     *
     * @return void
     */
    public function testCreateUserFailure()
    {
        $this->expectException(\Exception::class);
        $this->artisan('user:create');
    }

    /**
     * Ensure the user edit form will not open whilst app in read only mode
     *
     * @return void
     */
    public function testEditUserFailure()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->get(route('session.edit'));

        $response->assertStatus(404);
    }

    /**
     * Ensure user cannot be edited whilst app in read only mode
     *
     * @return void
     */
    public function testUpdateUserFailure()
    {
        $response = $this->actingAsTestingUser()
            ->withEncryptionKey()
            ->postWithCsrf(route('session.update'));

        $response->assertStatus(404);
    }
}
