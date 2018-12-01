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
    public function setUp()
    {
        putenv('READ_ONLY=true');

        parent::setUp();
    }

    public function testCreateUserFailure()
    {
        $this->expectException(\Exception::class);
        $this->artisan('user:create');
    }
}
