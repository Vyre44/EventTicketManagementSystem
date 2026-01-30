<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
/**
 * UserFactory
 *
 * Test ve seed işlemleri için kullanıcı üretir.
 * Varsayılan olarak attendee rolünde ve "password" şifresiyle gelir.
 *
 * Metotlar:
 * - definition: Varsayılan kullanıcı verisi
 * - unverified: Doğrulanmamış kullanıcı üretir
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
        /**
         * Varsayılan kullanıcı verisi
         */
         return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'attendee',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        /**
         * Kullanıcının email adresini doğrulanmamış olarak işaretler
         */
         return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
