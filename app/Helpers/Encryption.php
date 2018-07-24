<?php
namespace App\Helpers;

class Encryption
{
    public static function makeKey($input)
    {
        return hash('sha256', $input . env('ENCRYPTION_SALT', ''));
    }
}
