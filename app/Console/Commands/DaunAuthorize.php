<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DaunAuthorize extends Command
{
    protected $signature = 'daun:authorize';

    protected $description = 'One-time OAuth flow to get a Daun.me API key';

    public function handle(): int
    {
        $apiUrl    = config('services.daun.api_url', 'https://daun.me');
        $clientId  = config('services.daun.client_id');
        $clientSecret = config('services.daun.client_secret');
        $redirectUri  = config('services.daun.redirect_uri');

        if (!$clientId || !$clientSecret) {
            $this->error('DAUN_CLIENT_ID and DAUN_CLIENT_SECRET must be set in .env');
            return self::FAILURE;
        }

        if (!$redirectUri) {
            $this->error('DAUN_REDIRECT_URI must be set in .env');
            return self::FAILURE;
        }

        // --- Step 1: Generate PKCE code verifier + challenge ---
        $codeVerifier = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
        $state = bin2hex(random_bytes(16));

        // --- Step 2: Build authorization URL ---
        $params = http_build_query([
            'client_id'             => $clientId,
            'redirect_uri'          => $redirectUri,
            'state'                 => $state,
            'scope'                 => 'posts:write me:read',
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        $authUrl = "{$apiUrl}/oauth/authorize?{$params}";

        $this->info('');
        $this->info('=== Daun.me Authorization ===');
        $this->info('');
        $this->line('1. Open this URL in your browser (logged in as sedekahinfo):');
        $this->info('');
        $this->line($authUrl);
        $this->info('');
        $this->line('2. Approve the consent screen.');
        $this->line('3. You will be redirected to a URL like:');
        $this->line("   {$redirectUri}?code=XXXX&state=XXXX");
        $this->info('');

        // --- Step 3: Ask for the code ---
        $code = $this->ask('4. Paste the "code" value from the redirect URL');

        if (!$code) {
            $this->error('No code provided. Aborting.');
            return self::FAILURE;
        }

        // --- Step 4: Exchange code for API key ---
        $this->info('Exchanging code for API key...');

        $response = Http::post("{$apiUrl}/api/v2/oauth/token", [
            'grant_type'    => 'authorization_code',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'code'          => $code,
            'code_verifier' => $codeVerifier,
            'redirect_uri'  => $redirectUri,
        ]);

        if (!$response->successful()) {
            $this->error('Token exchange failed: ' . $response->body());
            return self::FAILURE;
        }

        $apiKey   = $response->json('data.apiKey');
        $expiresAt = $response->json('data.expiresAt');
        $scope    = implode(', ', $response->json('data.scope', []));

        $this->info('');
        $this->info('=== Success! ===');
        $this->info('');
        $this->line("Scope:     {$scope}");
        $this->line("Expires:   " . ($expiresAt ?? 'Never'));
        $this->info('');
        $this->info('Add this to your .env:');
        $this->info('');
        $this->line("DAUN_API_KEY={$apiKey}");
        $this->line('DAUN_ENABLED=true');
        $this->info('');

        return self::SUCCESS;
    }
}
