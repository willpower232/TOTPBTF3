<?php
namespace Tests\Feature;

use Tests\TestCase;

class HelpersTest extends TestCase
{
    /**
     * verify that the function is a shortcut for identifying whether the main database is sqlite or not
     *
     * @return void
     */
    public function testUsingsqlite()
    {
        $this->assertEquals((config('database.default') == 'sqlite'), usingsqlite());
    }
}
