<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== CHECKING DATABASE STRUCTURE ===\n\n";

// Check products table columns
if (Schema::hasTable('products')) {
    $columns = Schema::getColumnListing('products');
    echo "Products table columns:\n";
    foreach ($columns as $col) {
        echo "  - {$col}\n";
    }
} else {
    echo "❌ Products table doesn't exist!\n";
}

echo "\n\nTesting product creation:\n";

try {
    $testProduct = new App\Models\Product();
    $testProduct->name = 'Test Product';
    $testProduct->slug = 'test-product';
    $testProduct->sku = 'TEST-SKU-' . time();
    $testProduct->description = 'Test description';
    $testProduct->unit_price = 100;
    $testProduct->purchase_price = 100;
    $testProduct->current_stock = 10;
    $testProduct->category_id = 1;
    $testProduct->published = 1;
    $testProduct->featured = 0;
    $testProduct->digital = 0;
    $testProduct->tax = 0;
    $testProduct->tax_type = 'percent';
    $testProduct->discount = 0;
    $testProduct->discount_type = 'amount';
    $testProduct->shipping_type = 'flat_rate';
    $testProduct->shipping_cost = 0;
    $testProduct->min_qty = 1;
    $testProduct->refundable = 1;
    $testProduct->user_id = 1;
    $testProduct->added_by = 'admin';
    $testProduct->meta_title = 'Test Product';
    $testProduct->meta_description = 'Test description';
    $testProduct->barcode = $testProduct->sku;
    $testProduct->cash_on_delivery = 1;
    $testProduct->est_shipping_days = 7;
    $testProduct->num_of_sale = 0;
    $testProduct->rating = 0;
    $testProduct->weight = 0;
    $testProduct->tags = '';
    
    // Try to save
    $testProduct->save();
    
    echo "✅ Test product created successfully! ID: {$testProduct->id}\n";
    
    // Delete test product
    $testProduct->delete();
    echo "✅ Test product deleted\n";
    
} catch (\Exception $e) {
    echo "❌ Error creating product: " . $e->getMessage() . "\n";
    echo "\nFull error:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n✅ Check complete!\n";
?>
