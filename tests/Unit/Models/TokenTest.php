<?php
namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Token;

class TokenTest extends TestCase
{
    /**
     * Verify that an empty path is formatted to a single slash.
     *
     * @return void
     */
    public function testEmptyPath()
    {
        $this->assertSame('/', Token::formatPath(''));
    }

    /**
     * Verify that a single slash is added to the start of a string if necessary.
     *
     * @return void
     */
    public function testLeadingPath()
    {
        $this->assertSame('/Contoso/GitHub/', Token::formatPath('Contoso/GitHub/'));
    }

    /**
     * Verify that a single slash is added to the end of a string if necessary.
     *
     * @return void;
     */
    public function testTrailingPath()
    {
        $this->assertSame('/Contoso/GitHub/', Token::formatPath('/Contoso/GitHub'));
    }
}
