<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Zoho\ZohoCreatorService;
use App\Models\Client;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SyncZohoCreatorData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:sync-creator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync quotations data directly from Zoho Creator';

    protected ZohoCreatorService $creatorService;

    /**
     * Create a new command instance.
     */
    public function __construct(ZohoCreatorService $creatorService)
    {
        parent::__construct();
        $this->creatorService = $creatorService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        DB::connection()->disableQueryLog();

        $this->info("Syncing Quotations from Zoho Creator...");
        
        $report = 'Modern_Life_Quotations';
        $from = 0;
        $limit = 200;
        $totalSynced = 0;

        do {
            $result = $this->creatorService->getRecords($report, $from, $limit);

            if ($result === null) {
                $this->error("  API Error: Failed to fetch records from Creator. Check laravel.log.");
                break;
            }

            $records = $result['data'] ?? [];
            $count = count($records);

            if ($count > 0) {
                foreach ($records as $record) {
                    $this->processRecord($record);
                    $totalSynced++;
                }
                $this->line("  Offset {$from}: Synced {$count} records...");
            }

            $from += $count;
        } while ($count >= $limit);

        $this->info("Sync complete. Total records: {$totalSynced}");

        return 0;
    }

    protected function processRecord(array $data)
    {
        DB::transaction(function () use ($data) {
            $creatorId = $data['ID'];
            $quoteNumber = $data['Quotation_No'] ?? 'N/A';
            
            // Client Mapping (Fallback to name if no ID match)
            $contactName = $data['Contacts1']['display_value'] ?? null;
            $client = null;
            if ($contactName) {
                $client = Client::where('client_name', trim($contactName))->first();
            }

            // PDF URLs Logic
            $quotesHash = config('zoho.creator_quotes_hash');
            $contractsHash = config('zoho.creator_contracts_hash');
            $owner = config('zoho.creator_owner_name');
            $appName = config('zoho.creator_app_link_name');

            if ($quotesHash) {
                // Public/Published URL format
                $quoteUrl = "https://creatorapp.zoho.com/publish/{$owner}/{$appName}/record-print/Modern_Life_Quotations/{$creatorId}/{$quotesHash}";
            } else {
                // Standard Portal URL (Requires login)
                $quoteUrl = "https://crmsystem.zohocreatorportal.com/{$owner}/{$appName}/record-print/Modern_Life_Quotations/{$creatorId}";
            }

            if ($contractsHash) {
                // Public/Published URL format
                $contractUrl = "https://creatorapp.zoho.com/publish/{$owner}/{$appName}/record-print/Modern_Life_Contracts/{$creatorId}/{$contractsHash}";
            } else {
                // Standard Portal URL (Requires login)
                $contractUrl = "https://crmsystem.zohocreatorportal.com/{$owner}/{$appName}/record-print/Modern_Life_Contracts/{$creatorId}";
            }

            $quoteData = [
                'subject'       => ($data['Contract_Type'] ?? 'Quotation') . ' - ' . $quoteNumber,
                'quote_number'  => $quoteNumber,
                'quote_stage'   => $data['Quotation_Stage'] ?? null,
                'zoho_module'   => 'ZohoCreator_ModernLife',
                'contract_type' => $data['Contract_Type'] ?? null,
                'total_amount'  => $data['Total_inc_VAT'] ?? 0,
                'client_id'    => $client ? $client->client_id : null,
                'raw_data'     => $data,
                'quotation_pdf_url' => $quoteUrl,
                'contract_pdf_url' => $contractUrl,
            ];

            // Date parsing (Format: 21-Feb-2026)
            if (!empty($data['Quotation_date'])) {
                try {
                    $quoteData['created_at'] = Carbon::createFromFormat('d-M-Y', $data['Quotation_date']);
                } catch (\Exception $e) {
                    Log::warning("Failed to parse date: " . $data['Quotation_date']);
                }
            }

            Quotation::updateOrCreate(
                ['zoho_quote_id' => $creatorId],
                $quoteData
            );
        });
    }
}
