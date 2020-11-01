<?php

use Faker\Generator as Faker;
use Defuse\Crypto\KeyProtectedByPassword;
use App\Models\User;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => 'secret',
        'protected_key_encoded' => (KeyProtectedByPassword::createRandomPasswordProtectedKey('secret'))->saveToAsciiSafeString(),
        'light_mode' => false,
    ];
});

$factory->afterMaking(User::class, function (User $user, Faker $faker) {
    $user->putEncryptionKeyInSession('secret'); // password from the factory
});
