<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Helpers\Hashids;
use RobThree\Auth\TwoFactorAuth;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;

class Token extends Model
{
    /** @var array<string> */
    protected $appends = array(
        'id_hash',
    );

    /** @var array<string> */
    protected $fillable = array(
        'user_id',
        'path',
        'title',
        'secret',
    );

    public static function boot()
    {
        parent::boot();

        self::saving(function ($token) {
            $token->path = self::formatPath($token->path);
        });
    }

    // IMPORTANT: don't create a mutator for secret because we need to re encrypt the secrets on password change

    public function user() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Guarantee that an input begins and ends with a /.
     *
     * @param string|null $input a path-like string
     *
     * @return string a guaranteed correctly-formatted path-like string
     */
    public static function formatPath(?string $input) : string
    {
        // make sure we're using the correct slash
        $input = str_replace('\\', '/', $input);

        // cope with an empty string here
        if (substr($input, -1) != '/') {
            $input .= '/';
        }

        // this can't cope with an empty string
        // because of the explicit 0
        if ($input[0] != '/') {
            $input = '/' . $input;
        }

        // filter out any double slashes
        while (strpos($input, '//') !== false) {
            $input = str_replace('//', '/', $input);
        }

        return $input;
    }

    /**
     * Encrypt a secret with the sessions encryption key, decoupled for faker
     *
     * @param string $newsecret
     *
     * @throws \Defuse\Crypto\Exception\BadFormatException
     *
     * @return string the encrypted value
     */
    public static function encryptSecret(string $newsecret) : string
    {
        $user_key = Key::loadFromAsciiSafeString(session('encryptionkey'));

        return Crypto::encrypt($newsecret, $user_key);
    }

    /**
     * Set the secret but encrypt it first
     *
     * @return void
     */
    public function setSecret(string $newsecret) : void
    {
        $this->secret = self::encryptSecret($newsecret);
    }

    /**
     * A shortcut function to return the secret from the database
     * decrypted with the users session encryptionkey.
     *
     * @throws \Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException
     *
     * @return string the decrypted secret
     */
    public function getDecryptedSecret()
    {
        $user_key = Key::loadFromAsciiSafeString(session('encryptionkey'));

        // decrypt can return an exception but we want to let that get into the UI or something
        return Crypto::decrypt($this->secret, $user_key);
    }

    /**
     * Eloquent Accessor for Hashids encoded attribute
     *
     * @return string|null if the current object has an id, it will be returned hashed
     */
    public function getIdHashAttribute()
    {
        return ($this->id != '') ? Hashids::encode($this->id) : null;
    }

    /**
     * Get the current 6-digit TOTP code for the secret.
     *
     * @return string the 6-digit code
     */
    public function getTOTPCode()
    {
        return (new TwoFactorAuth(config('app.name')))->getCode($this->getDecryptedSecret());
    }

    /**
     * Return the validation rules for a given circumstance
     *
     * @param string $for the circumstances of validation
     *
     * @return array<string, string> the compiled list of rules
     */
    public static function getValidationRules(string $for = '')
    {
        $rules = array();

        if ($for == 'create' || $for == 'update') {
            array_merge_by_reference($rules, array(
                'path' => 'required',
                'title' => 'required',
            ));

            if ($for == 'create') {
                array_merge_by_reference($rules, array(
                    'secret' => 'required',
                ));
            }
        }

        return $rules;
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
        $hexToRGB = function ($input) {
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
        $svg = explode("\n", $writer->writeString(
            'otpauth://totp/' . rawurlencode($this->title) .
            '?secret=' . $this->getDecryptedSecret() .
            '&issuer=' . rawurlencode(trim($this->path, '/'))
        ));

        return $svg[1];
    }
}
