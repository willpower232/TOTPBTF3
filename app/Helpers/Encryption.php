<?php
namespace App\Helpers;

class Encryption
{
    const ENCRYPTION_METHOD = 'AES-256-CBC';

    public static function decrypt($input, $encryptionkey = null)
    {
        if ($encryptionkey === null) {
            $encryptionkey = session('encryptionkey');
        }

        list($iv, $cipher) = explode(';', $input);

        return openssl_decrypt($cipher, self::ENCRYPTION_METHOD, $encryptionkey, 0, base64_decode($iv));
    }

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

    public static function makeKey($input)
    {
        return hash('sha256', $input . env('ENCRYPTION_SALT', ''));
    }
}
