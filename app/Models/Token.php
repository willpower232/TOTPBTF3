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

    public function getDecryptedSecret()
    {
        return Encryption::decrypt($this->secret);
    }

    public function getTOTPCode()
    {
        $tfa = new TwoFactorAuth('WPInc Test');

        return $tfa->getCode($this->getDecryptedSecret());
    }

    public function getQRCode()
    {
        $hexToRGB = function($input) {
            return array_map('hexdec', str_split(trim($input, '#'), 2));
        };

        // from the scss
        $text = '#d0d0d0';
        $mainbackground = '#333333';

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

        $svg = explode("\n", $writer->writeString('otpauth://totp/LABEL:' . $this->title . '?secret=' . $this->getDecryptedSecret() . '&issuer=' . trim($this->path, '/')));
        
        // the explode removes the XML definition
        return $svg[1];
    }
}
