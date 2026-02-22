<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_methods_returns_json(): void
    {
        PaymentMethod::create(['code' => 'tng', 'name' => 'Touch n Go', 'active' => true]);

        $response = $this->getJson('/api/v1/payment-methods');

        $response->assertOk()
            ->assertJsonStructure([['id', 'code', 'name', 'active']]);
    }

    public function test_only_active_payment_methods_returned(): void
    {
        PaymentMethod::create(['code' => 'tng', 'name' => 'Touch n Go', 'active' => true]);
        PaymentMethod::create(['code' => 'grab', 'name' => 'GrabPay', 'active' => false]);
        PaymentMethod::create(['code' => 'boost', 'name' => 'Boost', 'active' => true]);

        $response = $this->getJson('/api/v1/payment-methods');

        $response->assertOk();
        $this->assertCount(2, $response->json());
        $this->assertTrue(collect($response->json())->every(fn ($m) => $m['active'] === true));
    }

    public function test_payment_methods_returned_in_alphabetical_order(): void
    {
        PaymentMethod::create(['code' => 'tng', 'name' => 'Touch n Go', 'active' => true]);
        PaymentMethod::create(['code' => 'boost', 'name' => 'Boost', 'active' => true]);
        PaymentMethod::create(['code' => 'grab', 'name' => 'GrabPay', 'active' => true]);

        $response = $this->getJson('/api/v1/payment-methods');
        $names = collect($response->json())->pluck('name')->toArray();

        $this->assertEquals(['Boost', 'GrabPay', 'Touch n Go'], $names);
    }

    public function test_returns_empty_array_when_no_active_methods(): void
    {
        PaymentMethod::create(['code' => 'inactive', 'name' => 'Inactive Method', 'active' => false]);

        $response = $this->getJson('/api/v1/payment-methods');

        $response->assertOk();
        $this->assertCount(0, $response->json());
    }
}
