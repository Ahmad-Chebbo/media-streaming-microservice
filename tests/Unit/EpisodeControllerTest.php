<?php

namespace Tests\Unit;

use App\Models\Episode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EpisodeControllerTest extends TestCase
{

    use WithFaker, RefreshDatabase;

    public function testStoreEpisode()
    {
        // Simulate authentication as an admin user
        Role::create(['name' => 'admin']);
        $adminUser = User::factory()->create();
        $adminUser->addRole('admin');
        Sanctum::actingAs($adminUser);


        $episodeName = $this->faker->name;

        $episodeData = [
            'mp3_url' => 'https://archive.org/download/work_2307.poem_librivox/work_anderson_alp.mp3',
            'name' => $episodeName,
            'author' => $this->faker->name,
        ];

        $response = $this->postJson(route('episodes.store'), $episodeData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('episodes', ['name' => $episodeName]);
    }

    public function testUserCanGetSignedUrlForEpisode()
    {
        // Create a user and episode
        Role::create(['name' => 'user']);
        $user = User::factory()->create();
        $episode = Episode::factory()->create(['private' => true]);

        // Authenticate the user
        $this->actingAs($user);

        // Send a GET request to the signed-url endpoint
        $response = $this->getJson(route('get-signed-url', ['episode' => $episode->id]));

        // Assert response status and structure
        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data' => ['signed_url']]);
    }

    public function testAdminCanFlagEpisodeAsPrivate()
    {
        // Create an admin user and episode
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->addRole('admin');
        $episode = Episode::factory()->create();

        // Authenticate the admin
        $this->actingAs($admin);

        // Send a POST request to the private flag endpoint
        $response = $this->postJson(route('flag-episode-private', ['episode' => $episode->id]));

        // Assert response status and data
        $response->assertStatus(200);
        $this->assertTrue($episode->fresh()->private == 1);
    }

    public function testNonAdminCannotFlagEpisodeAsPrivate()
    {
        // Create a user and episode
        Role::create(['name' => 'user']);
        $user = User::factory()->create();
        $user->addRole('user');
        $episode = Episode::factory()->create();

        // Authenticate the user
        $this->actingAs($user);

        // Send a POST request to the private flag endpoint
        $response = $this->postJson(route('flag-episode-private', ['episode' => $episode->id]));

        // Assert response status and data
        $response->assertStatus(403);
    }
}
