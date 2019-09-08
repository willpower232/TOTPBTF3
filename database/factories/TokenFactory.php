<?php

use Faker\Generator as Faker;
use App\Models\Token;
use App\Models\User;
use RobThree\Auth\TwoFactorAuth;

$factory->define(Token::class, function (Faker $faker) {
    $user = User::inRandomOrder()->first();

    if (! is_object($user)) {
        $user = factory(User::class)->create();
        $user->putEncryptionKeyInSession('secret'); // password from the factory
    }

    return [
        'user_id' => $user->id,
        'path' => $faker->company,
        'title' => $faker->company,
        'secret' => Token::encryptSecret((new TwoFactorAuth(config('app.name')))->createSecret()),
    ];
});
