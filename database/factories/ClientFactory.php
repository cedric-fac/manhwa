<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->e164PhoneNumber(),
            'address' => $this->faker->address(),
            'tva_rate' => $this->faker->randomFloat(2, 5.5, 20),
        ];
    }

    /**
     * Indicate that the client has no email.
     */
    public function withoutEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => null,
        ]);
    }

    /**
     * Indicate that the client has standard TVA rate.
     */
    public function standardTva(): static
    {
        return $this->state(fn (array $attributes) => [
            'tva_rate' => 20.0,
        ]);
    }

    /**
     * Indicate that the client has reduced TVA rate.
     */
    public function reducedTva(): static
    {
        return $this->state(fn (array $attributes) => [
            'tva_rate' => 5.5,
        ]);
    }
}