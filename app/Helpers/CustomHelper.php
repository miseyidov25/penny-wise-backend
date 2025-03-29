<?php

function convertCurrency($amount, $fromCurrency, $toCurrency)
{
    // Example conversion rates (values are hypothetical)
    $exchangeRates = [
        'USD' => 0.85, 'EUR' => 1.00, 'GBP' => 1.17, 'JPY' => 0.0078, 'CAD' => 0.63,
        'AUD' => 0.59, 'CHF' => 0.92, 'CNY' => 0.13, 'SEK' => 0.094, 'NZD' => 0.56,
        'MXN' => 0.043, 'SGD' => 0.65, 'HKD' => 0.11, 'NOK' => 0.096, 'KRW' => 0.00068,
        'TRY' => 0.085, 'RUB' => 0.0094, 'INR' => 0.011, 'BRL' => 0.17, 'ZAR' => 0.053,
        'DKK' => 0.13, 'PLN' => 0.22, 'THB' => 0.026, 'IDR' => 0.000059, 'HUF' => 0.0026,
        'CZK' => 0.041, 'ILS' => 0.23, 'CLP' => 0.0011, 'PHP' => 0.018, 'AED' => 0.23,
        'COP' => 0.00022, 'SAR' => 0.23, 'MYR' => 0.21, 'RON' => 0.20, 'EGP' => 0.055,
        'QAR' => 0.24, 'BGN' => 0.51, 'PKR' => 0.0055, 'NGN' => 0.0011, 'KWD' => 2.77,
        'BDT' => 0.0094, 'LKR' => 0.0026, 'JOD' => 1.20, 'OMR' => 2.20, 'ISK' => 0.0071,
        'KES' => 0.0074, 'BHD' => 2.65, 'GHS' => 0.085, 'TZS' => 0.00043, 'UGX' => 0.00023,
        'MAD' => 0.095, 'RSD' => 0.0085, 'XOF' => 0.0015, 'XAF' => 0.0015, 'BWP' => 0.075,
        'ETB' => 0.015, 'CRC' => 0.0016, 'MUR' => 0.019, 'MMK' => 0.0005, 'DZD' => 0.0063,
        'LAK' => 0.000043, 'MKD' => 0.016, 'GEL' => 0.34, 'BAM' => 0.51, 'ALL' => 0.0093,
        'MNT' => 0.0003, 'ZMW' => 0.048, 'AMD' => 0.0021, 'AZN' => 0.50, 'SYP' => 0.0004,
        'HRK' => 0.13, 'BYN' => 0.33, 'KZT' => 0.0021, 'MGA' => 0.00021, 'MDL' => 0.050,
        'LRD' => 0.005, 'SCR' => 0.067, 'NAD' => 0.053, 'KYD' => 1.13, 'BBD' => 0.43,
        'BSD' => 0.85, 'TTD' => 0.13, 'BZD' => 0.43, 'SVC' => 0.097, 'AWG' => 0.47,
        'BTN' => 0.011, 'PGK' => 0.24, 'GYD' => 0.0041, 'SRD' => 0.023, 'XCD' => 0.32,
        'NPR' => 0.0085, 'MOP' => 0.11, 'CDF' => 0.00042, 'MRU' => 0.023, 'DJF' => 0.0047,
        'STN' => 0.037, 'ERN' => 0.057, 'SZL' => 0.053, 'LSL' => 0.053, 'KMF' => 0.0021,
        'VUV' => 0.0071, 'SOS' => 0.0015, 'AFN' => 0.009, 'BIF' => 0.00042, 'MWK' => 0.001,
        'GNF' => 0.0001, 'SLL' => 0.000095, 'MVR' => 0.055, 'AOA' => 0.0014, 'HTG' => 0.0068,
        'GIP' => 1.17, 'BND' => 0.65, 'FJD' => 0.40, 'TOP' => 0.36, 'WST' => 0.32,
        'KGS' => 0.0095, 'TJS' => 0.075, 'UZS' => 0.000085, 'XPF' => 0.0091, 'TMT' => 0.24,
        'ZWL' => 0.0027, 'VES' => 0.0003, 'TND' => 0.28, 'LYD' => 0.18, 'SDG' => 0.0017,
        'MZN' => 0.013, 'SSP' => 0.0011, 'YER' => 0.0034, 'GMD' => 0.016,'XAU' => 1800.00,
        'KPW' => 0.00094, 'TVD' => 0.62, 'ANG' => 0.47, 'SHP' => 1.17, 'RWF' => 0.00079, 
        'MRO' => 0.0028, 'XDR' => 1.31, 'BMD' => 0.85, 'GGP' => 1.15, 'IMP' => 1.15, 
        'JEP' => 1.15, 'KID' => 0.55, 'PAB' => 0.85, 'UYU' => 0.021, 'FOK' => 0.12, 
        'SPL' => 1.25, 'VES' => 0.0003, 'XUA' => 1.2, 'CUC' => 0.85,'NIO' => 0.023,
        'KRO' => 0.00068, 'GHS' => 0.084, 'SCR' => 0.068, 'TMT' => 0.24, 'BAM' => 0.51,
        'MRU' => 0.024, 'SVC' => 0.097, 'XPT' => 1040.00,'XPD' => 2300.00, 'XAG' => 25.00,
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
?>
