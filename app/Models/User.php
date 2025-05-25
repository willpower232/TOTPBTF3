<?php

namespace App\Models;

use Defuse\Crypto\KeyProtectedByPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $attributes = [
        'light_mode' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'protected_key_encoded',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'light_mode' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<Token, $this>
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    /**
     * generate an encryption key for the user
     *
     * @param string $password the users plaintext password
     *
     * @return string the unlocked user key
     */
    public function getEncryptionKey(string $password): string
    {
        $protected_key = KeyProtectedByPassword::loadFromAsciiSafeString($this->protected_key_encoded);
        $user_key = $protected_key->unlockKey($password);

        return $user_key->saveToAsciiSafeString();
    }

    /**
     * store the encryption key in the session
     *
     * @param string $password whatever was input
     */
    public function putEncryptionKeyInSession(string $password): void
    {
        session()->put('encryptionkey', $this->getEncryptionKey($password));
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

        if ($for == 'login' || $for == 'create' || $for == 'update') {
            array_merge_by_reference($rules, [
                'email' => 'required|string|email',
            ]);

            if ($for == 'login') {
                array_merge_by_reference($rules, [
                    'password' => 'required|string',
                ]);
            }

            if ($for == 'create' || $for == 'update') {
                array_merge_by_reference($rules, [
                    'name' => 'required|string',
                ]);
            }

            if ($for == 'create') {
                array_merge_by_reference($rules, [
                    'password' => 'required|string',
                ]);
            }

            if ($for == 'update') {
                array_merge_by_reference($rules, [
                    'currentpassword' => 'required|string',
                    'newpassword' => 'confirmed',
                ]);
            }
        }

        return $rules;
    }
}
