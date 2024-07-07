<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test token generation and expiration.
     *
     * @return void
     */
    public function testTokenGenerationAndExpiration()
    {
        $user = User::factory()->create();

        // Generate a token with a specific expiration time (2 hours from now)
        $token = JWTAuth::fromUser($user, ['expires' => now()->addHours(2)]);

        $this->assertNotNull($token);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'userId' => $user->userId,
                    'email' => $user->email,
                ],
            ]);

        // Verify token expiration time
        $this->assertLessThanOrEqual(now()->addHours(2), $user->tokens()->first()->expires_at);
    }
    /**
     * Test organization access control.
     *
     * @return void
     */
    public function testOrganizationAccessControl()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create an organization for the first user
        $organization = $user1->organisations()->create([
            'orgId' => (string)Str::uuid(),
            'name' => "User1's Organisation",
            'description' => 'An organization',
        ]);

        // Log in as the second user
        $this->actingAs($user2);

        // Try to access the organization data
        $response = $this->getJson('/api/organisations/' . $organization->orgId);

        $response->assertStatus(403); // Forbidden
    }
}
