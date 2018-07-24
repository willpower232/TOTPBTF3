<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Encryption;
use RobThree\Auth\TwoFactorAuth;

use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;

class Token extends Model
{
    protected $fillable = array(
        'user_id',
        'path',
        'title',
        'secret',
    );

    /**
     * Guarantee that an input begins and ends with a /.
     *
     * @param string $input a path-like string
     *
     * @return string a guaranteed correctly-formatted path-like string
     */
    public static function formatPath($input)
    {
        if (strlen($input) > 0) {
            if ($input[0] != '/') {
                $input = '/' . $input;
            }
    
            if (substr($input, -1) != '/') {
                $input .= '/';
            }
        }

        return $input;
    }

    /**
     * A shortcut function to return the secret from the database
     * decrypted with the users session encryptionkey.
     *
     * @return string the decrypted secret
     */
    public function getDecryptedSecret()
    {
        return Encryption::decrypt($this->secret);
    }

    /**
     * Get the current 6-digit TOTP code for the secret.
     *
     * @return string the 6-digit code
     */
    public function getTOTPCode()
    {
        $tfa = new TwoFactorAuth('WPInc Test');

        return $tfa->getCode($this->getDecryptedSecret());
    }

    /**
     * Get the secret and accompanying information as a QR code
     * to export to another device.
     *
     * @return string the SVG of a QR code, coloured to match the default theme
     */
    public function getQRCode()
    {
        // anonymous function to simplify hexadecimal colour input
        $hexToRGB = function($input) {
            return array_map('hexdec', str_split(trim($input, '#'), 2));
        };

        // from the scss
        $text = '#d0d0d0';
        $mainbackground = '#333333';

        // can omit all the params for RendererStyle if you want black and white
        $writer = new Writer(new ImageRenderer(
            new RendererStyle(
                400, // size
                1,   // border
                null,
                null,
                Fill::withForegroundColor(
                    new Rgb(...$hexToRGB($text)),           // background
                    new Rgb(...$hexToRGB($mainbackground)), // foreground
                    new EyeFill(null, null),
                    new EyeFill(null, null),
                    new EyeFill(null, null)
                )
            ),
            new SvgImageBackEnd
        ));

        // the explode removes the XML definition so it can be inlined
        $svg = explode("\n", $writer->writeString('otpauth://totp/LABEL:' . $this->title . '?secret=' . $this->getDecryptedSecret() . '&issuer=' . trim($this->path, '/')));

        return $svg[1];
    }
}
