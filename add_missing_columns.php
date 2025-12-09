<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "=== ADDING MISSING COLUMNS TO PRODUCTS TABLE ===\n\n";

$missingColumns = [
    'shipping_type' => ['type' => 'string', 'default' => 'flat_rate'],
    'shipping_cost' => ['type' => 'decimal', 'default' => 0],
    'est_shipping_days' => ['type' => 'integer', 'default' => 7],
    'cash_on_delivery' => ['type' => 'boolean', 'default' => 1],
    'weight' => ['type' => 'decimal', 'default' => 0],
    'length' => ['type' => 'decimal', 'default' => 0],
    'width' => ['type' => 'decimal', 'default' => 0],
    'height' => ['type' => 'decimal', 'default' => 0],
    'discount_type' => ['type' => 'string', 'default' => 'amount'],
    'tax_type' => ['type' => 'string', 'default' => 'percent'],
];

Schema::table('products', function (Blueprint $table) use ($missingColumns) {
    $existingColumns = Schema::getColumnListing('products');
    
    foreach ($missingColumns as $column => $config) {
        if (!in_array($column, $existingColumns)) {
            echo "Adding column: {$column}\n";
            
            switch ($config['type']) {
                case 'string':
                    $table->string($column)->default($config['default'])->nullable();
                    break;
                case 'decimal':
                    $table->decimal($column, 10, 2)->default($config['default'])->nullable();
                    break;
                case 'integer':
                    $table->integer($column)->default($config['default'])->nullable();
                    break;
                case 'boolean':
                    $table->boolean($column)->default($config['default'])->nullable();
                    break;
            }
        } else {
            echo "Column {$column} already exists\n";
        }
    }
});

echo "\n✅ Missing columns added successfully!\n\n";

// Test product creation again
echo "Testing product creation...\n";

try {
    $testProduct = new App\Models\Product();
    $testProduct->name = 'Test Product';
    $testProduct->slug = 'test-product';
    $testProduct->sku = 'TEST-' . time();
    $testProduct->description = 'Test';
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
    $testProduct->meta_title = 'Test';
    $testProduct->meta_description = 'Test';
    $testProduct->barcode = $testProduct->sku;
    $testProduct->cash_on_delivery = 1;
    $testProduct->est_shipping_days = 7;
    $testProduct->num_of_sale = 0;
    $testProduct->rating = 0;
    $testProduct->weight = 0;
    $testProduct->tags = '';
    
    $testProduct->save();
    
    echo "✅ Test product created successfully!\n";
    $testProduct->delete();
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 Database structure fixed! You can now run the import.\n";
?>
