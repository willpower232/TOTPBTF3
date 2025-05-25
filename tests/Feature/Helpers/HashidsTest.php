<?php

namespace Tests\Feature\Helpers;

use Hashids\Hashids;
use Tests\TestCase;

class HashidsTest extends TestCase
{
    /**
     * Tests that our Hashids wrapper is capable of decoding what it encodes
     */
    public function testDecodeEncode(): void
    {
        $number = 74656;
        $encoded = 'b1Jx';

        $this->assertSame($encoded, app(Hashids::class)->encode($number));
        $this->assertSame($number, app(Hashids::class)->decode($encoded)[0]);
    }
}
