<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SignupTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Clear the mock DB before each test to ensure a clean slate
        Cache::forget('mock_users_db');
    }

    /**
     * Test a successful user signup.
     */
    public function test_user_can_signup_successfully(): void
    {
        $payload = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/signup', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Account created successfully',
                 ]);

        // Assert the user is in the mock DB
        $mockDb = Cache::get('mock_users_db', []);
        $this->assertCount(1, $mockDb);
        $this->assertEquals('test@example.com', $mockDb[0]['email']);
        $this->assertEquals('testuser', $mockDb[0]['username']);
    }

    /**
     * Test signup fails when email already exists.
     */
    public function test_signup_fails_if_email_already_exists(): void
    {
        // Seed the mock DB with an existing user
        $existingUser = [
            'id' => 'existing_id_123',
            'username' => 'existinguser',
            'email' => 'duplicate@example.com',
            'password' => 'hashed_password',
        ];
        Cache::put('mock_users_db', [$existingUser]);

        // Attempt to signup with the same email
        $payload = [
            'username' => 'newuser',
            'email' => 'duplicate@example.com',
            'password' => 'newpassword123',
        ];

        $response = $this->postJson('/api/signup', $payload);

        $response->assertStatus(409)
                 ->assertJson([
                     'message' => 'Email already exists.',
                 ]);
                 
        // Assert the database still only has 1 user
        $mockDb = Cache::get('mock_users_db', []);
        $this->assertCount(1, $mockDb);
    }

    /**
     * Test validation errors (e.g., missing fields).
     */
    public function test_signup_requires_username_email_and_password(): void
    {
        $response = $this->postJson('/api/signup', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['username', 'email', 'password']);
    }

    /**
     * Test password length validation.
     */
    public function test_signup_requires_minimum_password_length(): void
    {
        $payload = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'short', // less than 8 characters
        ];

        $response = $this->postJson('/api/signup', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }
}
