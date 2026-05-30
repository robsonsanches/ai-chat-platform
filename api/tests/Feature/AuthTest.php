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
            'email' => 'robson@test.com',
            'password' => '123456'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
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
                    'token_type',
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

}
