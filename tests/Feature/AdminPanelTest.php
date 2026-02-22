<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Institution;
use App\Models\PaymentMethod;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed categories
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_dashboard_requires_auth(): void
    {
        $response = $this->withoutVite()->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_dashboard_accessible_to_admin(): void
    {
        $admin = $this->makeAdmin();
        $response = $this->withoutVite()->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
    }

    public function test_institutions_index_returns_paginated_data(): void
    {
        Institution::factory()->count(3)->create();
        $admin = $this->makeAdmin();
        $response = $this->withoutVite()->actingAs($admin)->get(route('admin.institutions.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Institutions/Index')
            ->has('institutions.data')
        );
    }

    public function test_institution_create_page_loads(): void
    {
        PaymentMethod::factory()->count(2)->create(['active' => true]);
        $admin = $this->makeAdmin();
        $response = $this->withoutVite()->actingAs($admin)->get(route('admin.institutions.create'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Institutions/Create'));
    }

    public function test_admin_can_create_institution(): void
    {
        $admin = $this->makeAdmin();
        $response = $this->actingAs($admin)->post(route('admin.institutions.store'), [
            'name'     => 'Masjid Uji Coba',
            'category' => 'mosque',
            'state'    => 'Selangor',
            'city'     => 'Shah Alam',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('institutions', ['name' => 'Masjid Uji Coba']);
    }

    public function test_admin_can_update_institution(): void
    {
        $admin = $this->makeAdmin();
        $inst = Institution::factory()->create(['name' => 'Lama', 'category' => 'mosque', 'state' => 'Selangor', 'city' => 'PJ']);
        $response = $this->actingAs($admin)->put(route('admin.institutions.update', $inst->id), [
            'name'     => 'Baru',
            'category' => 'mosque',
            'state'    => 'Selangor',
            'city'     => 'PJ',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('institutions', ['id' => $inst->id, 'name' => 'Baru']);
    }

    public function test_admin_can_soft_delete_institution(): void
    {
        $admin = $this->makeAdmin();
        $inst = Institution::factory()->create();
        $response = $this->actingAs($admin)->delete(route('admin.institutions.destroy', $inst->id));
        $response->assertRedirect(route('admin.institutions.index'));
        $this->assertSoftDeleted('institutions', ['id' => $inst->id]);
    }

    public function test_submissions_index_accessible(): void
    {
        $admin = $this->makeAdmin();
        $response = $this->withoutVite()->actingAs($admin)->get(route('admin.submissions.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Submissions/Index'));
    }

    public function test_admin_can_approve_submission(): void
    {
        $admin = $this->makeAdmin();
        PaymentMethod::factory()->create();
        $submission = Submission::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin)->post(route('admin.submissions.approve', $submission->id));
        $response->assertRedirect();
        $this->assertDatabaseHas('submissions', ['id' => $submission->id, 'status' => 'approved']);
        $this->assertDatabaseHas('institutions', ['name' => $submission->institution_name]);
    }

    public function test_admin_can_reject_submission(): void
    {
        $admin = $this->makeAdmin();
        $submission = Submission::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin)->post(route('admin.submissions.reject', $submission->id), [
            'reason' => 'Duplikasi',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('submissions', [
            'id'               => $submission->id,
            'status'           => 'rejected',
            'rejection_reason' => 'Duplikasi',
        ]);
    }
}
