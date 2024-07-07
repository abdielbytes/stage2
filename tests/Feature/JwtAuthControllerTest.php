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

        $token = JWTAuth::fromUser($user, ['expires' => now()->addHours(1)]);

        $this->assertNotNull($token);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/me');

        Log::info('Response: ' . $response->getContent());

//        $response->assertStatus(200)
//            ->assertJson([
//                'user' => [
//                    'userId' => $user->userId,
//                    'email' => $user->email,
//                ],
//            ]);
//        $response->assertJsonStructure([
//            'status',
//            'user' => [
//                    'userId' => $user->userId,
//                    'email' => $user->email,
//                ],
//        ]);

        $this->assertLessThanOrEqual(now()->addHours(2), $user->tokens()->first()->expires_at());
    }

    /**
     * Test organization access control.
     *
     * @return void
     */
    public function testOrganizationAccessControl()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $organization = $user1->organisations()->create([
            'orgId' => (string)Str::uuid(),
            'name' => "User1's Organisation",
            'description' => 'An organization',
        ]);

        $this->actingAs($user2);

        $response = $this->getJson('/api/organisations/' . $organization->orgId);

        $response->assertStatus(403); // Forbidden
    }
}
