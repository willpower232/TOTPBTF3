<?php

namespace Tests\Unit\Models;

use App\Models\Token;
use App\Models\User;
use Tests\DatabaseTestCase;

class UserTest extends DatabaseTestCase
{
    /**
     * Test that a user has a function tokens relationship
     */
    public function testTokensRelationship(): void
    {
        $user = User::factory()->create();

        $testToken = new Token();
        $testToken->title = 'hereiam';
        $testToken->secret = 'unnecessary';

        $user->tokens()->save($testToken);

        $foundToken = $user->tokens()->first();

        $this->assertNotNull($foundToken);

        $this->assertSame($testToken->title, $foundToken->title);
    }
}
