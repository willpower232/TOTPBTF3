<?php

use Faker\Generator as Faker;
use App\Models\Token;
use App\Models\User;
use RobThree\Auth\TwoFactorAuth;

$factory->define(Token::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class),
        'path' => $faker->company,
        'title' => $faker->company,
        'secret' => (new TwoFactorAuth(config('app.name')))->createSecret(),
    ];
});

$factory->afterMaking(Token::class, function (Token $token, Faker $faker) {
    // after the token has been made, it will have a user
    // and an encryptionkey in the session to allow the secret to be encrypted
    $token->secret = Token::encryptSecret($token->secret);
    $token->save(); //lol?
});
