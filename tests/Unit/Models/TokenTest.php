<?php

namespace Tests\Unit\Models;

use App\Models\Token;
use RobThree\Auth\TwoFactorAuth;
use Tests\DatabaseTestCase;

class TokenTest extends DatabaseTestCase
{
    /**
     * Test that the model can return a decrypted secret
     */
    public function testSecretDecryption(): void
    {
        $token = Token::factory()
            ->for($this->getTestingUser()) // make sure encryption secret matches
            ->make();

        $knownsecret = app(TwoFactorAuth::class)->createSecret();
        $token->setSecret($knownsecret);
        $this->assertSame($knownsecret, $token->getDecryptedSecret());
    }

    /**
     * Test that the model can return a hashed id or null if no id present
     */
    public function testHashedId(): void
    {
        $token = Token::factory()
            ->for($this->getTestingUser()) // make sure encryption secret matches
            ->create();

        $this->assertNotNull($token->id_hash);

        $unsavedtoken = new Token();
        $this->assertNull($unsavedtoken->id_hash);
    }

    /**
     * Test that the model can return a valid TOTP code
     */
    public function testTOTPCode(): void
    {
        $token = Token::factory()
            ->for($this->getTestingUser()) // make sure encryption secret matches
            ->make();

        $this->assertMatchesRegularExpression('/^([0-9]{6})$/', $token->getTOTPCode());
    }

    /**
     * Test that the model can return an SVG QR code without failing
     */
    public function testQRCode(): void
    {
        $token = Token::factory()
            ->for($this->getTestingUser()) // make sure encryption secret matches
            ->make();

        $this->assertTrue(str_starts_with($token->getQRCode(), '<svg'));
    }

    /**
     * Verify that an empty path is formatted to a single slash.
     */
    public function testEmptyPath(): void
    {
        $this->assertSame('/', Token::formatPath(''));
    }

    /**
     * Verify that a single slash is added to the start of a string if necessary.
     */
    public function testLeadingPath(): void
    {
        $this->assertSame('/Contoso/GitHub/', Token::formatPath('Contoso/GitHub/'));
    }

    /**
     * Verify that a single slash is added to the end of a string if necessary.
     */
    public function testTrailingPath(): void
    {
        $this->assertSame('/Contoso/GitHub/', Token::formatPath('/Contoso/GitHub'));
    }

    /**
     * Verify that back slashes are switched to forward slashes
     */
    public function testWrongSlashesInPath(): void
    {
        $this->assertSame('/Contoso/GitHub/', Token::formatPath('\\Contoso\\GitHub\\'));
    }

    /**
     * Verify that multiple slashes are reduced down to one
     */
    public function testMultipleSlashesInPath(): void
    {
        $this->assertSame('/Contoso/GitHub/', Token::formatPath('///Contoso//GitHub/'));
    }
}
