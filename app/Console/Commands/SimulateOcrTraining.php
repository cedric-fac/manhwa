<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Reading;
use App\Models\OcrTrainingData;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SimulateOcrTraining extends Command
{
    protected $signature = 'ocr:simulate {--samples=10 : Number of samples to generate}';
    protected $description = 'Simulate OCR training data generation and review process';

    public function handle()
    {
        $this->info('Starting OCR training simulation...');

        // Ensure we have an admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create a test client if none exists
        $client = Client::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Client',
                'phone' => '+237612345678',
                'address' => '123 Test Street',
                'tva_rate' => 19.25,
            ]
        );

        $samples = (int) $this->option('samples');
        $bar = $this->output->createProgressBar($samples);
        $bar->start();

        // Generate test readings and OCR data
        for ($i = 0; $i < $samples; $i++) {
            $reading = Reading::create([
                'client_id' => $client->id,
                'value' => rand(1000, 9999),
                'read_at' => now()->subDays(rand(1, 30)),
                'photo_url' => 'https://example.com/meter.jpg',
                'synced' => true,
                'metadata' => [
                    'ocr' => [
                        'processed_at' => now()->toIso8601String(),
                        'duration_ms' => rand(100, 1000)
                    ]
                ]
            ]);

            // Create OCR training data with varying confidence levels
            OcrTrainingData::create([
                'reading_id' => $reading->id,
                'original_text' => (string) $reading->value,
                'corrected_text' => rand(0, 1) ? (string) $reading->value : null,
                'confidence' => rand(60, 100),
                'metadata' => [
                    'suggestions' => [
                        (string) $reading->value,
                        (string) ($reading->value + rand(-100, 100))
                    ],
                    'processing_time' => rand(100, 500),
                ],
                'image_url' => $reading->photo_url,
                'verified' => (bool) rand(0, 1)
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Display statistics
        $stats = OcrTrainingData::getStatistics();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Samples', $stats['total']],
                ['Verified', $stats['verified']],
                ['Low Confidence', $stats['low_confidence']],
                ['Average Confidence', number_format($stats['avg_confidence'], 2) . '%'],
                ['Needs Review', $stats['needs_review']],
            ]
        );

        // Generate performance report
        $this->info('Generating performance report...');
        $this->call('ocr:report', [
            '--days' => 30,
            '--email' => $admin->email
        ]);

        $this->info('OCR training simulation completed successfully!');
    }
}