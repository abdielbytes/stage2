<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registering a new user successfully with default organization.
     *
     * @return void
     */
    public function testRegisterUserWithDefaultOrganization()
    {
        $userData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
        ];

        $response = $this->postJson('api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Registration successful',
            ]);

        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertNotNull($user);

        $organisation = $user->organisations()->first();
        $this->assertNotNull($organisation);
        $this->assertEquals("John's Organisation", $organisation->name);
        $this->assertEquals('Your default organisation', $organisation->description);
    }
    /**
     * Test user login successfully.
     *
     * @return void
     */
    public function testUserLogin()
    {
        // Create a user first
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'accessToken',
                    'user' => [
                        'userId',
                        'firstName',
                        'lastName',
                        'email',
                        // Add other expected user attributes here
                    ],
                ],
            ]);
    }

    /**
     * Test validation errors when required fields are missing.
     *
     * @return void
     */
    public function testRegistrationValidationErrors()
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['firstName', 'lastName', 'email', 'password']);
    }

    /**
     * Test registration fails if there's duplicate email or userId.
     *
     * @return void
     */
    public function testDuplicateEmailOrUserId()
    {
        // Create a user first
        $user = User::factory()->create();

        $userData = [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'email' => $user->email, // Use existing email
            'phone' => '1234567890',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
