<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Zoho\ZohoCrmService;

class ZohoFindValue extends Command
{
    protected $signature = 'zoho:find-value {module} {value}';
    protected $description = 'Find which field in Zoho CRM contains a specific value';

    public function handle(ZohoCrmService $crm): int
    {
        $module = $this->argument('module');
        $valueToFind = $this->argument('value');
        
        $this->info("Searching for '{$valueToFind}' in {$module}...");
        
        $records = $crm->getRecords($module, 1, 200, null, 'Created_Time', 'desc'); // Check 200 most recent records
        
        foreach ($records as $record) {
            foreach ($record as $key => $val) {
                if (is_array($val) || is_object($val)) {
                    continue;
                }
                $checkVal = (string)$val;
                if (str_contains($checkVal, $valueToFind)) {
                    $this->warn("FOUND MATCH! Field: {$key} | Value: {$checkVal}");
                    return 0;
                }
            }
        }

        $this->error("Value not found. Found keys in first record: " . implode(', ', array_keys($records[0] ?? [])));
        return 1;
    }
}
