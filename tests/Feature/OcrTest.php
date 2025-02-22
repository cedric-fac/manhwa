<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Reading;
use App\Models\OcrTrainingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class OcrTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected Client $client;

    public function setUp(): void
    {
        parent::setUp();

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

    /** @test */
    public function only_admin_can_access_ocr_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('ocr.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)
            ->get(route('ocr.dashboard'));
        $response->assertStatus(302);
    }

    /** @test */
    public function it_stores_ocr_training_data_with_reading()
    {
        Storage::fake('cloudinary');

        $file = UploadedFile::fake()->image('meter.jpg');

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
        $this->assertDatabaseHas('readings', [
            'client_id' => $this->client->id,
            'value' => '12345'
        ]);

        $reading = Reading::latest()->first();
        $this->assertDatabaseHas('ocr_training_data', [
            'reading_id' => $reading->id,
            'original_text' => '12345',
            'confidence' => 85.5
        ]);
    }

    /** @test */
    public function admin_can_review_ocr_training_data()
    {
        $reading = Reading::factory()->create([
            'client_id' => $this->client->id
        ]);

        $trainingData = OcrTrainingData::create([
            'reading_id' => $reading->id,
            'original_text' => '12345',
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

    /** @test */
    public function it_sends_notification_for_low_confidence_results()
    {
        $reading = Reading::factory()->create([
            'client_id' => $this->client->id
        ]);

        $trainingData = OcrTrainingData::create([
            'reading_id' => $reading->id,
            'original_text' => '12345',
            'confidence' => 65.5, // Low confidence
            'metadata' => ['suggestions' => ['12345', '12346']],
            'image_url' => 'https://example.com/image.jpg',
            'verified' => false
        ]);

        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\OcrReviewNeededNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->admin->id,
        ]);
    }

    /** @test */
    public function it_generates_performance_report()
    {
        $this->artisan('ocr:report', ['--days' => 7])
            ->assertSuccessful();
    }
}