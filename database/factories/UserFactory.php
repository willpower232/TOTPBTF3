<?php

use Faker\Generator as Faker;
use Defuse\Crypto\KeyProtectedByPassword;

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => 'secret',
        'protected_key_encoded' => (KeyProtectedByPassword::createRandomPasswordProtectedKey('secret'))->saveToAsciiSafeString(),
        'light_mode' => false,
    ];
});
