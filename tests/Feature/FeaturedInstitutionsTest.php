<?php

namespace Tests\Feature;

use App\Models\FeaturedInstitution;
use App\Models\Institution;
use App\Models\QrCode;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class FeaturedInstitutionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed payment methods since QR codes need them
        PaymentMethod::factory()->create(['name' => 'TnG', 'code' => 'tng']);
    }

    /** @test */
    public function test_featured_institutions_display_on_homepage()
    {
        // Create 3 verified institutions
        $inst1 = Institution::factory()->create(['name' => 'Masjid A', 'verified_at' => now()]);
        $inst2 = Institution::factory()->create(['name' => 'Masjid B', 'verified_at' => now()]);
        $inst3 = Institution::factory()->create(['name' => 'Masjid C', 'verified_at' => now()]);

        // Add QR codes to make them valid
        $inst1->qrCodes()->create([
            'payment_method_id' => PaymentMethod::first()->id,
            'status' => 'active',
        ]);
        $inst2->qrCodes()->create([
            'payment_method_id' => PaymentMethod::first()->id,
            'status' => 'active',
        ]);

        // Add them as featured in order
        FeaturedInstitution::create(['institution_id' => $inst1->id, 'order' => 1]);
        FeaturedInstitution::create(['institution_id' => $inst2->id, 'order' => 2]);
        FeaturedInstitution::create(['institution_id' => $inst3->id, 'order' => 3]);

        // Clear cache
        Cache::forget('homepage_featured');

        // Get homepage
        $response = $this->get('/');
        $response->assertStatus(200);

        // Check featured institutions are in props
        $response->assertInertia(fn (AssertableInertia $page) =>
            $page
                ->component('Welcome')
                ->has('featured', 3)
                ->where('featured.0.name', 'Masjid A')
                ->where('featured.1.name', 'Masjid B')
                ->where('featured.2.name', 'Masjid C')
        );
    }

    /** @test */
    public function test_featured_institutions_update_clears_cache()
    {
        $this->actingAsAdmin();

        $inst1 = Institution::factory()->create(['name' => 'Masjid A', 'verified_at' => now()]);
        $inst2 = Institution::factory()->create(['name' => 'Masjid B', 'verified_at' => now()]);

        // Prime the cache with old data
        Cache::put('homepage_featured', collect([$inst1]), 600);

        // Update featured institutions
        $response = $this->post(route('admin.featured.update'), [
            'institution_ids' => [$inst2->id],
        ]);

        $response->assertRedirect();

        // Cache should be cleared
        $this->assertNull(Cache::get('homepage_featured'));

        // Get fresh data from DB
        $featured = FeaturedInstitution::with('institution')->ordered()->get();
        $this->assertCount(1, $featured);
        $this->assertEquals($inst2->id, $featured[0]->institution_id);
    }

    /** @test */
    public function test_featured_institutions_reorder()
    {
        $this->actingAsAdmin();

        $inst1 = Institution::factory()->create(['name' => 'Masjid A', 'verified_at' => now()]);
        $inst2 = Institution::factory()->create(['name' => 'Masjid B', 'verified_at' => now()]);
        $inst3 = Institution::factory()->create(['name' => 'Masjid C', 'verified_at' => now()]);

        // Set initial order: 1, 2, 3
        $this->post(route('admin.featured.update'), [
            'institution_ids' => [$inst1->id, $inst2->id, $inst3->id],
        ]);

        // Reorder to: 3, 1, 2
        $this->post(route('admin.featured.update'), [
            'institution_ids' => [$inst3->id, $inst1->id, $inst2->id],
        ]);

        $featured = FeaturedInstitution::ordered()->get();
        $this->assertEquals($inst3->id, $featured[0]->institution_id);
        $this->assertEquals($inst1->id, $featured[1]->institution_id);
        $this->assertEquals($inst2->id, $featured[2]->institution_id);
        $this->assertEquals(1, $featured[0]->order);
        $this->assertEquals(2, $featured[1]->order);
        $this->assertEquals(3, $featured[2]->order);
    }

    /** @test */
    public function test_remove_featured_institutions()
    {
        $this->actingAsAdmin();

        $inst1 = Institution::factory()->create(['name' => 'Masjid A', 'verified_at' => now()]);
        $inst2 = Institution::factory()->create(['name' => 'Masjid B', 'verified_at' => now()]);
        $inst3 = Institution::factory()->create(['name' => 'Masjid C', 'verified_at' => now()]);

        // Add all 3
        $this->post(route('admin.featured.update'), [
            'institution_ids' => [$inst1->id, $inst2->id, $inst3->id],
        ]);

        $this->assertCount(3, FeaturedInstitution::all());

        // Remove all but first
        $this->post(route('admin.featured.update'), [
            'institution_ids' => [$inst1->id],
        ]);

        $this->assertCount(1, FeaturedInstitution::all());
        $this->assertEquals($inst1->id, FeaturedInstitution::first()->institution_id);
    }

    /** @test */
    public function test_remove_all_featured_institutions()
    {
        $this->actingAsAdmin();

        $inst = Institution::factory()->create(['name' => 'Masjid A', 'verified_at' => now()]);

        // Add 1
        $this->post(route('admin.featured.update'), [
            'institution_ids' => [$inst->id],
        ]);

        $this->assertCount(1, FeaturedInstitution::all());

        // Remove all (empty array)
        $response = $this->post(route('admin.featured.update'), [
            'institution_ids' => [],
        ]);

        $response->assertRedirect();
        $this->assertCount(0, FeaturedInstitution::all());
    }

    /** @test */
    public function test_featured_institutions_empty_when_none_set()
    {
        // Don't add any featured institutions
        Cache::forget('homepage_featured');

        $response = $this->get('/');
        $response->assertStatus(200);

        // Should be empty, no fallback
        $response->assertInertia(fn (AssertableInertia $page) =>
            $page
                ->component('Welcome')
                ->has('featured', 0)  // Empty array
        );
    }

    /** @test */
    public function test_featured_institution_without_qr_code_still_shows()
    {
        // Create verified institution without QR code
        $inst = Institution::factory()->create(['name' => 'Orphanage Without QR', 'verified_at' => now()]);
        FeaturedInstitution::create(['institution_id' => $inst->id, 'order' => 1]);

        Cache::forget('homepage_featured');

        $response = $this->get('/');

        // Should still appear in featured list even without QR
        $response->assertInertia(fn (AssertableInertia $page) =>
            $page
                ->component('Welcome')
                ->has('featured')
                ->where('featured.0.name', 'Orphanage Without QR')
        );
    }

    /** @test */
    public function test_cannot_feature_unverified_institutions()
    {
        $this->actingAsAdmin();

        $verified = Institution::factory()->create(['name' => 'Verified Masjid', 'verified_at' => now()]);
        $unverified = Institution::factory()->create(['name' => 'Unverified Masjid', 'verified_at' => null]);

        // Try to add unverified institution
        $response = $this->post(route('admin.featured.update'), [
            'institution_ids' => [$verified->id, $unverified->id],
        ]);

        // Should redirect back with error
        $response->assertRedirect();
        $response->assertSessionHasErrors('institution_ids');
        $this->assertCount(0, FeaturedInstitution::all());
    }

    /** @test */
    public function test_featured_index_only_shows_verified_institutions()
    {
        $this->actingAsAdmin();

        Institution::factory()->create(['name' => 'Verified 1', 'verified_at' => now()]);
        Institution::factory()->create(['name' => 'Verified 2', 'verified_at' => now()]);
        Institution::factory()->create(['name' => 'Unverified', 'verified_at' => null]);

        $response = $this->get(route('admin.featured.edit'));

        $response->assertInertia(fn (AssertableInertia $page) =>
            $page
                ->component('Admin/Featured/Edit')
                ->has('institutions', 2)  // Only 2 verified
        );
    }

    // Helper to act as admin
    protected function actingAsAdmin()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin);
        return $this;
    }
}
