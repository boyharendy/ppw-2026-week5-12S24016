<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTokenTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Pustakawan Uji',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_it_authenticates_valid_credentials_and_issues_bearer_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'password123',
            'device_name' => 'phpunit-client',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token_type',
                'access_token',
                'user' => ['id', 'name', 'email'],
            ],
        ]);
        $this->assertEquals('Bearer', $response->json('data.token_type'));
        $this->assertNotEmpty($response->json('data.access_token'));
    }

    public function test_it_rejects_invalid_credentials_with_rfc_7807_problem_details(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'title' => 'Invalid Credentials',
            'status' => 401,
        ]);
    }

    public function test_it_retrieves_authenticated_user_profile_with_bearer_token(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'email' => 'pustakawan@del.ac.id',
            ],
        ]);
    }

    public function test_it_rejects_unauthenticated_request_with_rfc_7807_problem_details(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'title' => 'Unauthorized',
            'status' => 401,
        ]);
    }

    public function test_it_revokes_token_on_logout(): void
    {
        $token = $this->user->createToken('logout-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Bersihkan cache guard pada testing runner sebelum request berikutnya
        auth()->forgetGuards();

        // Token yang sudah dicabut harus ditolak saat digunakan kembali
        $subsequentResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $subsequentResponse->assertStatus(401);
    }
}
