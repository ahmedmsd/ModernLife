<?php

namespace App\Services\Zoho;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ZohoAuthService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $refreshToken;
    protected string $accountsBase;

    public function __construct()
    {
        $this->clientId     = config('zoho.client_id');
        $this->clientSecret = config('zoho.client_secret');
        $this->refreshToken = config('zoho.refresh_token');
        $this->accountsBase = config('zoho.accounts_base');
    }

    /**
     * Get a valid access token, either from cache or by refreshing it.
     */
    public function getAccessToken(): ?string
    {
        $cacheKey = 'zoho_access_token';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        return $this->refreshAccessToken();
    }

    /**
     * Request a new access token using the refresh token.
     */
    public function refreshAccessToken(): ?string
    {
        if (!$this->refreshToken) {
            Log::error('ZohoAuthService: Refresh token is missing from configuration.');
            return null;
        }

        $url = "{$this->accountsBase}/oauth/v2/token";

        $response = Http::withoutVerifying()->asForm()->post($url, [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
        ]);

        if ($response->failed()) {
            Log::error('ZohoAuthService: Failed to refresh access token.', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            return null;
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? null;

        if ($accessToken) {
            // Buffer of 60 seconds before actual expiration
            $expiresIn = ($data['expires_in'] ?? 3600) - 60;
            Cache::put('zoho_access_token', $accessToken, $expiresIn);
            return $accessToken;
        }

        Log::error('ZohoAuthService: Access token not found in response.', $data);
        return null;
    }
}
