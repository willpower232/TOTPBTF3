<?php

use Illuminate\Database\Seeder;

use Faker\Factory as Faker;

use App\Models\User;
use App\Models\Token;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $user = factory(User::class)->create(array(
            'email' => 'user@example.com',
        ));

        $folders = $faker->words(2);
        $subfolders = $faker->words(5);

        factory(Token::class, $faker->numberBetween(7, 15))->create(array(
            'user_id' => $user
        ))->each(function ($token) use ($faker, $folders, $subfolders) {
            // add subfolders randomly
            if ($faker->boolean(35)) {
                $token->path = $faker->randomElement($subfolders) . '/' . $token->path;
            }

            // want top level folders all the time
            $token->path = $faker->randomElement($folders) . '/' . $token->path;

            $token->save();
        });
    }
}
