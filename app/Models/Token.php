<?php

namespace App\Models;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RobThree\Auth\TwoFactorAuth;

class Token extends Model
{
    /** @use HasFactory<\Database\Factories\TokenFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $appends = [
        'id_hash',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'path',
        'title',
        'secret',
    ];

    public static function booted()
    {
        self::saving(function ($token) {
            $token->path = self::formatPath($token->path);
        });
    }

    // IMPORTANT: don't create a mutator for secret because we need to re encrypt the secrets on password change

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
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
    public static function formatPath(?string $input): string
    {
        if ($input === null) {
            $input = '';
        }

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
    public static function encryptSecret(string $newsecret): string
    {
        $user_key = Key::loadFromAsciiSafeString(session('encryptionkey'));

        return Crypto::encrypt($newsecret, $user_key);
    }

    /**
     * Set the secret but encrypt it first
     */
    public function setSecret(string $newsecret): void
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
    public function getDecryptedSecret(): string
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
    public function getIdHashAttribute(): string|null
    {
        return ($this->id) ? app(Hashids::class)->encode($this->id) : null;
    }

    /**
     * Get the current 6-digit TOTP code for the secret.
     *
     * @return string the 6-digit code
     */
    public function getTOTPCode(): string
    {
        return app(TwoFactorAuth::class)->getCode($this->getDecryptedSecret());
    }

    /**
     * Return the validation rules for a given circumstance
     *
     * @param string $for the circumstances of validation
     *
     * @return array<string, string> the compiled list of rules
     */
    public static function getValidationRules(string $for = ''): array
    {
        $rules = [];

        if ($for == 'create' || $for == 'update') {
            array_merge_by_reference($rules, [
                'path' => 'required',
                'title' => 'required',
            ]);

            if ($for == 'create') {
                array_merge_by_reference($rules, [
                    'secret' => 'required',
                ]);
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
    public function getQRCode(): string
    {
        // @codeCoverageIgnoreStart
        if ($this->title === null && $this->path === null) {
            throw new \RuntimeException('this token has no identifiers somehow');
        }
        // @codeCoverageIgnoreEnd

        $dataUri = app()
            ->makeWith(
                TwoFactorAuth::class,
                [
                    'issuer' => trim($this->path ?? '', '/'),
                ]
            )
            ->getQRCodeImageAsDataUri(
                $this->title ?? '',
                $this->getDecryptedSecret(),
                400
            );

        // rm data uri string
        $base64 = substr($dataUri, 26);

        // fetch real svg xml
        $svg = base64_decode($base64);

        return $svg;
    }
}
