<?php

namespace Database\Factories;

use App\Models\Reading;
use App\Models\OcrTrainingData;
use Illuminate\Database\Eloquent\Factories\Factory;

class OcrTrainingDataFactory extends Factory
{
    protected $model = OcrTrainingData::class;

    public function definition(): array
    {
        $originalValue = (string)$this->faker->numberBetween(1000, 9999);
        
        return [
            'reading_id' => Reading::factory(),
            'original_text' => $originalValue,
            'corrected_text' => null,
            'confidence' => $this->faker->numberBetween(60, 100),
            'metadata' => [
                'suggestions' => [
                    $originalValue,
                    (string)($this->faker->numberBetween(1000, 9999)),
                ],
                'processing_time' => $this->faker->numberBetween(100, 1000),
                'processed_at' => now()->toIso8601String(),
            ],
            'image_url' => $this->faker->imageUrl(),
            'verified' => false,
        ];
    }

    public function verified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'verified' => true,
                'corrected_text' => $attributes['original_text'],
            ];
        });
    }

    public function lowConfidence(): static
    {
        return $this->state([
            'confidence' => $this->faker->numberBetween(50, 75),
        ]);
    }

    public function highConfidence(): static
    {
        return $this->state([
            'confidence' => $this->faker->numberBetween(90, 100),
        ]);
    }
}