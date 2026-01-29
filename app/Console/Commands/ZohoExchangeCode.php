<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ZohoExchangeCode extends Command
{
    protected $signature = 'zoho:exchange {code}';
    protected $description = 'Exchange a Zoho Grant Code for a Refresh Token';

    public function handle()
    {
        $code = $this->argument('code');
        $clientId = config('zoho.client_id');
        $clientSecret = config('zoho.client_secret');
        $accountsBase = config('zoho.accounts_base');

        $this->info("Exchanging code for client: " . $clientId);

        $response = Http::withoutVerifying()->asForm()->post("{$accountsBase}/oauth/v2/token", [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
        ]);

        if ($response->failed()) {
            $this->error("Failed to exchange code!");
            $this->error("Status: " . $response->status());
            $this->error("Body: " . $response->body());
            return 1;
        }

        $data = $response->json();
        
        if (isset($data['error'])) {
            $this->error("Error from Zoho: " . $data['error']);
            $this->error("Full Body: " . $response->body());
            return 1;
        }

        $this->info("--- SUCCESS! ---");
        $this->info("Access Token: " . ($data['access_token'] ?? 'N/A'));
        $this->info("Refresh Token: " . ($data['refresh_token'] ?? 'N/A'));
        $this->info("----------------");
        $this->line("Please copy the REFRESH TOKEN above and paste it into your .env file.");
        
        return 0;
    }
}
