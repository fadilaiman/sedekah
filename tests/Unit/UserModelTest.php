<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_role_is_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($user->isAdmin());
    }

    public function test_super_admin_role_is_admin(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $this->assertTrue($user->isAdmin());
    }

    public function test_super_admin_is_super_admin(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_admin_role_is_not_super_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_user_can_be_soft_deleted(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertNull(User::find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
    }

    public function test_password_is_hidden_from_serialization(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }
}
