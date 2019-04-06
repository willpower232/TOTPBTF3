<?php
namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Token;
use App\Helpers\Encryption;
use RobThree\Auth\TwoFactorAuth;

class TokenTest extends TestCase
{
    use RefreshDatabase;

    private static $encryptionsalt = 'lsngmym1nd';
    private $token;

    /**
     * Prepare for testing tokens using encryption helper with known inputs
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();

        config(array(
            'app.encryptionsalt' => self::$encryptionsalt,
        ));

        session()->put('encryptionkey', Encryption::makeKey('wish somebody would'));

        // don't save unless we really want to
        $this->token = factory(Token::class)->make();
    }

    /**
     * Test that the model can return a decrypted secret
     *
     * @return void
     */
    public function testSecretDecryption()
    {
        $knownsecret = (new TwoFactorAuth(config('app.name')))->createSecret();
        $this->token->secret = Encryption::encrypt($knownsecret);
        $this->assertSame($knownsecret, $this->token->getDecryptedSecret());
    }

    /**
     * Test that the model can return a hashed id or null if no id present
     *
     * @return void
     */
    public function testHashedId()
    {
        $this->token->save(); // need an id
        $this->assertNotNull($this->token->id_hash);

        $unsavedtoken = new Token();
        $this->assertNull($unsavedtoken->id_hash);
    }

    /**
     * Test that the model can return a valid TOTP code
     *
     * @return void
     */
    public function testTOTPCode()
    {
        $this->assertRegExp('/^([0-9]{6})$/', $this->token->getTOTPCode());
    }

    /**
     * Test that the model can return an SVG QR code without failing
     *
     * @return void
     */
    public function testQRCode()
    {
        $this->assertEquals(0, strpos($this->token->getQRCode(), '<svg'));
    }

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

    /**
     * Verify that back slashes are switched to forward slashes
     *
     * @return void
     */
    public function testWrongSlashesInPath()
    {
        $this->assertSame('/Contoso/GitHub/', Token::formatPath('\\Contoso\\GitHub\\'));
    }

    /**
     * Verify that multiple slashes are reduced down to one
     *
     * @return void
     */
    public function testMultipleSlashesInPath()
    {
        $this->assertSame('/Contoso/GitHub/', Token::formatPath('///Contoso//GitHub/'));
    }
}
