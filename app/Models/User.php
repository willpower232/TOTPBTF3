<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Hash;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = array(
        'name',
        'email',
        'password',
    );

    protected $hidden = array(
        'password',
        'remember_token',
    );

    /**
     * Hash the password value when it is set
     *
     * WARNING: the existence of this method means that Auth::logoutOtherDevices will break your user record
     * because you will end up hashing a hash
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function tokens()
    {
        return $this->hasMany(Token::class);
    }

    /**
     * Return the validation rules for a given circumstance
     *
     * @param string $for the circumstances of validation
     *
     * @return array the compiled list of rules
     */
    public static function getValidationRules($for = '')
    {
        $rules = array();

        if ($for == 'login' || $for == 'create' || $for == 'update') {
            array_merge_by_reference($rules, array(
                'email' => 'required|string|email',
            ));

            if ($for == 'login') {
                array_merge_by_reference($rules, array(
                    'password' => 'required|string',
                ));
            }

            if ($for == 'create' || $for == 'update') {
                array_merge_by_reference($rules, array(
                    'name' => 'required|string',
                ));
            }

            if ($for == 'create') {
                array_merge_by_reference($rules, array(
                    'password' => 'required|string',
                ));
            }

            if ($for == 'update') {
                array_merge_by_reference($rules, array(
                    'currentpassword' => 'required|string',
                    'newpassword' => 'confirmed',
                ));
            }
        }

        return $rules;
    }
}
