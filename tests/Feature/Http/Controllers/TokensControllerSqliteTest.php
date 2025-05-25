<?php

namespace Tests\Feature\Http\Controllers;

class TokensControllerSqliteTest extends TokensControllerWriteableTest
{
    public function setUp(): void
    {
        parent::setUp();

        $path = tempnam(sys_get_temp_dir(), 'testdb');

        touch($path);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $path,
        ]);

        // make sure the empty database has some tables
        $this->artisan('migrate:fresh');
    }

    /**
     * Now this is true
     */
    public function testUsingsqlite(): void
    {
        $this->assertTrue(usingsqlite());
    }
}
