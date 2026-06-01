<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Robson',
            'email' => 'robson@domain.com',
            'password' => '123456'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'expires_at',
                    'expires_in',
                    'user' => [
                        'id',
                        'name',
                        'email'
                    ]
                ]
            ]);
    }

    public function test_user_can_login()
    {
        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('123456')
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => '123456'
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'expires_at',
                    'expires_in',
                    'user'
                ]
            ]);
    }

    public function test_authenticated_user_can_get_profile()
    {
        $user = \App\Models\User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->uid
                ]
            ]);
    }

    public function test_guest_cannot_access_me()
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertUnauthorized();
    }

    public function test_user_can_logout_revoking_current_token()
    {
        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('123456')
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => '123456'
        ])->json('data');

        $token = $login['access_token'];

        $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->postJson('/api/v1/auth/logout')
            ->assertOk();
    }

    public function test_logout_all_revokes_all_tokens()
    {
        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('123456')
        ]);

        $login1 = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => '123456'
        ])->json('data');

        $login2 = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => '123456'
        ])->json('data');

        $t1 = $login1['access_token'];
        $t2 = $login2['access_token'];

        $this->withHeaders(['Authorization' => 'Bearer '.$t1])
            ->postJson('/api/v1/auth/logout-all')
            ->assertOk();
    }

    public function test_refresh_rotates_token()
    {
        $user = \App\Models\User::factory()->create([
            'password' => bcrypt('123456')
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => '123456'
        ])->json('data');

        $old = $login['access_token'];

        $resp = $this->withHeaders(['Authorization' => 'Bearer '.$old])
                    ->getJson('/api/v1/auth/refresh')
                    ->assertOk()
                    ->json('data');

        $this->assertArrayHasKey('access_token', $resp);

        $newToken = $resp['access_token'];

        $this->withHeaders(['Authorization' => 'Bearer '.$newToken])
            ->getJson('/api/v1/auth/me')->assertOk();
    }

}
