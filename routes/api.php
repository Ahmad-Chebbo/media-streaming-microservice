<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EpisodeController;
use App\Jobs\LogAnalyticsJob;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('test', function()
{
    $response = Http::withToken(Cache::get('access_token'))->post(env('ANALYTIC_SERVICE_MICROSERVICE_URL') . '/api/log', [
        'episode_id' => 1,
        'user_id' => 1
    ]);

    return response()->json($response->body());
});

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');

Route::get('/episodes/{episode}', [EpisodeController::class, 'streamEpisode'])->name('stream');

Route::middleware('auth:sanctum')->group(function () {


    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Episode Routes
    Route::post('/episodes', [EpisodeController::class, 'store'])->name('episodes.store');
    Route::post('/episodes/{episode}/private', [EpisodeController::class, 'flagAsPrivate'])->name('flag-episode-private');
    Route::get('/episodes/{episode}/signed-url', [EpisodeController::class, 'getSignedUrl'])->name('get-signed-url');
});
