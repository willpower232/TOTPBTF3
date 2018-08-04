<?php
namespace Tests\Feature\Helpers;

use Tests\TestCase;
use App\Helpers\Encryption;

class EncryptionTest extends TestCase
{
    private static $encryptionkey;

    /**
     * Prepare for using encryption helper with known salt and key input.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    { 
        putenv('ENCRYPTION_SALT=lsngmym1nd');

        self::$encryptionkey = Encryption::makeKey('wish somebody would');
    }

    /**
     * Verify encyption key generated with key input followed by encryption salt.
     *
     * @return void
     */
    public function testKey()
    {
        $this->assertEquals('d8f12b1737e7ede3daca0dfd311edcde2b702f2bd4c5deabfdcd0f0ac6f28d30', self::$encryptionkey);
    }

    /**
     * Verify that the helper class can decrypt what it encrypted.
     *
     * @return void
     */
    public function testDecryptEncrypt()
    {
        $string = 'tell me I am fine';
        $this->assertEquals($string, Encryption::decrypt(Encryption::encrypt($string, self::$encryptionkey), self::$encryptionkey));
    }
}
