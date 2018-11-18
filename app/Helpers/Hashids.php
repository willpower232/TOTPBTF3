<?php
namespace App\Helpers;

use Hashids\Hashids as Hasher;
use Exception;

class Hashids
{
    /**
     * Decode a value using our apps salt
     *
     * @param string $input a previously encoded value
     *
     * @return string|array the decoded value or values
     */
    public static function decode($input)
    {
        $decoded = self::getHasher()->decode($input);

        if (count($decoded) < 1) {
            throw new Exception("Bogus input to hashids decode");
        }

        return (count($decoded) === 1) ? reset($decoded) : $decoded;
    }

    /**
     * Encode a value or values using our apps salt
     *
     * @param mixed $args values to encode
     *
     * @return string encoded value or values
     */
    public static function encode(...$args)
    {
        $toreturn = self::getHasher()->encode($args);

        if (strlen($toreturn) < 1) {
            throw new Exception('Bogus input to hashids encode');
        }

        return $toreturn;
    }

    /**
     * Return an instance of Hashids prepared with our apps salt
     *
     * @return Hashids an instance of Hashids
     */
    private static function getHasher()
    {
        return new Hasher(config('app.hashidssalt'));
    }
}
