<?php

namespace Database\Factories;

use App\Models\Token;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use RobThree\Auth\TwoFactorAuth;

/**
 * @extends Factory<\App\Models\Token>
 */
class TokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'path' => str()->snake(fake()->company()),
            'title' => fake()->company(),
            'secret' => app(TwoFactorAuth::class)->createSecret(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Token $token) {
            // after the token has been made, it will have a user
            // and an encryptionkey in the session to allow the secret to be encrypted
            $token->secret = Token::encryptSecret($token->secret);
        })->afterCreating(function (Token $token) {
            $token->save(); // save the updated secret to the database, don't save in make to avoid confusion
        });
    }
}
