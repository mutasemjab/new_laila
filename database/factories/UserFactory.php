<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $gender = rand(0,1);
        return [
            'name'  => fake()->firstName($gender),
            'company'     => fake()->company(),
            'country'     => fake()->country(),
            'email'       => fake()->safeEmail(),
            'gender'      => ($gender ? 2 : 1),
            'phone'       => fake()->phoneNumber(),
            'barcode'     => fake()->ean13(),
            'activate'    => 1,// array_rand([1,1,1,1,0]),
            'category'    => rand(1,6),
            // 'email_verified_at' => now(),
            // 'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
