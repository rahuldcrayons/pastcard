<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== CSV DIAGNOSTIC ===\n\n";

$csvFile = 'product.csv';
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle, 0, ',', '"', '\\');

// Map columns
$columns = [];
foreach ($header as $i => $col) {
    $columns[trim($col)] = $i;
}

echo "Total columns: " . count($columns) . "\n";
echo "Key columns check:\n";
echo "  - post_title: " . (isset($columns['post_title']) ? "✓ at index {$columns['post_title']}" : "✗") . "\n";
echo "  - sku: " . (isset($columns['sku']) ? "✓ at index {$columns['sku']}" : "✗") . "\n";
echo "  - post_status: " . (isset($columns['post_status']) ? "✓ at index {$columns['post_status']}" : "✗") . "\n";
echo "  - regular_price: " . (isset($columns['regular_price']) ? "✓ at index {$columns['regular_price']}" : "✗") . "\n";
echo "  - stock: " . (isset($columns['stock']) ? "✓ at index {$columns['stock']}" : "✗") . "\n";
echo "  - tax:product_cat: " . (isset($columns['tax:product_cat']) ? "✓ at index {$columns['tax:product_cat']}" : "✗") . "\n\n";

echo "First 10 products:\n";
echo str_repeat("-", 60) . "\n";

$count = 0;
$emptyNames = 0;
$emptySKUs = 0;
$duplicateSKUs = [];
$seenSKUs = [];

while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false && $count < 10) {
    $count++;
    
    $name = isset($columns['post_title']) && isset($row[$columns['post_title']]) ? 
            trim($row[$columns['post_title']]) : '';
    $sku = isset($columns['sku']) && isset($row[$columns['sku']]) ? 
           trim($row[$columns['sku']]) : '';
    $status = isset($columns['post_status']) && isset($row[$columns['post_status']]) ? 
             $row[$columns['post_status']] : '';
    $price = isset($columns['regular_price']) && isset($row[$columns['regular_price']]) ? 
            $row[$columns['regular_price']] : '';
    $category = isset($columns['tax:product_cat']) && isset($row[$columns['tax:product_cat']]) ? 
               $row[$columns['tax:product_cat']] : '';
    
    echo "\n{$count}. Product:\n";
    echo "   Name: " . ($name ?: "[EMPTY]") . "\n";
    echo "   SKU: " . ($sku ?: "[EMPTY]") . "\n";
    echo "   Status: {$status}\n";
    echo "   Price: {$price}\n";
    echo "   Category: " . substr($category, 0, 50) . "\n";
    
    if (empty($name)) $emptyNames++;
    if (empty($sku)) $emptySKUs++;
    
    if (!empty($sku)) {
        if (in_array($sku, $seenSKUs)) {
            $duplicateSKUs[] = $sku;
        }
        $seenSKUs[] = $sku;
    }
}

// Check full file for issues
rewind($handle);
fgetcsv($handle); // Skip header

$totalRows = 0;
$totalEmptyNames = 0;
$totalEmptySKUs = 0;
$totalEmptyBoth = 0;
$uniqueSKUs = [];

while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
    $totalRows++;
    
    $name = isset($columns['post_title']) && isset($row[$columns['post_title']]) ? 
            trim($row[$columns['post_title']]) : '';
    $sku = isset($columns['sku']) && isset($row[$columns['sku']]) ? 
           trim($row[$columns['sku']]) : '';
    
    if (empty($name)) $totalEmptyNames++;
    if (empty($sku)) $totalEmptySKUs++;
    if (empty($name) && empty($sku)) $totalEmptyBoth++;
    
    if (!empty($sku) && !in_array($sku, $uniqueSKUs)) {
        $uniqueSKUs[] = $sku;
    }
}

fclose($handle);

echo "\n\n📊 ANALYSIS RESULTS:\n";
echo str_repeat("=", 60) . "\n";
echo "Total rows: {$totalRows}\n";
echo "Rows with empty names: {$totalEmptyNames}\n";
echo "Rows with empty SKUs: {$totalEmptySKUs}\n";
echo "Rows with both empty: {$totalEmptyBoth}\n";
echo "Unique SKUs: " . count($uniqueSKUs) . "\n";

// Check if products already exist in database
$existingProducts = App\Models\Product::count();
echo "\nDatabase products: {$existingProducts}\n";

if ($existingProducts > 0) {
    echo "\n⚠️ Database already has products. Checking for SKU conflicts...\n";
    
    $existingSKUs = App\Models\Product::pluck('sku')->toArray();
    $conflicts = array_intersect($uniqueSKUs, $existingSKUs);
    
    echo "SKU conflicts: " . count($conflicts) . "\n";
    
    if (count($conflicts) > 0) {
        echo "Sample conflicting SKUs:\n";
        foreach (array_slice($conflicts, 0, 5) as $sku) {
            echo "  - {$sku}\n";
        }
    }
}

echo "\n💡 RECOMMENDATIONS:\n";

if ($totalEmptyNames == $totalRows) {
    echo "❌ All rows have empty product names! Check if 'post_title' column has data.\n";
}

if (count($uniqueSKUs) == 0) {
    echo "❌ No valid SKUs found! Products need unique SKUs to import.\n";
}

if ($existingProducts > 0 && count($conflicts) > 0) {
    echo "⚠️ Products with same SKUs already exist. Clear database first or use different SKUs.\n";
}

echo "\n✅ Diagnosis complete!\n";
?>
