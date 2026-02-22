<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Zoho\ZohoCrmService;

class ZohoFirstRecordDump extends Command
{
    protected $signature = 'zoho:dump-one {module}';
    protected $description = 'Dump one record from Zoho CRM to see all field names';

    public function handle(ZohoCrmService $crm): int
    {
        $module = $this->argument('module');
        $this->info("Fetching first record from {$module}...");
        
        $records = $crm->getRecords($module, 1, 1);
        
        if (count($records) > 0) {
            $record = $records[0];
            $this->info("First Record Structure:");
            foreach ($record as $key => $value) {
                if (is_array($value)) {
                    $this->line("Field: {$key} | Value: [Array/Object]");
                } else {
                    $this->line("Field: {$key} | Value: {$value}");
                }
            }
        } else {
            $this->error("No records found in {$module}.");
        }

        return 0;
    }
}
