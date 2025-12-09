<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\Product;

$slug = $argv[1] ?? null;
if (!$slug) {
    echo "Usage: php list_products_by_slug.php <product-slug>\n";
    exit(1);
}

$products = Product::where('slug', $slug)->orderBy('id')->get();

if ($products->isEmpty()) {
    echo "No products found for slug: {$slug}\n";
    exit(0);
}

echo "Found " . $products->count() . " product(s) with slug '{$slug}':\n\n";

foreach ($products as $p) {
    echo "ID: {$p->id}\n";
    echo "  name: {$p->name}\n";
    echo "  slug: {$p->slug}\n";
    echo "  published: {$p->published}\n";
    echo "  thumbnail_img: " . ($p->thumbnail_img ?? 'NULL') . "\n";
    echo "  photos: " . ($p->photos ?? 'NULL') . "\n";
    echo "-----------------------------\n";
}
