<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\PaymentMethod;
use App\Models\QrCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed a payment method for tests
        PaymentMethod::create([
            'code' => 'touch_n_go',
            'name' => 'Touch n Go',
            'active' => true,
        ]);
    }

    public function test_institutions_list_returns_json(): void
    {
        Institution::create([
            'name' => 'Test Masjid',
            'category' => 'mosque',
            'state' => 'Selangor',
            'city' => 'Petaling Jaya',
            'verified_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/institutions');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'category', 'state', 'city', 'slug']],
                'total',
            ]);
    }

    public function test_institutions_can_be_filtered_by_category(): void
    {
        Institution::create(['name' => 'Masjid A', 'category' => 'mosque', 'state' => 'KL', 'city' => 'KL', 'verified_at' => now()]);
        Institution::create(['name' => 'Surau B', 'category' => 'surau', 'state' => 'KL', 'city' => 'KL', 'verified_at' => now()]);

        $response = $this->getJson('/api/v1/institutions?category=mosque');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('mosque', $response->json('data.0.category'));
    }

    public function test_institutions_can_be_searched(): void
    {
        Institution::create(['name' => 'Al-Hidayah Mosque', 'category' => 'mosque', 'state' => 'KL', 'city' => 'KL', 'verified_at' => now()]);
        Institution::create(['name' => 'Community Other', 'category' => 'other', 'state' => 'KL', 'city' => 'KL', 'verified_at' => now()]);

        $response = $this->getJson('/api/v1/institutions?search=Hidayah');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_institution_detail_returns_full_data(): void
    {
        $institution = Institution::create([
            'name' => 'Test Institution',
            'category' => 'mosque',
            'state' => 'Selangor',
            'city' => 'Shah Alam',
        ]);

        $response = $this->getJson("/api/v1/institutions/{$institution->id}");

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Test Institution']);
    }

    public function test_institution_can_be_retrieved_by_slug(): void
    {
        $institution = Institution::create([
            'name' => 'Unique Mosque',
            'category' => 'mosque',
            'state' => 'Penang',
            'city' => 'Georgetown',
        ]);

        $response = $this->getJson("/api/v1/institutions/slug/{$institution->slug}");

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Unique Mosque']);
    }

    public function test_institution_detail_includes_active_qr_codes(): void
    {
        $institution = Institution::create([
            'name' => 'Mosque With QR',
            'category' => 'mosque',
            'state' => 'KL',
            'city' => 'KL',
        ]);

        $paymentMethod = PaymentMethod::first();

        QrCode::create([
            'institution_id' => $institution->id,
            'payment_method_id' => $paymentMethod->id,
            'qr_image_url' => '/storage/qr/test.png',
            'qr_content' => 'https://payment.example.com',
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/v1/institutions/{$institution->id}");

        $response->assertOk()
            ->assertJsonStructure(['qr_codes' => [['id', 'status', 'payment_method']]]);
    }

    public function test_slug_auto_generated_on_create(): void
    {
        $institution = Institution::create([
            'name' => 'My Test Mosque',
            'category' => 'mosque',
            'state' => 'KL',
            'city' => 'KL',
        ]);

        $this->assertEquals('my-test-mosque', $institution->slug);
    }

    public function test_slug_is_unique_with_suffix(): void
    {
        Institution::create(['name' => 'Al-Amin', 'category' => 'mosque', 'state' => 'KL', 'city' => 'KL']);
        $second = Institution::create(['name' => 'Al-Amin', 'category' => 'surau', 'state' => 'Selangor', 'city' => 'PJ']);

        $this->assertEquals('al-amin-1', $second->slug);
    }
}
