<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== SETTING UP INR CURRENCY ===\n\n";

// Check if INR already exists
$inrCurrency = App\Models\Currency::where('code', 'INR')->first();

if ($inrCurrency) {
    echo "INR currency already exists (ID: {$inrCurrency->id})\n";
} else {
    // Create INR currency
    $inrCurrency = new App\Models\Currency();
    $inrCurrency->name = 'Indian Rupee';
    $inrCurrency->code = 'INR';
    $inrCurrency->symbol = '₹';
    $inrCurrency->exchange_rate = 1.00; // Base rate
    $inrCurrency->status = 1; // Active
    $inrCurrency->save();
    
    echo "✓ Created INR currency (ID: {$inrCurrency->id})\n";
}

// Update system default currency to INR
$setting = App\Models\BusinessSetting::where('type', 'system_default_currency')->first();
if ($setting) {
    $setting->value = $inrCurrency->id;
    $setting->save();
    echo "✓ Set INR as default currency\n";
} else {
    App\Models\BusinessSetting::create([
        'type' => 'system_default_currency',
        'value' => $inrCurrency->id
    ]);
    echo "✓ Created default currency setting with INR\n";
}

// Set all other currencies to have exchange rates relative to INR
echo "\n=== UPDATING EXCHANGE RATES ===\n";

// Common exchange rates (approximate, you may want to update these)
$exchangeRates = [
    'USD' => 83.50,  // 1 USD = 83.50 INR
    'EUR' => 91.20,  // 1 EUR = 91.20 INR  
    'GBP' => 106.80, // 1 GBP = 106.80 INR
    'AUD' => 54.60,  // 1 AUD = 54.60 INR
    'CAD' => 61.20,  // 1 CAD = 61.20 INR
    'SGD' => 62.30,  // 1 SGD = 62.30 INR
    'AED' => 22.75,  // 1 AED = 22.75 INR
    'SAR' => 22.25,  // 1 SAR = 22.25 INR
    'JPY' => 0.56,   // 1 JPY = 0.56 INR
    'CNY' => 11.75,  // 1 CNY = 11.75 INR
];

foreach ($exchangeRates as $code => $rate) {
    $currency = App\Models\Currency::where('code', $code)->first();
    if ($currency) {
        $currency->exchange_rate = $rate;
        $currency->save();
        echo "✓ Updated {$code} rate to {$rate} INR\n";
    }
}

// Set INR exchange rate to 1 (base currency)
$inrCurrency->exchange_rate = 1.00;
$inrCurrency->save();
echo "✓ Set INR base rate to 1.00\n";

echo "\n=== CURRENCY SETUP COMPLETE ===\n";
echo "Default Currency: INR (₹)\n";
echo "All product prices will now display in Indian Rupees\n";
echo "Exchange rates updated for major currencies\n";

// Display current currency status
echo "\n=== CURRENT CURRENCIES ===\n";
$currencies = App\Models\Currency::where('status', 1)->orderBy('code')->get();
foreach ($currencies as $currency) {
    $isDefault = ($currency->id == get_setting('system_default_currency')) ? ' (DEFAULT)' : '';
    echo "{$currency->code} - {$currency->name} - Rate: {$currency->exchange_rate} - Symbol: {$currency->symbol}{$isDefault}\n";
}

echo "\n=== SAMPLE PRODUCT PRICES ===\n";
$sampleProducts = App\Models\Product::take(5)->get();
foreach ($sampleProducts as $product) {
    echo "Product: {$product->name} - Price: ₹{$product->unit_price}\n";
}
?>
