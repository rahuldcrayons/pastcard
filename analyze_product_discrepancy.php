<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== PRODUCT DISCREPANCY ANALYSIS ===\n\n";

$currentTotal = App\Models\Product::count();
$expectedTotal = 76092;
$difference = $currentTotal - $expectedTotal;

echo "Expected: {$expectedTotal}\n";
echo "Current: {$currentTotal}\n";
echo "Difference: {$difference} extra products\n\n";

// Check if we can identify where the extra products came from
echo "=== MIGRATION ANALYSIS ===\n";
echo "Round 1: 1,000 products\n";
echo "Round 2: 5,000 products\n";  
echo "Round 3: 10,040 products\n";
echo "Round 4: 10,000 products\n";
echo "Round 5: 10,000 products\n";
echo "Round 6: 10,000 products\n";
echo "Round 7: 10,000 products\n";
echo "Round 8: 10,000 products\n";
echo "Round 9: 10,195 products\n";
echo "Round 10: 224 products\n";
echo "Expected total: " . (1000 + 5000 + 10040 + 10000 + 10000 + 10000 + 10000 + 10000 + 10195 + 224) . "\n\n";

// Check product creation pattern
echo "=== PRODUCT ID ANALYSIS ===\n";
$minId = App\Models\Product::min('id');
$maxId = App\Models\Product::max('id');
echo "ID Range: {$minId} to {$maxId}\n";

// Check for gaps in IDs that might indicate duplicates or skipped IDs
$idCount = App\Models\Product::count();
$expectedRange = $maxId - $minId + 1;
echo "Expected IDs in range: {$expectedRange}\n";
echo "Actual products: {$idCount}\n";
echo "Missing IDs: " . ($expectedRange - $idCount) . "\n\n";

// Check highest 10 product IDs
echo "=== HIGHEST PRODUCT IDS ===\n";
$highestProducts = App\Models\Product::select('id', 'name')->orderBy('id', 'desc')->take(10)->get();
foreach($highestProducts as $product) {
    echo "ID: {$product->id}, Name: " . substr($product->name, 0, 50) . "\n";
}

echo "\n=== SOLUTION OPTIONS ===\n";
if ($difference > 0) {
    echo "1. Keep all {$currentTotal} products (recommended if all are valid)\n";
    echo "2. Remove {$difference} highest ID products\n";
    echo "3. Identify and remove duplicate products by name/content\n";
    echo "4. Keep current state - the difference might be due to updated WordPress data\n";
}
?>
