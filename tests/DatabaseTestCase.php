<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class DatabaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $testinguser;
    protected string $encryptionkey;

    public function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql', // compensate for the sqlite test
        ]);
    }

    public function setTestingUser(User $user, string $password = 'password'): void
    {
        $this->testinguser = $user;
        $this->encryptionkey = $user->getEncryptionKey($password);
    }

    /**
     * create and or return a user for the tests
     */
    public function getTestingUser(): User
    {
        if (! isset($this->testinguser)) {
            $user = User::factory()->create();

            // refresh to load the missing attributes from the database
            $user->refresh();

            $this->setTestingUser($user);
        }

        return $this->testinguser;
    }

    /**
     * Update the cached user object
     */
    public function refreshTestingUser(): void
    {
        $this->testinguser->refresh();
    }

    /**
     * Shortcut function for acting as cached user object
     */
    public function actingAsTestingUser(): self
    {
        $this->actingAs($this->getTestingUser());

        return $this;
    }
}
