<?php

namespace Tests\Unit;

use App\Models\Episode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StreamingTest extends TestCase
{
    use RefreshDatabase;

    public function testStreamPublicEpisodeWithoutAuthentication()
    {
        $episode = Episode::factory()->create(['private' => false]);

        // Mock external URL response with an MP3 file content
        $mp3Content = Storage::get('app/testing-purpose.mp3');

        Http::fake([
            $episode->mp3_url => Http::response($mp3Content, 200),
        ]);

        // Send a GET request to the streaming endpoint
        $response = $this->get(route('stream', ['episode' => $episode->id]));

        // Assert response status code and headers
        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'audio/mpeg');

        // Assert response content
        $response->assertSee($mp3Content);
    }

    public function testUserCanStreamPrivateEpisodeWithValidToken()
    {
        $episode = Episode::factory()->create(['private' => true]);

        // Mock external URL response with an MP3 file content
        $mp3Content = Storage::get('app/testing-purpose.mp3');

        // Mock external URL response
        Http::fake([
            $episode->mp3_url => Http::response($mp3Content, 200),
        ]);

        // Simulate authentication for the test user
        $user = User::factory()->create();
        Role::create(['name' => 'user']);
        $user->addRole('user');
        Sanctum::actingAs($user);

        // Send a GET request to the streaming endpoint
        try {
            $response = $this->get(route('stream', ['episode' => $episode->id]));

            // Assert response status code and headers
            $response->assertStatus(Response::HTTP_OK);
            $response->assertHeader('Content-Type', 'audio/mpeg');

            // Assert response content
            $response->assertSee($mp3Content);
        } catch (\Exception $e) {
            // Output the exception message for debugging
            echo $e->getMessage();
            throw $e;
        }
    }

    public function testUserCanStreamPrivateEpisodeWithSignedURL()
    {
        Role::create(['name' => 'user']);
        $user = User::factory()->create();
        $episode = Episode::factory()->create(['private' => true]);

        // Authenticate the user
        $this->actingAs($user);

        // Send a GET request to the signed-url endpoint
        $response = $this->getJson(route('get-signed-url', ['episode' => $episode->id]));

        // Assert response status code
        $response->assertStatus(Response::HTTP_OK);

        // Logout the user
        $this->postJson(route('logout'));

        // Get the signed URL from the response
        $signedUrl = $response->json('data.signed_url');

        // Send a GET request to the signed URL without being authenticated
        $response = $this->get($signedUrl);

        // Assert response status code and headers
        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'audio/mpeg');

        // Mock external URL response with an MP3 file content
        $mp3Content = Storage::get('app/testing-purpose.mp3');

        // Assert response content
        $response->assertSee($mp3Content);
    }

    // public function testStreamPartialContent()
    // {
    //     // Create a user and episode
    //     $episode = Episode::factory()->create();

    //     // Mock external URL response with an MP3 file content
    //     $mp3Content = Storage::get('app/testing-purpose.mp3');

    //     // Mock external URL response
    //     Http::fake([
    //         $episode->mp3_url => Http::response($mp3Content, 200),
    //     ]);

    //     // Send a GET request to the streaming endpoint with a range header
    //     $response = $this->get(route('stream', ['episode' => $episode->id]), [
    //         'Range' => 'bytes=100-199',
    //     ]);

    //     // Assert response status code, headers, and content
    //     $response->assertStatus(Response::HTTP_PARTIAL_CONTENT);
    //     $response->assertHeader('Content-Type', 'audio/mpeg');
    //     $response->assertHeader('Accept-Ranges', 'bytes');

    //     // Check the Content-Range header format
    //     $response->assertHeader('Content-Range', 'bytes 100-199/' . strlen($mp3Content));

    //     // Assert response content length
    //     $response->assertHeader('Content-Length', '100');

    //     // Assert response content
    //     $response->assertSee(substr($mp3Content, 100, 100));
    // }

}
