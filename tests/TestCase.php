<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Helpers\Encryption;
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
    public function setUp()
    {
        parent::setUp();

        $this->encryptionkey = Encryption::makeKey('wish somebody would');
    }

    /**
     * create and or return a user for the tests
     *
     * @return App\Models\User
     */
    public function getTestingUser()
    {
        if (! is_object($this->testinguser)) {
            $this->testinguser = factory(User::class)->make();
            $this->testinguser->save();
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
        $this->testinguser = User::where('id', $this->getTestingUser()->id)->first();
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
