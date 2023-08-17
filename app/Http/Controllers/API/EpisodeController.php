<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StoreEpisodeRequest;
use App\Jobs\LogAnalyticsJob;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class EpisodeController extends BaseController
{
    public function store(StoreEpisodeRequest $request)
    {
        Episode::create($request->validated());

        return $this->responseJsonSuccess('Episode added successfully');
    }

    public function flagAsPrivate(Episode $episode)
    {
        try {

            if (Auth::user()->hasRole('admin')) {
                $episode->private = true;
                $episode->save();
                return $this->responseJsonSuccess('Episode flagged as private');
            }
            return $this->responseJsonError('You don\'t have any of the necessary access rights to perform this action', null, 403);
        } catch (\Exception $ex) {
            return $this->responseJsonErrorInternalServerError('Something went wrong, please try again later', $ex->getMessage());
        }
    }

    public function getSignedUrl(Episode $episode)
    {
        // Check the status of the episode to see if it's private or not
        if (!$episode->private) {
            return $this->responseJsonError('This episode is already publicly available, you don\'t need to generate a signed URL');
        }

        // Generate a signed URL with a maximum time to live of 1 hour
        $expiration = now()->addSeconds(3600);
        $signedUrl = URL::temporarySignedRoute('stream', $expiration, [
            'episode' => $episode->id
        ]);

        return $this->responseJsonSuccess('Signed Url retrieved successfully', ['signed_url' => $signedUrl]);
    }

    public function streamEpisode(Request $request, Episode $episode)
    {
        try {
            // Check authentication using token in headers or signed URL
            if (!$this->isAuthorized($request, $episode)) {
                return $this->responseJsonError('Oops, this episode is not publicly available or the given signed URL is not valid', null, 401);
            }

            // Call analytics service to log the request
            $this->logAnalytics($episode, $request->ip());

            // Generate a unique cache key based on the episode ID
            $cacheKey = 'episode_' . $episode->id;

            // Check if the episode is already cached
            if (Cache::has($cacheKey)) {
                $cachedData = Cache::get($cacheKey);
                $cachedFilePath = $cachedData['path'];
                $fileSize = $cachedData['size'];
            } else {
                // Download the file from external storage and cache it locally
                $mp3Url = $episode->mp3_url;
                $response = Http::get($mp3Url);

                if (!$response->successful()) {
                    return $this->responseJsonError('Failed to fetch MP3 file', null, 500);
                }

                // Create a unique cache file name
                $filename = 'episode_' . $episode->id . '_' . uniqid() . '.mp3';
                $cachedFilePath = storage_path('app/' . $filename);

                // Save the downloaded file to local storage
                file_put_contents($cachedFilePath, $response->body());

                // Get the file size
                $fileSize = strlen($response->body());

                // Cache the episode data
                Cache::put($cacheKey, ['path' => $cachedFilePath, 'size' => $fileSize], now()->addMinutes(30));
            }

            // Check if the request is for partial content
            $range = $request->headers->get('Range');
            if ($range) {
                list($start, $end) = explode('-', substr($range, 6), 2);

                $start = intval($start);
                $end = $end ? intval($end) : $fileSize - 1;

                if ($start >= 0 && $start <= $end && $end < $fileSize) {
                    $length = ($end - $start) + 1;
                    $headers = [
                        'Content-Type' => 'audio/mpeg',
                        'Content-Range' => "bytes $start-$end/$fileSize",
                        'Accept-Ranges' => 'bytes',
                        'Content-Length' => $length,
                    ];

                    return response()->stream(
                        function () use ($cachedFilePath, $start, $length) {
                            $stream = fopen($cachedFilePath, 'rb');
                            fseek($stream, $start);
                            echo fread($stream, $length);
                            fclose($stream);
                        },
                        206,
                        $headers
                    );
                }
            }

            // Full audio streaming response
            return response()->stream(
                function () use ($cachedFilePath) {
                    readfile($cachedFilePath);
                },
                200,
                [
                    'Content-Type' => 'audio/mpeg',
                    'Content-Length' => $fileSize,
                    'Accept-Ranges' => 'bytes',
                ]
            );

        } catch (\Exception $ex) {
            return $this->responseJsonError('Something went wrong, please try again later', $ex->getMessage());
        }
    }

    private function isAuthorized(Request $request, $episode)
    {
        // Check if the episode is private and authentication is provided
        if ($episode->private) {
            return Auth::guard('sanctum')->check() || URL::hasValidSignature($request);
        }
        return true;
    }

    private function logAnalytics($episode, $ipAddress)
    {
        // Implement logic to call the analytics service API
        // Dispatch the job for asynchronous processing
         dispatch(new LogAnalyticsJob($episode->id, $ipAddress));

    }

    // Additional backend functionalities that are not being used, but only for reviewing purposes

    public function StoreEpisodeIntoLocalFiles(StoreEpisodeRequest $request)
    {
        $validatedData = $request->validated();

        $name = $validatedData['name'];
        $author = $validatedData['author'];

        if ($request->has('mp3_file')) {
            // If the user uploaded an MP3 file
            $uploadedFile = $validatedData['mp3_file'];
            $filename = uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
            // $localFilePath = storage_path('app/' . $filename);

            // Move the uploaded file to local storage
            $uploadedFile->move(storage_path('app'), $filename);

            // Convert the local file path to a public URL
            $publicUrl = url('storage/' . $filename);

        } elseif ($request->has('mp3_url')) {
            // If the user provided an MP3 URL
            $mp3Url = $validatedData['mp3_url'];
            $filename = uniqid() . '.mp3';
            // $localFilePath = storage_path('app/' . $filename);
            // Convert the local file path to a public URL
            $publicUrl = url('storage/' . $filename);

            $response = Http::get($mp3Url);
            if (!$response->successful()) {
                return $this->responseJsonError('Failed to download MP3 file', null, 500);
            }

            // Save the downloaded file to local storage
            Storage::put($filename, $response->body());
        } else {
            return $this->responseJsonError('No MP3 file or URL provided', null, 400);
        }

        Episode::create([
            'mp3_url' => $publicUrl,
            'name' => $name,
            'author' => $author,
        ]);

        return $this->responseJsonSuccess('Episode added successfully');

    }

    public function streamEpisodeFromTheStorage(Request $request, Episode $episode)
    {
        try {
            // Check authentication using token in headers or signed URL
            if (!$this->isAuthorized($request, $episode)) {
                return $this->responseJsonError("Oops, this episode is not publicly available or the given signed URL is not valid", null, 401);
            }

            // Call analytics service to log the request
            $this->logAnalytics($episode, $request->ip());

            // Check if the file exists in the local cache
            $cachedFilePath = storage_path('app/episode_' . $episode->id);

            if (!Storage::disk('local')->exists($cachedFilePath)) {
                // Download the file from storage and cache it locally
                $fileStream = Storage::disk('local')->get($episode->mp3_url);
                Storage::disk('local')->put($cachedFilePath, $fileStream);
            }

            $fileSize = Storage::disk('local')->size($cachedFilePath);

            // Check if the request is for partial content
            $range = $request->headers->get('Range');
            if ($range) {
                list($start, $end) = explode('-', substr($range, 6), 2);

                $start = intval($start);
                $end = $end ? intval($end) : $fileSize - 1;

                if ($start >= 0 && $start <= $end && $end < $fileSize) {
                    $length = ($end - $start) + 1;

                    $headers = [
                        'Content-Type' => 'audio/mpeg',
                        'Content-Range' => "bytes $start-$end/$fileSize",
                        'Accept-Ranges' => 'bytes',
                        'Content-Length' => $length,
                    ];

                    return response()->stream(
                        function () use ($cachedFilePath, $start, $length) {
                            $stream = fopen($cachedFilePath, 'rb');
                            fseek($stream, $start);
                            echo fread($stream, $length);
                            fclose($stream);
                        },
                        206,
                        $headers
                    );
                }
            }

            // Full audio streaming response
            return response()->stream(
                function () use ($cachedFilePath) {
                    readfile($cachedFilePath);
                },
                200,
                [
                    'Content-Type' => 'audio/mpeg',
                    'Content-Length' => $fileSize,
                ]
            );

        } catch (\Exception $ex) {
            return $this->responseJsonError('Something went wrong, please try again later', $ex);
        }
    }

}
