<?php

namespace Tests\Unit;

use App\Models\Institution;
use App\Models\PaymentMethod;
use App\Models\QrCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionModelTest extends TestCase
{
    use RefreshDatabase;

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function test_scope_verified_returns_only_verified(): void
    {
        Institution::create(['name' => 'Verified', 'category' => 'mosque', 'state' => 'KL', 'city' => 'KL', 'verified_at' => now()]);
        Institution::create(['name' => 'Unverified', 'category' => 'mosque', 'state' => 'KL', 'city' => 'KL']);

        $results = Institution::verified()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Verified', $results->first()->name);
    }

    public function test_scope_by_category_filters_correctly(): void
    {
        Institution::create(['name' => 'Mosque A', 'category' => 'mosque', 'state' => 'KL', 'city' => 'KL']);
        Institution::create(['name' => 'Surau B', 'category' => 'surau', 'state' => 'KL', 'city' => 'KL']);
        Institution::create(['name' => 'Mosque C', 'category' => 'mosque', 'state' => 'KL', 'city' => 'KL']);

        $results = Institution::byCategory('mosque')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($i) => $i->category === 'mosque'));
    }

    public function test_scope_by_state_filters_correctly(): void
    {
        Institution::create(['name' => 'KL Mosque', 'category' => 'mosque', 'state' => 'Kuala Lumpur', 'city' => 'KL']);
        Institution::create(['name' => 'PG Mosque', 'category' => 'mosque', 'state' => 'Penang', 'city' => 'Georgetown']);

        $results = Institution::byState('Penang')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('PG Mosque', $results->first()->name);
    }

    public function test_scope_by_city_filters_correctly(): void
    {
        Institution::create(['name' => 'PJ Mosque', 'category' => 'mosque', 'state' => 'Selangor', 'city' => 'Petaling Jaya']);
        Institution::create(['name' => 'SA Mosque', 'category' => 'mosque', 'state' => 'Selangor', 'city' => 'Shah Alam']);

        $results = Institution::byCity('Petaling Jaya')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('PJ Mosque', $results->first()->name);
    }

    public function test_scope_search_matches_name(): void
    {
        Institution::create(['name' => 'Al-Hidayah Mosque', 'category' => 'mosque', 'state' => 'KL', 'city' => 'KL']);
        Institution::create(['name' => 'Community Center', 'category' => 'other', 'state' => 'KL', 'city' => 'KL']);

        $results = Institution::search('Hidayah')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Al-Hidayah Mosque', $results->first()->name);
    }

    public function test_scope_search_matches_city(): void
    {
        Institution::create(['name' => 'Mosque A', 'category' => 'mosque', 'state' => 'Selangor', 'city' => 'Klang']);
        Institution::create(['name' => 'Mosque B', 'category' => 'mosque', 'state' => 'Penang', 'city' => 'Georgetown']);

        $results = Institution::search('Klang')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Mosque A', $results->first()->name);
    }

    // ── is_verified accessor/mutator ──────────────────────────────────────────

    public function test_is_verified_is_true_when_verified_at_set(): void
    {
        $institution = Institution::create([
            'name' => 'Verified Mosque',
            'category' => 'mosque',
            'state' => 'KL',
            'city' => 'KL',
            'verified_at' => now(),
        ]);

        $this->assertTrue($institution->is_verified);
    }

    public function test_is_verified_is_false_when_verified_at_null(): void
    {
        $institution = Institution::create([
            'name' => 'Unverified Mosque',
            'category' => 'mosque',
            'state' => 'KL',
            'city' => 'KL',
        ]);

        $this->assertFalse($institution->is_verified);
    }

    public function test_setting_is_verified_true_sets_verified_at(): void
    {
        $institution = Institution::create([
            'name' => 'Test Mosque',
            'category' => 'mosque',
            'state' => 'KL',
            'city' => 'KL',
        ]);

        $institution->is_verified = true;
        $institution->save();

        $this->assertNotNull($institution->fresh()->verified_at);
    }

    public function test_setting_is_verified_false_clears_verified_at(): void
    {
        $institution = Institution::create([
            'name' => 'Test Mosque',
            'category' => 'mosque',
            'state' => 'KL',
            'city' => 'KL',
            'verified_at' => now(),
        ]);

        $institution->is_verified = false;
        $institution->save();

        $this->assertNull($institution->fresh()->verified_at);
    }

    // ── Soft deletes ──────────────────────────────────────────────────────────

    public function test_soft_delete_excludes_from_default_query(): void
    {
        $institution = Institution::create([
            'name' => 'To Be Deleted',
            'category' => 'mosque',
            'state' => 'KL',
            'city' => 'KL',
        ]);

        $institution->delete();

        $this->assertNull(Institution::find($institution->id));
        $this->assertNotNull(Institution::withTrashed()->find($institution->id));
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function test_active_qr_codes_returns_only_active(): void
    {
        // Two different payment methods required — unique constraint on (institution_id, payment_method_id)
        $pm1 = PaymentMethod::create(['code' => 'tng', 'name' => 'Touch n Go', 'active' => true]);
        $pm2 = PaymentMethod::create(['code' => 'grab', 'name' => 'GrabPay', 'active' => true]);

        $institution = Institution::create([
            'name' => 'Mosque With QR',
            'category' => 'mosque',
            'state' => 'KL',
            'city' => 'KL',
        ]);

        QrCode::create(['institution_id' => $institution->id, 'payment_method_id' => $pm1->id, 'status' => 'active']);
        QrCode::create(['institution_id' => $institution->id, 'payment_method_id' => $pm2->id, 'status' => 'inactive']);

        $this->assertCount(2, $institution->qrCodes);
        $this->assertCount(1, $institution->activeQrCodes);
    }
}
