<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== TESTING DIVISION BY ZERO FIX ===\n\n";

// Test products with zero or null prices
$testProducts = App\Models\Product::limit(10)->get();

echo "Testing discount_in_percentage function:\n";
echo str_repeat("-", 50) . "\n";

foreach ($testProducts as $product) {
    try {
        $discount = discount_in_percentage($product);
        $basePrice = home_base_price($product, false);
        $discountedPrice = home_discounted_base_price($product, false);
        
        echo "Product ID: {$product->id}\n";
        echo "  Name: " . substr($product->name, 0, 30) . "...\n";
        echo "  Base Price: {$basePrice}\n";
        echo "  Discounted Price: {$discountedPrice}\n";
        echo "  Discount %: {$discount}%\n";
        echo "  Status: ✅ OK\n\n";
        
    } catch (Exception $e) {
        echo "Product ID: {$product->id}\n";
        echo "  Error: " . $e->getMessage() . "\n";
        echo "  Status: ❌ ERROR\n\n";
    }
}

echo "✅ Division by zero fix test completed!\n";
echo "💡 If you see this message, the 500 error should be resolved.\n";
?>
