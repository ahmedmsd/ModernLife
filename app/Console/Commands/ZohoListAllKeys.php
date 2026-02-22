<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Zoho\ZohoCrmService;

class ZohoListAllKeys extends Command
{
    protected $signature = 'zoho:list-keys {module}';
    protected $description = 'List all API field names for a specific module';

    public function handle(ZohoCrmService $crm): int
    {
        $module = $this->argument('module');
        $this->info("Fetching first record from {$module} to list keys...");
        
        $records = $crm->getRecords($module, 1, 1);
        
        if (count($records) > 0) {
            $keys = array_keys($records[0]);
            sort($keys);
            $this->info("Found " . count($keys) . " fields:");
            foreach ($keys as $key) {
                $this->line("- $key");
            }
        } else {
            $this->error("No records found in {$module}.");
        }

        return 0;
    }
}
