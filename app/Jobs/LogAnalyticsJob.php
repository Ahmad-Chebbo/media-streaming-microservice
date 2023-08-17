<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LogAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $episodeId, $ipAddress;

    public function __construct($episodeId, $ipAddress)
    {
        $this->episodeId = $episodeId;
        $this->ipAddress = $ipAddress;
    }

    public function handle()
    {
        $accessToken = Cache::get('access_token');

        if ($accessToken) {
            // Use the HTTP client to make the fire-and-forget request
            Http::withToken($accessToken)
                ->post(env('ANALYTIC_SERVICE_MICROSERVICE_URL') . '/api/log', [
                    'episode_id' => $this->episodeId,
                    'ip_address' => $this->ipAddress
                ]);
        }
    }
}
