<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $testinguser;
    protected $encryptionkey;

    /**
     * Generate an appropriate encryption key for the session
     *
     * @return void
     */
    // public function setUp()
    // {
    //     parent::setUp();
    // }

    public function setTestingUser(User $user, string $password = 'secret')
    {
        $this->testinguser = $user;
        $this->encryptionkey = $user->getEncryptionKey($password);
    }

    /**
     * create and or return a user for the tests
     *
     * @return App\Models\User
     */
    public function getTestingUser()
    {
        if (! is_object($this->testinguser)) {
            $this->setTestingUser(factory(User::class)->create());
        }

        return $this->testinguser;
    }

    /**
     * Update the cached user object
     *
     * @return void
     */
    public function refreshTestingUser()
    {
        $this->testinguser->refresh();
    }

    /**
     * Shortcut function for acting as cached user object
     *
     * @return $this
     */
    public function actingAsTestingUser()
    {
        return $this->actingAs($this->getTestingUser());
    }

    /**
     * Shortcut function for applying the encryptionkey to the session
     *
     * @return $this
     */
    public function withEncryptionKey()
    {
        if ($this->encryptionkey === null) {
            throw new \Exception('no encryption key');
        }

        return $this->session(array(
            'encryptionkey' => $this->encryptionkey,
        ));
    }

    /**
     * Shortcut function for including the expected CSRF value in a POST
     *
     * @return $this
     */
    public function postWithCsrf($url, $values = array())
    {
        $values['_token'] = csrf_token();

        return parent::post($url, $values);
    }
}
