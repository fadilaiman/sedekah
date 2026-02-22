<?php

namespace Tests\Feature;

use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebTest extends TestCase
{
    use RefreshDatabase;

    // ── Homepage ──────────────────────────────────────────────────────────────

    public function test_homepage_loads(): void
    {
        $response = $this->withoutVite()->get('/');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('Welcome'));
    }

    public function test_homepage_has_stats(): void
    {
        Institution::create(['name' => 'Test Mosque', 'category' => 'mosque', 'state' => 'KL', 'city' => 'KL']);

        $response = $this->withoutVite()->get('/');

        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('stats')
            ->has('stats.total')
            ->has('stats.by_category')
        );
    }

    public function test_homepage_has_featured_prop(): void
    {
        $response = $this->withoutVite()->get('/');

        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('featured')
        );
    }

    // ── Institution list page ─────────────────────────────────────────────────

    public function test_institution_list_page_loads(): void
    {
        $response = $this->withoutVite()->get('/institutions');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('Institution/Index'));
    }

    // ── Institution detail page ───────────────────────────────────────────────

    public function test_institution_detail_page_loads_by_slug(): void
    {
        $institution = Institution::create([
            'name'     => 'Al-Hidayah Mosque',
            'category' => 'mosque',
            'state'    => 'Selangor',
            'city'     => 'Shah Alam',
        ]);

        $response = $this->withoutVite()->get("/{$institution->slug}");

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Institution/Show')
                ->where('institution.name', 'Al-Hidayah Mosque')
            );
    }

    public function test_institution_detail_returns_404_for_unknown_slug(): void
    {
        $response = $this->withoutVite()->get('/nonexistent-mosque-slug');

        $response->assertNotFound();
    }

    // ── Submit pages ──────────────────────────────────────────────────────────

    public function test_submit_page_loads(): void
    {
        $response = $this->withoutVite()->get('/submit');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('Submit/Index'));
    }

    public function test_submit_thank_you_page_loads(): void
    {
        $response = $this->withoutVite()->get('/submit/thank-you');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page->component('Submit/ThankYou'));
    }
}
