<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Zoho\ZohoAuthService;
use Illuminate\Support\Facades\Http;

class ZohoFieldsInfo extends Command
{
    protected $signature = 'zoho:fields {module}';
    protected $description = 'Fetch all fields for a Zoho CRM module';

    public function handle(ZohoAuthService $auth): int
    {
        $module = $this->argument('module');
        $token = $auth->getAccessToken();
        $apiBase = config('zoho.api_base');

        if (!$token) {
            $this->error('Failed to get access token.');
            return 1;
        }

        $this->info("Fetching fields for module: {$module}...");
        $url = "{$apiBase}/crm/v2/settings/fields?module={$module}";
        
        $response = Http::withoutVerifying()->withToken($token)->get($url);
        
        if ($response->successful()) {
            $fields = $response->json()['fields'] ?? [];
            foreach ($fields as $field) {
                if ($field['data_type'] === 'url' || str_contains(strtolower($field['field_label']), 'creator') || str_contains(strtolower($field['field_label']), 'print')) {
                    $this->warn("RELEVANT FIELD: " . $field['api_name'] . " | Label: " . $field['field_label'] . " | Type: " . $field['data_type']);
                } else {
                    $this->line("Field: " . $field['api_name'] . " | Label: " . $field['field_label'] . " | Type: " . $field['data_type']);
                }
            }
        } else {
            $this->error("Failed to fetch fields. Status: " . $response->status());
            $this->error("Body: " . $response->body());
        }

        return 0;
    }
}
