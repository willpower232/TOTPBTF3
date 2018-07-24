<?php
namespace App\Helpers;

class Encryption
{
    /**
     * The encryption algorithm to use
     *
     * @var string
     */
    const ENCRYPTION_METHOD = 'AES-256-CBC';

    /**
     * Decrypts a string, hopefully the first half is the cipher that was used,
     * the encryption key matches, and the algorithm hasn't changed.
     *
     * @param string $input encrypted string, the result of the encrypt function
     * @param string|null $encryptionkey an encryption key to decrypt with
     *      if null, use key from session
     *
     * @return string decrypted value
     */
    public static function decrypt($input, $encryptionkey = null)
    {
        if ($encryptionkey === null) {
            $encryptionkey = session('encryptionkey');
        }

        list($iv, $cipher) = explode(';', $input);

        return openssl_decrypt($cipher, self::ENCRYPTION_METHOD, $encryptionkey, 0, base64_decode($iv));
    }

    /**
     * Encrypts a string using the algorithm.
     *
     * @param string $input string to encrypt
     * @param string|null $encryptionkey an encryption key to decrypt with
     *      if null, use key from session
     *
     * @return string encrypted value including IV
     */
    public static function encrypt($input, $encryptionkey = null)
    {
        if ($encryptionkey === null) {
            $encryptionkey = session('encryptionkey');
        }

        $iv = random_bytes(openssl_cipher_iv_length(self::ENCRYPTION_METHOD));

        // remember openssl_encrypt returns base64!
        $cipher = openssl_encrypt($input, self::ENCRYPTION_METHOD, $encryptionkey, 0, $iv);

        return base64_encode($iv) . ';' . $cipher;
    }

    /**
     * Generates a hash to use as an encryption key.
     *
     * The 256 matches the algorithm used in this class.
     *
     * @param string $input string to base the encryption key/hash on
     *
     * @return string hash to use as encryption key
     */
    public static function makeKey($input)
    {
        return hash('sha256', $input . env('ENCRYPTION_SALT', ''));
    }
}
