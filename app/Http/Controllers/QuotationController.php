<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function print(Quotation $quotation, Request $request)
    {
        $type = $request->query('type', 'quotation'); // 'quotation' or 'contract'
        
        // Handle Zoho Creator Records (Proxy PDF directly)
        if ($quotation->zoho_module === 'ZohoCreator_ModernLife' && !empty($quotation->zoho_quote_id)) {
            $creatorService = app(\App\Services\Zoho\ZohoCreatorService::class);
            
            // Map type to report link name (Using verified names from Zoho Reports List)
            $quoteReport = config('zoho.creator_quotes_report', 'Modern_Life_Quotations');
            $contractReport = config('zoho.creator_contracts_report', 'Modern_Life_Contracts');
            $changeOrderReport = config('zoho.creator_change_orders_report', 'Modern_Life_Change_Orders');

            // Logic: If it's Additional Work, we use the Change Orders report for the "Contract/Change" button
            if ($type === 'contract') {
                $report = (str_contains($quotation->contract_type, 'Additional') || str_contains($quotation->contract_type, 'إضافية'))
                    ? $changeOrderReport
                    : $contractReport;
            } else {
                $report = $quoteReport;
            }

            // Proxy PDF via API (Requires Zoho API connectivity)
            $pdfContent = $creatorService->getRecordPdf($report, $quotation->zoho_quote_id);

            if ($pdfContent) {
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', "inline; filename=\"{$type}_{$quotation->zoho_quote_id}.pdf\"");
            }

            Log::error("QuotationController: Proxy failed for record {$quotation->zoho_quote_id} in report {$report}. Check ZohoCreatorService logs.");
            return response()->json(['error' => 'Failed to fetch document from Zoho.'], 404);
        }

        // Standard Local Printing
        $quotation->load(['client', 'items']);
        
        return view('quotations.print', [
            'quotation' => $quotation,
            'client' => $quotation->client,
            'items' => $quotation->items,
            'type' => $type,
        ]);
    }
}
