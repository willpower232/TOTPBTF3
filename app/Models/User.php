<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
}
