<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutenticacionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_revokes_token()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $token = $user->createToken('test-token')->plainTextToken;

        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
                               ->postJson("/api/v1/auth/logout");

        $logoutResponse->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson("/api/v1/animales");

        $response->assertUnauthorized();
    }

    public function test_login_brute_force_protection()
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson("/api/v1/auth/login", [
                'email' => 'fake@example.com',
                'password' => 'wrongpass'
            ]);
        }

        $response->assertStatus(429);
    }
}
