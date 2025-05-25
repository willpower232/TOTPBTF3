<?php

namespace Database\Seeders;

use App\Models\Token;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $folders = fake()->words(2);
        $subfolders = fake()->words(5);

        Token::factory()
            ->for($user)
            ->count(fake()->numberBetween(7, 15))
            ->create()
            ->each(function ($token) use ($folders, $subfolders) {
                // add subfolders randomly
                if (fake()->boolean(35)) {
                    $token->path = fake()->randomElement($subfolders) . '/' . $token->path;
                }

                // want top level folders all the time
                $token->path = fake()->randomElement($folders) . '/' . $token->path;

                $token->save();
            });
    }
}
