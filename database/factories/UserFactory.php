<?php

namespace Database\Factories;

use App\Models\User;
use Defuse\Crypto\KeyProtectedByPassword;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'protected_key_encoded' => (KeyProtectedByPassword::createRandomPasswordProtectedKey('password'))->saveToAsciiSafeString(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (User $user) {
            $user->putEncryptionKeyInSession('password'); // password from the factory
        });
    }
}
