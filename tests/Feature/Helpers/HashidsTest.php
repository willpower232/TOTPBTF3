<?php
namespace Tests\Feature\Helpers;

use Tests\TestCase;
use App\Helpers\Hashids;
use Exception;

class HashidsTest extends TestCase
{
    private static $hashidssalt = 'lsngmym1nd';

    /**
     * Prepare for using hashids helper with known salt.
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();

        config(array(
            'app.hashidssalt' => self::$hashidssalt,
        ));
    }

    /**
     * Tests that our Hashids wrapper is capable of decoding what it encodes
     *
     * @return void
     */
    public function testDecodeEncode()
    {
        $number = 74656;
        $this->assertEquals($number, Hashids::decode(Hashids::encode($number)));
    }

    /**
     * Tests that our Hashids wrapper will throw exception when decoding an invalid value
     *
     * @return void
     */
    public function testDecodeFailure()
    {
        $number = 74656;
        $string = Hashids::encode($number);
        $string .= 'F';

        $this->expectException(Exception::class);
        $output = Hashids::decode($string);
    }

    /**
     * Tests that our Hashids wrapper will throw exception when encoding an invalid value
     *
     * @return void
     */
    public function testEncodeFailure()
    {
        $string = 'tell me I am fine';

        $this->expectException(Exception::class);
        $output = Hashids::encode($string);
    }
}
