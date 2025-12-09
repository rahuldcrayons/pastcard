<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== TESTING CSV READING ===\n\n";

// First check if database has any products
$existingProducts = App\Models\Product::count();
echo "Existing products in database: {$existingProducts}\n\n";

$csvFile = 'product.csv';
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle, 0, ',', '"', '\\');

// Show header
echo "CSV Header columns:\n";
foreach ($header as $i => $col) {
    if ($i < 10 || $col == 'post_title' || $col == 'sku') {
        echo "  [{$i}] {$col}\n";
    }
}

echo "\nTesting first 5 data rows:\n";
echo str_repeat("-", 60) . "\n";

for ($j = 0; $j < 5; $j++) {
    $row = fgetcsv($handle, 0, ',', '"', '\\');
    if (!$row) break;
    
    echo "\nRow " . ($j + 1) . ":\n";
    
    // Check column 0 (should be post_title)
    $name = isset($row[0]) ? trim($row[0]) : '[EMPTY]';
    $sku = isset($row[12]) ? trim($row[12]) : '[EMPTY]';
    
    echo "  Column 0 (name): {$name}\n";
    echo "  Column 12 (sku): {$sku}\n";
    
    // Check if SKU exists in database
    if (!empty($sku) && $sku != '[EMPTY]') {
        $exists = App\Models\Product::where('sku', $sku)->exists();
        echo "  SKU exists in DB: " . ($exists ? 'YES (will skip)' : 'NO (can import)') . "\n";
    }
    
    // Check why it might be skipped
    if (empty($name) || $name == '[EMPTY]') {
        echo "  ❌ SKIP REASON: Empty product name\n";
    } elseif (!empty($sku) && App\Models\Product::where('sku', $sku)->exists()) {
        echo "  ❌ SKIP REASON: SKU already exists\n";
    } else {
        echo "  ✅ Should be able to import\n";
    }
}

fclose($handle);

// Clear any existing products if needed
echo "\n\n💡 RECOMMENDATION:\n";
if ($existingProducts > 0) {
    echo "Database has {$existingProducts} existing products.\n";
    echo "To import fresh data, first run:\n";
    echo "  php artisan tinker\n";
    echo "  >>> App\\Models\\Product::truncate();\n";
    echo "  >>> DB::table('product_stocks')->truncate();\n";
} else {
    echo "Database is empty. Products should import if names are not empty.\n";
}

echo "\n✅ Test complete!\n";
?>
