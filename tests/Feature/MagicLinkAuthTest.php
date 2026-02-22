<?php

namespace Tests\Feature;

use App\Models\MagicLinkToken;
use App\Models\User;
use App\Notifications\MagicLinkNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MagicLinkAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_accessible(): void
    {
        $response = $this->withoutVite()->get(route('admin.login'));

        $response->assertOk();
    }

    public function test_magic_link_is_sent_for_existing_admin(): void
    {
        Notification::fake();

        User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $this->post(route('admin.login.send'), ['email' => 'admin@example.com'])
            ->assertSessionHas('status');

        Notification::assertSentTo(
            User::where('email', 'admin@example.com')->first(),
            MagicLinkNotification::class
        );
    }

    public function test_magic_link_not_sent_for_unknown_email(): void
    {
        Notification::fake();

        $this->post(route('admin.login.send'), ['email' => 'unknown@example.com'])
            ->assertSessionHas('status'); // Same message, prevents enumeration

        Notification::assertNothingSent();
    }

    public function test_valid_token_logs_user_in(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $token = MagicLinkToken::create([
            'email' => 'admin@example.com',
            'token' => 'valid-test-token-64chars-padded-here-xxxxxxxxxxxxxxxxxxxxxxxx',
            'expires_at' => now()->addMinutes(30),
        ]);

        $this->get(route('admin.login.verify', ['token' => $token->token]))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_expired_token_is_rejected(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $token = MagicLinkToken::create([
            'email' => 'admin@example.com',
            'token' => 'expired-token-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            'expires_at' => now()->subMinutes(5), // already expired
        ]);

        $this->get(route('admin.login.verify', ['token' => $token->token]))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_used_token_is_rejected(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $token = MagicLinkToken::create([
            'email' => 'admin@example.com',
            'token' => 'used-token-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            'expires_at' => now()->addMinutes(30),
            'used_at' => now()->subMinutes(5), // already used
        ]);

        $this->get(route('admin.login.verify', ['token' => $token->token]))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_admin_can_logout(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }
}
