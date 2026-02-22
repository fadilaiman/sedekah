<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PaymentMethod::create(['code' => 'tng', 'name' => 'Touch n Go', 'active' => true]);

        Institution::create(['name' => 'Alpha Mosque',  'category' => 'mosque', 'state' => 'Selangor',     'city' => 'Shah Alam',      'verified_at' => now()]);
        Institution::create(['name' => 'Beta Surau',    'category' => 'surau',  'state' => 'Selangor',     'city' => 'Petaling Jaya', 'verified_at' => now()]);
        Institution::create(['name' => 'Gamma Other',   'category' => 'other',  'state' => 'Kuala Lumpur', 'city' => 'Chow Kit',       'verified_at' => now()]);
        Institution::create(['name' => 'Delta Mosque',  'category' => 'mosque', 'state' => 'Penang',       'city' => 'Georgetown',    'verified_at' => now()]);
    }

    public function test_filter_by_state(): void
    {
        $response = $this->getJson('/api/v1/institutions?state=Selangor');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertTrue(collect($response->json('data'))->every(fn ($i) => $i['state'] === 'Selangor'));
    }

    public function test_filter_by_category_and_state_combined(): void
    {
        $response = $this->getJson('/api/v1/institutions?category=mosque&state=Selangor');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Alpha Mosque', $response->json('data.0.name'));
    }

    public function test_sort_by_name_ascending(): void
    {
        $response = $this->getJson('/api/v1/institutions?sort=name&direction=asc');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->toArray();
        $sorted = $names;
        sort($sorted);
        $this->assertEquals($sorted, $names);
    }

    public function test_sort_by_name_descending(): void
    {
        $response = $this->getJson('/api/v1/institutions?sort=name&direction=desc');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->toArray();
        $sorted = $names;
        rsort($sorted);
        $this->assertEquals($sorted, $names);
    }

    public function test_pagination_limits_results(): void
    {
        $response = $this->getJson('/api/v1/institutions?per_page=2');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals(4, $response->json('total'));
    }

    public function test_returns_all_institutions_with_no_filter(): void
    {
        $response = $this->getJson('/api/v1/institutions');

        $response->assertOk();
        $this->assertCount(4, $response->json('data'));
        $this->assertEquals(4, $response->json('total'));
    }

    public function test_nonexistent_institution_id_returns_404(): void
    {
        $response = $this->getJson('/api/v1/institutions/99999');
        $response->assertNotFound();
    }

    public function test_nonexistent_slug_returns_404(): void
    {
        $response = $this->getJson('/api/v1/institutions/slug/nonexistent-slug');
        $response->assertNotFound();
    }

    public function test_search_returns_empty_for_no_match(): void
    {
        $response = $this->getJson('/api/v1/institutions?search=ZZZNonexistent');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
        $this->assertEquals(0, $response->json('total'));
    }
}
