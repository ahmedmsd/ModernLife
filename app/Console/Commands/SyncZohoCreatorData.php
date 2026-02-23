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
        
        $reports = [
            config('zoho.creator_quotes_report', 'Modern_Life_Quotations'),
            config('zoho.creator_change_orders_report', 'Modern_Life_Change_Orders'),
        ];

        $totalSynced = 0;

        foreach ($reports as $reportName) {
            $this->info("Fetching from report: {$reportName}");
            $from = 0;
            $limit = 200;

            do {
                $result = $this->creatorService->getRecords($reportName, $from, $limit);

                if ($result === null) {
                    $this->error("  API Error: Failed to fetch records from report {$reportName}.");
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
        }

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


            $quoteData = [
                'subject'       => ($data['Contract_Type'] ?? 'Quotation') . ' - ' . $quoteNumber,
                'quote_number'  => $quoteNumber,
                'quote_stage'   => $data['Quotation_Stage'] ?? null,
                'zoho_module'   => 'ZohoCreator_ModernLife',
                'contract_type' => $data['Contract_Type'] ?? null,
                'total_amount'  => $data['Total_inc_VAT'] ?? 0,
                'client_id'    => $client ? $client->client_id : null,
                'customer_name' => $contactName,
                'sales_person' => $data['Owner']['name'] ?? null,
                'raw_data'     => $data,
                'quotation_pdf_url' => null, // Will be set below
                'contract_pdf_url' => null, // Will be set below
            ];

            // Date parsing (Format: 21-Feb-2026)
            if (!empty($data['Quotation_date'])) {
                try {
                    $quoteData['created_at'] = Carbon::createFromFormat('d-M-Y', $data['Quotation_date']);
                } catch (\Exception $e) {
                    Log::warning("SyncZohoCreatorData: Failed to parse Quotation_date: " . $data['Quotation_date']);
                }
            }

            if (!empty($data['Quotation_Valid_Until'])) {
                try {
                    $quoteData['valid_till'] = Carbon::createFromFormat('d-M-Y', $data['Quotation_Valid_Until']);
                } catch (\Exception $e) {
                    Log::warning("SyncZohoCreatorData: Failed to parse valid_till: " . $data['Quotation_Valid_Until']);
                }
            }

            $quotation = Quotation::updateOrCreate(
                ['zoho_quote_id' => $creatorId],
                $quoteData
            );

            // PDF URLs Logic
            $quotesHash = config('zoho.creator_quotes_hash');
            $contractsHash = config('zoho.creator_contracts_hash');
            $owner = config('zoho.creator_owner_name');
            $appName = config('zoho.creator_app_link_name');

            // If Hashes are available, generate public "Published" URLs.
            // These bypass Zoho login entirely.
            if ($quotesHash && $contractsHash) {
                $quoteReport = config('zoho.creator_quotes_report', 'Modern_Life_Quotations');
                $contractReport = config('zoho.creator_contracts_report', 'Modern_Life_Contracts');
                $changeOrderReport = config('zoho.creator_change_orders_report', 'Modern_Life_Change_Orders');
                $portalBase = config('zoho.creator_portal_base', 'https://creatorapp.zoho.com');
                
                $quoteUrl = "{$portalBase}/publish/{$owner}/{$appName}/record-print/{$quoteReport}/{$creatorId}/{$quotesHash}";
                
                // If it's Additional Work, use Change Orders report for the contract URL
                $cReport = (str_contains($data['Contract_Type'] ?? '', 'Additional') || str_contains($data['Contract_Type'] ?? '', 'إضافية')) 
                    ? $changeOrderReport 
                    : $contractReport;

                $contractUrl = "{$portalBase}/publish/{$owner}/{$appName}/record-print/{$cReport}/{$creatorId}/{$contractsHash}";
            } else {
                // Fallback to internal proxy routes (Requires Zoho API access)
                $quoteUrl = route('quotations.print', ['quotation' => $quotation->id, 'type' => 'quotation']);
                $contractUrl = route('quotations.print', ['quotation' => $quotation->id, 'type' => 'contract']);
            }

            $quotation->update([
                'quotation_pdf_url' => $quoteUrl,
                'contract_pdf_url'  => $contractUrl,
            ]);
        });
    }
}
