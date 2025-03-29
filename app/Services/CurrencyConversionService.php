<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CurrencyConversionService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.currencyapi.key');
        $this->baseUrl = config('services.currencyapi.url');
    }

    public function convert($from, $to, $amount)
    {
        $response = Http::get($this->baseUrl, [
            'apikey' => $this->apiKey,
            'currencies' => $to,
            'base_currency' => $from
        ]);

        if ($response->successful()) {
            $rate = $response->json()['data'][$to]['value'];
            return $amount * $rate;
        }

        throw new \Exception('Currency conversion failed.');
    }

    public function convertForWallet($walletCurrency, $targetCurrency, $amount)
{
    if ($walletCurrency === $targetCurrency) {
        return $amount; // No conversion needed if the currencies are the same
    }

    return $this->convert($walletCurrency, $targetCurrency, $amount);
}

}
