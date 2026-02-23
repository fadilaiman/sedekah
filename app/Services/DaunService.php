<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DaunService
{
    /**
     * Check if Daun integration is enabled.
     */
    public function isEnabled(): bool
    {
        return config('services.daun.enabled') && !empty(config('services.daun.api_key'));
    }

    /**
     * Post content to Daun.me.
     *
     * Returns false on any exception or non-201 response.
     * Never throws exceptions, always logs warnings on failure.
     */
    public function post(string $content): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $apiUrl = config('services.daun.api_url', 'https://daun.me');
            $apiKey = config('services.daun.api_key');

            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
            ])->attach('content', $content)->post("{$apiUrl}/api/v2/posts");

            if ($response->status() === 201) {
                return true;
            }

            Log::warning('Daun.me post failed with status ' . $response->status(), [
                'content' => $content,
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::warning('Daun.me post failed with exception: ' . $e->getMessage(), [
                'content' => $content,
                'exception' => get_class($e),
            ]);

            return false;
        }
    }
}
