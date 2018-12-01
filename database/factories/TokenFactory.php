<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Token::class, function (Faker $faker) {
    $user = App\Models\User::inRandomOrder()->first();

    if (! is_object($user)) {
        $user = factory(App\Models\User::class)->make();
        $user->save(); // for id
    }

    return [
        'user_id' => $user->id,
        'path' => $faker->company,
        'title' => $faker->company,
    ];
});
