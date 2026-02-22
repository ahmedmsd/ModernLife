<?php

namespace App\Services\Zoho;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZohoCreatorService
{
    protected ZohoAuthService $authService;
    protected string $ownerName;
    protected string $appLinkName;
    protected string $apiBase;

    public function __construct(ZohoAuthService $authService)
    {
        $this->authService = $authService;
        $this->ownerName    = config('zoho.creator_owner_name', 'zoho_ali979');
        $this->appLinkName  = config('zoho.creator_app_link_name', 'object-system');
        $this->apiBase      = config('zoho.creator_api_base', 'https://creator.zoho.com/api/v2');
    }

    /**
     * Get records from a Zoho Creator Report.
     */
    public function getRecords(string $reportLinkName, int $from = 0, int $limit = 200, array $criteria = []): ?array
    {
        $token = $this->authService->getAccessToken();

        if (!$token) {
            Log::error("ZohoCreatorService: No valid access token available.");
            return null;
        }

        $url = "{$this->apiBase}/{$this->ownerName}/{$this->appLinkName}/report/{$reportLinkName}";

        $params = [
            'from'  => $from,
            'limit' => $limit,
        ];

        // Criteria format: (Field_Name == "Value")
        if (!empty($criteria)) {
            $criteriaStr = "";
            foreach ($criteria as $field => $value) {
                if ($criteriaStr !== "") $criteriaStr .= " && ";
                $criteriaStr .= "({$field} == \"{$value}\")";
            }
            $params['criteria'] = $criteriaStr;
        }

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->get($url, $params);

        if ($response->failed()) {
            Log::error("ZohoCreatorService: Failed to fetch records from report {$reportLinkName}.", [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
            return null;
        }

        return $response->json();
    }

    /**
     * Get a specific record by ID.
     */
    public function getRecord(string $reportLinkName, string $id): ?array
    {
        $token = $this->authService->getAccessToken();

        if (!$token) return null;

        $url = "{$this->apiBase}/{$this->ownerName}/{$this->appLinkName}/report/{$reportLinkName}/{$id}";

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->get($url);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'] ?? null;
        }

        return null;
    }
}
