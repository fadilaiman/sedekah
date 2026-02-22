<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    private PaymentMethod $pm;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Seed categories
        $this->seed(\Database\Seeders\CategorySeeder::class);

        $this->pm = PaymentMethod::create(['code' => 'tng', 'name' => 'Touch n Go', 'active' => true]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'submitter_email'      => 'user@example.com',
            'institution_name'     => 'Masjid Al-Amin',
            'institution_category' => 'mosque',
            'institution_state'    => 'Selangor',
            'institution_city'     => 'Petaling Jaya',
            'qr_image'             => UploadedFile::fake()->image('qr.png', 100, 100)->size(50),
            'payment_method_id'    => $this->pm->id,
        ], $overrides);
    }

    public function test_valid_submission_creates_pending_record(): void
    {
        $response = $this->postJson('/api/v1/submissions', $this->validPayload());

        $response->assertCreated()
            ->assertJsonFragment(['message' => 'Submission received. An admin will review it shortly.']);

        $this->assertDatabaseHas('submissions', [
            'institution_name' => 'Masjid Al-Amin',
            'status'           => 'pending',
        ]);
    }

    public function test_submission_requires_email(): void
    {
        $response = $this->postJson('/api/v1/submissions', $this->validPayload(['submitter_email' => '']));
        $response->assertUnprocessable()->assertJsonValidationErrors(['submitter_email']);
    }

    public function test_submission_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/submissions', $this->validPayload(['submitter_email' => 'not-an-email']));
        $response->assertUnprocessable()->assertJsonValidationErrors(['submitter_email']);
    }

    public function test_submission_requires_institution_name(): void
    {
        $response = $this->postJson('/api/v1/submissions', $this->validPayload(['institution_name' => '']));
        $response->assertUnprocessable()->assertJsonValidationErrors(['institution_name']);
    }

    public function test_submission_requires_valid_category(): void
    {
        $response = $this->postJson('/api/v1/submissions', $this->validPayload(['institution_category' => 'invalid_category']));
        $response->assertUnprocessable()->assertJsonValidationErrors(['institution_category']);
    }

    public function test_submission_requires_existing_payment_method(): void
    {
        $response = $this->postJson('/api/v1/submissions', $this->validPayload(['payment_method_id' => 9999]));
        $response->assertUnprocessable()->assertJsonValidationErrors(['payment_method_id']);
    }

    public function test_submission_requires_state_and_city(): void
    {
        $response = $this->postJson('/api/v1/submissions', $this->validPayload([
            'institution_state' => '',
            'institution_city'  => '',
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['institution_state', 'institution_city']);
    }

    public function test_submission_requires_qr_image(): void
    {
        $payload = $this->validPayload();
        unset($payload['qr_image']);

        $response = $this->postJson('/api/v1/submissions', $payload);
        $response->assertUnprocessable()->assertJsonValidationErrors(['qr_image']);
    }

    public function test_qr_image_stored_on_valid_submission(): void
    {
        $this->postJson('/api/v1/submissions', $this->validPayload());

        Storage::disk('public')->assertExists(
            collect(Storage::disk('public')->allFiles('submissions/qr'))->first()
        );
    }

    public function test_all_valid_categories_are_accepted(): void
    {
        // Bypass rate limiting so we can test multiple categories in one test
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $categories = ['mosque', 'surau', 'other'];

        foreach ($categories as $cat) {
            $response = $this->postJson('/api/v1/submissions', $this->validPayload([
                'institution_name'     => "Test {$cat}",
                'institution_category' => $cat,
                'qr_image'             => UploadedFile::fake()->image('qr.png')->size(50),
            ]));

            $response->assertCreated("Category '{$cat}' should be accepted");
        }
    }
}
