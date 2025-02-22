<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use App\Models\Reading;
use App\Models\OcrTrainingData;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Create regular user
        User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        // Create test client
        $client = Client::create([
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'phone' => '+237612345678',
            'address' => '123 Test Street',
            'tva_rate' => 19.25,
        ]);

        // Create test readings with OCR training data
        $readings = [];
        $confidenceLevels = [65, 75, 85, 95];
        
        for ($i = 1; $i <= 4; $i++) {
            $reading = Reading::create([
                'client_id' => $client->id,
                'value' => 1000 * $i,
                'read_at' => now()->subMonths(5 - $i),
                'photo_url' => "https://res.cloudinary.com/demo/image/upload/sample.jpg",
                'synced' => true,
            ]);

            $readings[] = $reading;

            // Create OCR training data with varying confidence levels
            OcrTrainingData::create([
                'reading_id' => $reading->id,
                'original_text' => (string)(1000 * $i),
                'corrected_text' => $i < 3 ? (string)(1000 * $i) : null,
                'confidence' => $confidenceLevels[$i - 1],
                'metadata' => [
                    'processing_time' => rand(100, 500),
                    'numbers' => [(1000 * $i), (1000 * $i + rand(-100, 100))],
                    'processed_at' => now()->toIso8601String(),
                ],
                'image_url' => $reading->photo_url,
                'verified' => $i < 3, // First two are verified
            ]);
        }

        // Log seeding completion
        \Log::info('Database seeded successfully with test data for OCR training.');
    }
}
