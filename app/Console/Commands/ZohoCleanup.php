<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Support\Facades\DB;

class ZohoCleanup extends Command
{
    protected $signature = 'zoho:cleanup';
    protected $description = 'Remove incorrectly synced Zoho modules (Construction, Woodwork, Packages)';

    protected array $removedModules = [
        'Construction_Quotation',
        'Woodwork_Quotation',
        'Residential_Packages',
    ];

    public function handle(): int
    {
        $this->info('Starting Zoho data cleanup...');
        $this->info('Modules to remove: ' . implode(', ', $this->removedModules));

        DB::transaction(function () {
            $quotationIds = Quotation::whereIn('zoho_module', $this->removedModules)
                ->pluck('id');

            if ($quotationIds->isEmpty()) {
                $this->warn('No records found for the specified modules. Nothing to delete.');
                return;
            }

            $itemsDeleted = QuotationItem::whereIn('quotation_id', $quotationIds)->delete();
            $this->info("Deleted {$itemsDeleted} quotation item(s).");

            $quotationsDeleted = Quotation::whereIn('zoho_module', $this->removedModules)->delete();
            $this->info("Deleted {$quotationsDeleted} quotation(s).");
        });

        $this->info('Cleanup completed successfully.');

        return 0;
    }
}
