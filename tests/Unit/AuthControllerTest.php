<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function testUserCanLogin()
    {
        Role::create(['name' => 'user']);
        $user = User::create([
            'email' => $this->faker->safeEmail,
            'name' => $this->faker->name,
            'password' => bcrypt('SecureP@ss123!'),
        ]);

        $response = $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'SecureP@ss123!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
            ],
        ]);
    }

    public function testUserCanRegister()
    {
        Role::create(['name' => 'user']);
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'SecureP@ss123!',
        ];

        $response = $this->postJson(route('register'), $userData);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'User created successfully']);
        $this->assertDatabaseHas('users', ['email' => $userData['email']]);
    }


}
