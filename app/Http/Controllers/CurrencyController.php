<?php

namespace App\Http\Controllers;

use App\Services\CurrencyConversionService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyConversionService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function convertCurrency(Request $request)
    {
        $from = $request->input('from', 'USD');  // Base currency
        $to = $request->input('to', 'EUR');      // Target currency
        $amount = $request->input('amount', 1);  // Amount to convert

        try {
            $convertedAmount = $this->currencyService->convert($from, $to, $amount);
            return response()->json([
                'from' => $from,
                'to' => $to,
                'amount' => $amount,
                'convertedAmount' => $convertedAmount
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
