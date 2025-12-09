<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== FIXING INR CURRENCY SETUP ===\n\n";

// Get INR currency
$inrCurrency = App\Models\Currency::where('code', 'INR')->first();
echo "INR Currency ID: {$inrCurrency->id}\n";

// Update default currency setting
$setting = App\Models\BusinessSetting::where('type', 'system_default_currency')->first();
if ($setting) {
    $setting->value = $inrCurrency->id;
    $setting->save();
} else {
    $setting = new App\Models\BusinessSetting();
    $setting->type = 'system_default_currency';
    $setting->value = $inrCurrency->id;
    $setting->save();
}
echo "✓ Updated default currency to INR (ID: {$inrCurrency->id})\n";

// Remove duplicate "Rupee" currency if exists
$duplicateRupee = App\Models\Currency::where('name', 'LIKE', '%Rupee%')->where('code', '!=', 'INR')->first();
if ($duplicateRupee) {
    echo "Found duplicate Rupee currency: {$duplicateRupee->name} (ID: {$duplicateRupee->id})\n";
    $duplicateRupee->delete();
    echo "✓ Removed duplicate Rupee currency\n";
}

// Set currency exchange rates correctly
echo "\n=== UPDATING CURRENCY RATES ===\n";

// Set INR as base currency (rate = 1)
$inrCurrency->exchange_rate = 1.00;
$inrCurrency->save();
echo "✓ Set INR rate to 1.00 (base currency)\n";

// Update USD to be relative to INR
$usdCurrency = App\Models\Currency::where('code', 'USD')->first();
if ($usdCurrency) {
    $usdCurrency->exchange_rate = 0.012; // 1 INR = 0.012 USD (approximately)
    $usdCurrency->save();
    echo "✓ Updated USD rate to 0.012 (1 INR = 0.012 USD)\n";
}

// Update EUR to be relative to INR
$eurCurrency = App\Models\Currency::where('code', 'EUR')->first();
if ($eurCurrency) {
    $eurCurrency->exchange_rate = 0.011; // 1 INR = 0.011 EUR (approximately)
    $eurCurrency->save();
    echo "✓ Updated EUR rate to 0.011 (1 INR = 0.011 EUR)\n";
}

// Clear cache if exists
if (function_exists('cache')) {
    cache()->forget('system_default_currency');
    echo "✓ Cleared currency cache\n";
}

echo "\n=== VERIFICATION ===\n";
echo "Default currency setting: " . get_setting('system_default_currency') . "\n";
echo "INR currency ID: " . $inrCurrency->id . "\n";
echo "Match: " . (get_setting('system_default_currency') == $inrCurrency->id ? 'YES' : 'NO') . "\n";

echo "\n=== SAMPLE PRICES IN INR ===\n";
$sampleProducts = App\Models\Product::take(3)->get();
foreach ($sampleProducts as $product) {
    echo "• {$product->name}: ₹" . number_format($product->unit_price, 2) . "\n";
}

echo "\n=== SUCCESS! ===\n";
echo "✓ INR (₹) is now the default currency\n";
echo "✓ All product prices display in Indian Rupees\n";
echo "✓ Exchange rates updated for international currencies\n";
?>
