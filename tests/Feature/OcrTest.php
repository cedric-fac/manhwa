<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\HandlesStorage;
use App\Models\User;
use App\Models\Client;
use App\Models\Reading;
use App\Models\OcrTrainingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class OcrTest extends TestCase
{
    use RefreshDatabase, HandlesStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupTestStorage();

        // Create admin user
        $this->admin = User::factory()->create([
            'is_admin' => true
        ]);

        // Create regular user
        $this->user = User::factory()->create([
            'is_admin' => false
        ]);

        // Create test client
        $this->client = Client::factory()->create();
    }

    #[Test]
    public function only_admin_can_access_ocr_dashboard(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('ocr.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)
            ->get(route('ocr.dashboard'));
        $response->assertStatus(302)
            ->assertRedirect(route('dashboard'));
    }

    #[Test]
    public function it_stores_ocr_training_data_with_reading(): void
    {
        $file = $this->createFakeMeterImage();

        $response = $this->actingAs($this->admin)
            ->post(route('readings.store', $this->client->id), [
                'value' => '12345',
                'read_at' => now()->format('Y-m-d'),
                'photo' => $file,
                'ocr_data' => [
                    'confidence' => 85.5,
                    'text' => '12345',
                    'suggestions' => ['12345', '12346']
                ]
            ]);

        $response->assertRedirect();

        // Assert reading was created
        $reading = Reading::latest()->first();
        $this->assertNotNull($reading);
        $this->assertEquals('12345', $reading->value);

        // Assert OCR training data was created
        $this->assertDatabaseHas('ocr_training_data', [
            'reading_id' => $reading->id,
            'original_text' => '12345',
            'confidence' => 85.5,
            'verified' => false
        ]);

        // Assert file was stored
        $this->assertStorageHasFile('meter-readings/' . $this->client->id . '/' . $file->hashName());
    }

    #[Test]
    public function admin_can_review_ocr_training_data(): void
    {
        $reading = Reading::factory()->create([
            'client_id' => $this->client->id
        ]);

        $trainingData = OcrTrainingData::create([
            'reading_id' => $reading->id,
            'original_text' => '12345',
            'corrected_text' => null,
            'confidence' => 75.5,
            'metadata' => ['suggestions' => ['12345', '12346']],
            'image_url' => 'https://example.com/image.jpg',
            'verified' => false
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('ocr.update', $trainingData->id), [
                'corrected_text' => '12346',
                'feedback' => 'Correction of last digit'
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('ocr_training_data', [
            'id' => $trainingData->id,
            'corrected_text' => '12346',
            'verified' => true
        ]);
    }

    #[Test]
    public function it_sends_notification_for_low_confidence_results(): void
    {
        $reading = Reading::factory()->create([
            'client_id' => $this->client->id
        ]);

        $trainingData = OcrTrainingData::create([
            'reading_id' => $reading->id,
            'original_text' => '12345',
            'corrected_text' => null,
            'confidence' => 65.5, // Low confidence
            'metadata' => ['suggestions' => ['12345', '12346']],
            'image_url' => 'https://example.com/image.jpg',
            'verified' => false
        ]);

        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\OcrReviewNeededNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->admin->id
        ]);
    }

    #[Test]
    public function it_generates_performance_report(): void
    {
        // Create some test data
        Reading::factory()
            ->count(5)
            ->create(['client_id' => $this->client->id])
            ->each(function ($reading) {
                OcrTrainingData::factory()->create([
                    'reading_id' => $reading->id,
                    'confidence' => rand(60, 95)
                ]);
            });

        $this->artisan('ocr:report', [
            '--days' => 7,
            '--email' => $this->admin->email
        ])->assertSuccessful();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestStorage();
        parent::tearDown();
    }
}