<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\Product;

$id = $argv[1] ?? null;
if (!$id) {
    echo "Usage: php preview_product_box.php <product-id>\n";
    exit(1);
}

$product = Product::find($id);
if (!$product) {
    echo "Product not found for ID: {$id}\n";
    exit(1);
}

echo "=== product_box_2 for product ID {$product->id} ({$product->slug}) ===\n\n";

// Render the Blade partial exactly as in the app
echo view('frontend.partials.product_box_2', ['product' => $product])->render();
