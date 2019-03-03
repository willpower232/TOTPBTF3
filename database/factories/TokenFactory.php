<?php

use Faker\Generator as Faker;
use App\Models\Token;
use App\Models\User;

$factory->define(Token::class, function (Faker $faker) {
    $user = User::inRandomOrder()->first();

    if (! is_object($user)) {
        $user = factory(User::class)->make();
        $user->save(); // for id
    }

    return [
        'user_id' => $user->id,
        'path' => $faker->company,
        'title' => $faker->company,
    ];
});
