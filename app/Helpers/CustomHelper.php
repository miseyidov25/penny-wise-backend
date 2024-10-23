<?php

function convertCurrency($amount, $fromCurrency, $toCurrency)
    {
    // Example conversion rates (you might want to fetch these from a reliable API)
    $exchangeRates = [
        'USD' => 0.85, // 1 USD = 0.85 EUR
        'EUR' => 1.00, // 1 EUR = 1 EUR
        // Add more currencies as needed
    ];

    if ($fromCurrency === $toCurrency) {
        return (float)$amount; // No conversion needed
    }

    if (!isset($exchangeRates[$fromCurrency]) || !isset($exchangeRates[$toCurrency])) {
        return 0; // Return 0 if the currency is not supported
    }

    // Convert amount to EUR as primary currency
    $amountInEur = $amount * $exchangeRates[$fromCurrency];

    // Now convert it to the target currency if necessary
    return $amountInEur / $exchangeRates[$toCurrency];
}


