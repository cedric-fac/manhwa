<?php

namespace Database\Factories;

use App\Models\Reading;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reading::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'value' => $this->faker->randomFloat(2, 1000, 9999),
            'read_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'photo_url' => $this->faker->imageUrl(),
            'synced' => true,
            'metadata' => [
                'ocr' => [
                    'processed_at' => now()->toIso8601String(),
                    'confidence' => $this->faker->numberBetween(60, 100),
                ]
            ],
        ];
    }

    /**
     * Indicate that the reading is not synced.
     */
    public function unsynced(): static
    {
        return $this->state(fn (array $attributes) => [
            'synced' => false,
        ]);
    }

    /**
     * Indicate that the reading has high OCR confidence.
     */
    public function highConfidence(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => [
                'ocr' => [
                    'processed_at' => now()->toIso8601String(),
                    'confidence' => $this->faker->numberBetween(90, 100),
                ]
            ],
        ]);
    }

    /**
     * Indicate that the reading has low OCR confidence.
     */
    public function lowConfidence(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => [
                'ocr' => [
                    'processed_at' => now()->toIso8601String(),
                    'confidence' => $this->faker->numberBetween(50, 75),
                ]
            ],
        ]);
    }
}