<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== CURRENT DATABASE STATUS ===\n\n";

$products = App\Models\Product::count();
$categories = App\Models\Category::count();
$parentCategories = App\Models\Category::where('parent_id', 0)->count();
$childCategories = App\Models\Category::where('parent_id', '>', 0)->count();

echo "Products: {$products}\n";
echo "Categories: {$categories}\n";
echo "  - Parent categories: {$parentCategories}\n";
echo "  - Child categories: {$childCategories}\n\n";

// Check first few products
echo "Sample products:\n";
$sampleProducts = App\Models\Product::take(5)->get();
foreach ($sampleProducts as $p) {
    echo "  - {$p->name} (SKU: {$p->sku})\n";
}

// Check why products might not be importing
echo "\nChecking product CSV first row...\n";
$handle = fopen('product.csv', 'r');
$header = fgetcsv($handle);
$firstRow = fgetcsv($handle);
fclose($handle);

echo "CSV Columns: " . implode(', ', array_slice($header, 0, 10)) . "...\n";
echo "First product SKU: " . ($firstRow[array_search('sku', $header)] ?? 'Not found') . "\n";
echo "First product Name: " . ($firstRow[array_search('name', $header)] ?? 'Not found') . "\n";

// Check if SKU column exists
$skuColumnExists = in_array('sku', $header);
$nameColumnExists = in_array('name', $header);

echo "\nColumn check:\n";
echo "  SKU column exists: " . ($skuColumnExists ? 'YES' : 'NO') . "\n";
echo "  Name column exists: " . ($nameColumnExists ? 'YES' : 'NO') . "\n";

if (!$skuColumnExists || !$nameColumnExists) {
    echo "\n⚠️ ISSUE: Required columns missing!\n";
    echo "Available columns in CSV:\n";
    foreach (array_slice($header, 0, 20) as $col) {
        echo "  - {$col}\n";
    }
}
?>
