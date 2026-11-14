<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
        $defaultPassword = config('auth.seed_default_password', 'password123');

        return [
            'name' => fake()->name(),
            'username' => preg_replace('/[^a-z0-9_]/', '', strtolower(fake()->unique()->userName())),
            'password' => static::$password ??= Hash::make($defaultPassword),
            'role' => 'kasir',
            'is_active' => 1,
            'credit_limit' => 0,
            'created_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
