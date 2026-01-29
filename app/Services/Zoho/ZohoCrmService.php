<?php

namespace App\Services\Zoho;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZohoCrmService
{
    protected ZohoAuthService $authService;
    protected string $apiBase;

    public function __construct(ZohoAuthService $authService)
    {
        $this->authService = $authService;
        $this->apiBase     = config('zoho.api_base');
    }

    /**
     * General method to fetch records from a module.
     */
    public function getRecords(string $module, int $page = 1, int $perPage = 200, string $modifiedSince = null): array
    {
        $token = $this->authService->getAccessToken();

        if (!$token) {
            Log::error("ZohoCrmService: No valid access token available.");
            return [];
        }

        $url = "{$this->apiBase}/crm/v2/{$module}";

        $params = [
            'page'     => $page,
            'per_page' => $perPage,
        ];

        if ($modifiedSince) {
            $params['headers'] = ['If-Modified-Since' => $modifiedSince];
        }

        // Using withoutVerifying() for local dev environment SSL issues
        $response = Http::withoutVerifying()
            ->withToken($token)
            ->get($url, $params);

        if ($response->failed()) {
            Log::error("ZohoCrmService: Failed to fetch {$module}.", [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            return [];
        }

        return $response->json()['data'] ?? [];
    }

    /**
     * Fetch a specific record by ID.
     */
    public function getRecord(string $module, string $id): ?array
    {
        $token = $this->authService->getAccessToken();

        if (!$token) {
            return null;
        }

        $url = "{$this->apiBase}/crm/v2/{$module}/{$id}";

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->get($url);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'][0] ?? null;
        }

        Log::error("ZohoCrmService: Failed to fetch record {$id} from {$module}.", [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

        return null;
    }

    /**
     * Search for records (e.g. search Account by name).
     */
    public function searchRecords(string $module, string $criteria): array
    {
        $token = $this->authService->getAccessToken();

        if (!$token) {
            return [];
        }

        $url = "{$this->apiBase}/crm/v2/{$module}/search";

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->get($url, [
                'criteria' => $criteria,
            ]);

        return $response->json()['data'] ?? [];
    }
}
