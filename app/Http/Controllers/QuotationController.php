<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function print(Quotation $quotation, Request $request)
    {
        $type = $request->query('type', 'quotation'); // 'quotation' or 'contract'
        $quotation->load(['client', 'items']);
        
        return view('quotations.print', [
            'quotation' => $quotation,
            'client' => $quotation->client,
            'items' => $quotation->items,
            'type' => $type,
        ]);
    }
}
