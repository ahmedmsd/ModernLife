<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Zoho\ZohoCrmService;
use App\Models\Client;
use App\Models\Quotation;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SyncZohoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:sync {module? : The module to sync (accounts, quotes, sales_orders). If empty, syncs all.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data from Zoho CRM (Accounts, Quotes, Sales Orders)';

    protected ZohoCrmService $zohoService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ZohoCrmService $zohoService)
    {
        parent::__construct();
        $this->zohoService = $zohoService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        set_time_limit(0); 
        ini_set('memory_limit', '512M');
        DB::connection()->disableQueryLog();
        $module = $this->argument('module');

        if ($module) {
            $this->syncModule($module);
        } else {
            $this->syncModule('accounts');
            $this->syncModule('contacts');
            $this->syncModule('quotes');
            $this->syncModule('sales_orders');
        }

        return 0;
    }

    protected $contactAccountMap = [];

    protected function syncModule($module)
    {
        $this->info("Syncing {$module}...");

        switch ($module) {
            case 'accounts':
                $this->syncAccounts();
                break;
            case 'contacts':
                $this->syncContacts();
                break;
            case 'quotes':
                $this->syncQuotes('Quotations'); // Commercial
                $this->syncQuotes('Residential_Quotations'); // Residential
                $this->syncQuotes('Construction_Quotation'); 
                $this->syncQuotes('Woodwork_Quotation');
                $this->syncQuotes('Residential_Packages');
                break;
            case 'sales_orders':
                // We sync contacts map first to handle deals linked to contacts
                $this->syncContactMap();
                $this->syncSalesOrders('Deals'); // Projects (Deals)
                $this->syncSalesOrders('Sales_Orders'); // Specific Sales Orders
                break;
            default:
                $this->error("Unknown module: {$module}");
        }

        $this->info("{$module} sync complete.");
    }

    protected function syncContactMap()
    {
        $this->info("Building Contact to Account mapping...");
        $page = 1;
        do {
            $records = $this->zohoService->getRecords('Contacts', $page);
            foreach ($records as $record) {
                if (!empty($record['Account_Name']['id'])) {
                    $this->contactAccountMap[$record['id']] = $record['Account_Name']['id'];
                }
            }
            $page++;
        } while (count($records) > 0 && $page <= 5); // Scanned enough for recent deals
    }

    protected function syncAccounts()
    {
        $page = 1;
        do {
            $records = $this->zohoService->getRecords('Accounts', $page);
            foreach ($records as $record) {
                $this->processAccount($record);
            }
            $page++;
            gc_collect_cycles();
        } while (count($records) > 1);
    }

    protected function processAccount($data)
    {
        Client::updateOrCreate(
            ['zoho_account_id' => $data['id']],
            [
                'client_name' => $data['Account_Name'] ?? $data['Company_Name'] ?? 'Unknown',
                'phone'       => $data['Phone'] ?? '',
                'is_active'   => 1,
            ]
        );
    }

    protected function syncContacts()
    {
        $page = 1;
        do {
            $records = $this->zohoService->getRecords('Contacts', $page);
            foreach ($records as $record) {
                $this->processContact($record);
            }
            $page++;
            gc_collect_cycles();
        } while (count($records) > 1);
    }

    protected function processContact($data)
    {
        $accountId = $data['Account_Name']['id'] ?? null;
        Log::info("Processing Contact: " . ($data['Full_Name'] ?? 'No Name') . " | Account: " . ($accountId ?? 'NONE'));
        
        // If it's a B2C contact (no account), create a client record for them
        if (!$accountId) {
             $client = Client::updateOrCreate(
                ['zoho_contact_id' => $data['id']],
                [
                    'client_name'     => $data['Full_Name'] ?? ($data['First_Name'] . ' ' . $data['Last_Name']),
                    'phone'           => $data['Phone'] ?? $data['Mobile'] ?? '',
                    'is_active'       => 1,
                ]
            );
            Log::info("B2C Client created/updated: " . $client->client_name . " (ID: " . $client->client_id . ")");
        }
    }

    protected function syncQuotes($module)
    {
        $this->info("Syncing modules from Zoho: {$module}");
        $page = 1;
        do {
            $records = $this->zohoService->getRecords($module, $page);
            foreach ($records as $record) {
                $this->processQuote($record, $module);
            }
            $page++;
            gc_collect_cycles();
        } while (count($records) > 0);
    }

    protected function processQuote($data, $moduleType)
    {
        DB::transaction(function () use ($data, $moduleType) {
            $isResidential = $moduleType === 'Residential_Quotations';
            
            // Find client
            $accountId = $data['Account_Name']['id'] ?? $data['Company_Name']['id'] ?? null;
            $contactId = $data['Contact_Name']['id'] ?? $data['Contact_Person']['id'] ?? $data['Customer_Name']['id'] ?? null;
            
            $client = null;
            if ($accountId) {
                $client = Client::where('zoho_account_id', $accountId)->first();
            }
            if (!$client && $contactId) {
                $client = Client::where('zoho_contact_id', $contactId)->first();
            }

            // Also check for creator-style mapping if names match but IDs differ (fallback)
            if (!$client) {
                $clientName = $data['Account_Name']['name'] ?? $data['Company_Name']['name'] ?? $data['Customer_Name']['name'] ?? $data['Contact_Person']['name'] ?? null;
                if ($clientName) {
                    $client = Client::where('client_name', $clientName)->first();
                }
            }

            $quoteNumber = $data['Quotation_No'] ?? $data['Quote_Number'] ?? $data['Estimate_No'] ?? ($data['Name'] ?? null);
            $subject = $data['Subject'] ?? $data['Deal_Name'] ?? $data['Quotation_Name'] ?? $data['Name'] ?? ($moduleType . ' - ' . $quoteNumber);

            $quote = Quotation::updateOrCreate(
                ['zoho_quote_id' => $data['id']],
                [
                    'subject'       => $subject,
                    'quote_number'  => $quoteNumber,
                    'quote_stage'   => $data['Quotation_Stage'] ?? $data['Stage'] ?? $data['Quote_Stage'] ?? $data['Status'] ?? $data['$approval_state'] ?? null,
                    'zoho_module'   => $moduleType,
                    'contract_type' => $data['Contract_Type'] ?? $data['Quote_Type'] ?? $data['Project_Type'] ?? ($moduleType === 'Residential_Quotations' || $moduleType === 'Residential_Packages' ? 'Residential' : null),
                    'valid_till'    => isset($data['Valid_Till']) ? Carbon::parse($data['Valid_Till']) : (isset($data['Quotation_Valid_Until']) ? Carbon::parse($data['Quotation_Valid_Until']) : null),
                    'total_amount'  => $data['Total_Inc_VAT'] ?? $data['Total_Inc_VAT1'] ?? $data['Net_Amount'] ?? $data['Grand_Total'] ?? $data['Amount'] ?? $data['Total'] ?? 0,
                    'sub_total'    => $data['Total_Price_After_Discount'] ?? $data['Sub_Total'] ?? $data['Total_Exc_VAT'] ?? $data['Total_Exc_VAT1'] ?? $data['Total'] ?? 0,
                    'tax'          => $data['VAT_Amount'] ?? $data['VAT_Amount1'] ?? $data['Tax'] ?? $data['VAT_Amount'] ?? 0,
                    'adjustment'   => $data['Adjustment'] ?? 0,
                    'discount'     => $data['Total_Discount'] ?? $data['Discount'] ?? $data['Discount_Amount'] ?? 0,
                    'client_id'    => $client ? $client->client_id : null,
                    'raw_data'     => $data,
                ]
            );

            // Sync Items: Handle both standard subforms and custom "Service" fields
            $quote->items()->delete();
            $items = $data['Quoted_Items'] ?? $data['Product_Details'] ?? $data['Items'] ?? $data['Quotation_Items'] ?? $data['Construction_Details'] ?? $data['Woodwork_Details'] ?? $data['Residential_Packages_Details'] ?? [];
            
            // 1. Standard Subforms
            if (is_array($items) && !empty($items)) {
                foreach ($items as $item) {
                    $quote->items()->create([
                        'zoho_line_item_id' => $item['id'] ?? null,
                        'product_name'      => $item['product']['name'] ?? $item['Product_Name'] ?? $item['Item_Name'] ?? 'Item',
                        'product_id'        => $item['product']['id'] ?? $item['Product_ID'] ?? null,
                        'quantity'          => $item['quantity'] ?? $item['Quantity'] ?? 1,
                        'list_price'        => $item['list_price'] ?? $item['List_Price'] ?? 0,
                        'unit_price'        => $item['unit_price'] ?? $item['Unit_Price'] ?? 0,
                        'discount'          => $item['discount'] ?? $item['Discount'] ?? 0,
                        'tax'               => $item['tax'] ?? $item['Tax'] ?? 0,
                        'total'             => $item['total'] ?? $item['Total'] ?? 0,
                    ]);
                }
            } 
            
            // 2. Custom "Service" Fields (Fallback if no standard items)
            if ($quote->items()->count() === 0) {
                $patterns = [
                    'Standard_Service_',
                    'Detail_Drawings_Service_',
                    'Material_Reference_Service_',
                    'Branding_Service_',
                    'Furniture_Reference_Service_',
                    'Detail_Drawing_Service_',
                    'Elevation_Facade_Service_'
                ];

                foreach ($patterns as $prefix) {
                    for ($i = 1; $i <= 10; $i++) {
                        $key = $prefix . $i;
                        if (!empty($data[$key])) {
                            // Extract Name and Description if separated by slash or newline
                            $content = $data[$key];
                            $parts = explode('/', $content, 2);
                            $name = trim($parts[0]);
                            $desc = isset($parts[1]) ? trim($parts[1]) : '';

                            $quote->items()->create([
                                'product_name' => $name,
                                'description'  => $desc,
                                'quantity'     => 1,
                                'unit_price'   => 0,
                                'total'        => 0,
                            ]);
                        }
                    }
                }
            }

            // 3. Custom Amount Fields (Fallback or Addition)
            $amountFields = [
                'Detail_Drawings_Amount' => 'الرسومات التفصيلية (Detail Drawings)',
                'Material_Take_Off_Amount' => 'حصر الكميات (Material Take-off)',
                'Furniture_Reference_Amount' => 'مرجع الأثاث (Furniture Reference)',
                'Detail_Drawing' => 'رسوم التصميم (Design Fees)',
                'Branding_Amount' => 'رسوم الهوية (Branding Fees)',
                'Material_Take_Off' => 'حصر الكميات (MTO)',
            ];

            foreach($amountFields as $field => $label) {
                if (!empty($data[$field]) && is_numeric($data[$field]) && $data[$field] > 0) {
                    // Check if already added via standard items to avoid duplication
                    $exists = $quote->items()->where('product_name', 'like', "%$label%")->exists();
                    if (!$exists) {
                        $quote->items()->create([
                            'product_name' => $label,
                            'quantity'     => 1,
                            'unit_price'   => $data[$field],
                            'total'        => $data[$field],
                        ]);
                    }
                }
            }
        });
    }

    protected function syncSalesOrders($moduleName = 'Deals')
    {
        $this->info("Syncing modules from Zoho: {$moduleName}");
        $page = 1;
        do {
            $records = $this->zohoService->getRecords($moduleName, $page);
            foreach ($records as $record) {
                $this->processSalesOrder($record, $moduleName);
            }
            $page++;
            gc_collect_cycles();
        } while (count($records) > 0);
    }

    protected function processSalesOrder($data, $moduleType)
    {
        DB::transaction(function () use ($data, $moduleType) {
            $accountId = $data['Account_Name']['id'] ?? $data['Company_Name']['id'] ?? null;
            $contactId = $data['Contact_Name']['id'] ?? null;

            $client = null;
            if ($accountId) {
                $client = Client::where('zoho_account_id', $accountId)->first();
            }
            if (!$client && $contactId) {
                $client = Client::where('zoho_contact_id', $contactId)->first();
            }
            
            // Still no account? Maybe it exists in our map from syncContactMap
            if (!$client && $contactId) {
                $mappedAccountId = $this->contactAccountMap[$contactId] ?? null;
                if ($mappedAccountId) {
                    $client = Client::where('zoho_account_id', $mappedAccountId)->first();
                }
            }

            $order = SalesOrder::updateOrCreate(
                ['zoho_so_id' => $data['id']],
                [
                    'subject'      => $data['Deal_Name'] ?? $data['Subject'] ?? 'Project/Order',
                    'so_number'    => $data['Project_No'] ?? $data['SO_Number'] ?? $data['id'],
                    'status'       => $data['Stage'] ?? $data['Status'] ?? null,
                    'total_amount' => $data['Amount'] ?? $data['Grand_Total'] ?? 0,
                    'zoho_module'  => $moduleType,
                    'client_id'    => $client ? $client->client_id : null,
                    'raw_data'     => $data,
                ]
            );

            // Deals often don't have standard line items unless customized
            // Sync Items: Handle both standard subforms and custom "Service" fields
            $order->items()->delete();
            $items = $data['Product_Details'] ?? $data['Line_Items'] ?? $data['Items'] ?? [];
            
            // 1. Standard Subforms
            if (is_array($items) && !empty($items)) {
                foreach ($items as $item) {
                     $order->items()->create([
                        'zoho_line_item_id' => $item['id'] ?? null,
                        'product_name'      => $item['product']['name'] ?? $item['Product_Name'] ?? 'Item',
                        'product_id'        => $item['product']['id'] ?? $item['Product_ID'] ?? null,
                        'quantity'          => $item['quantity'] ?? 1,
                        'total'             => $item['total'] ?? $item['Amount'] ?? 0,
                    ]);
                }
            }

            // 2. Custom "Service" Fields (Fallback)
            if ($order->items()->count() === 0) {
                $patterns = [
                    'Standard_Service_',
                    'Detail_Drawings_Service_',
                    'Material_Reference_Service_',
                    'Branding_Service_',
                    'Furniture_Reference_Service_'
                ];

                foreach ($patterns as $prefix) {
                    for ($i = 1; $i <= 10; $i++) {
                        $key = $prefix . $i;
                        if (!empty($data[$key])) {
                             $content = $data[$key];
                            $parts = explode('/', $content, 2);
                            $name = trim($parts[0]);
                            
                            $order->items()->create([
                                'product_name' => $name,
                                'quantity'     => 1,
                                'total'        => 0,
                            ]);
                        }
                    }
                }
            }
        });
    }
}
