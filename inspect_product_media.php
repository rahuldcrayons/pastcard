<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Upload;

$slug = $argv[1] ?? null;
if (!$slug) {
    echo "Usage: php inspect_product_media.php <product-slug>\n";
    exit(1);
}

$product = Product::where('slug', $slug)->first();
if (!$product) {
    echo "Product not found for slug: {$slug}\n";
    exit(1);
}

echo "Product ID: {$product->id}\n";
echo "Name: {$product->name}\n";
echo "Slug: {$product->slug}\n";
echo "thumbnail_img: " . ($product->thumbnail_img ?? 'NULL') . "\n";
echo "photos raw: " . ($product->photos ?? 'NULL') . "\n";

$photoIds = [];
if (!empty($product->photos)) {
    if (is_array($product->photos)) {
        $photoIds = $product->photos;
    } else {
        $photoIds = array_filter(explode(',', $product->photos));
    }
}

if (empty($photoIds)) {
    echo "No photo IDs parsed from photos field.\n";
} else {
    echo "Parsed photo IDs: " . implode(', ', $photoIds) . "\n";
}

echo "\nUpload records:\n";
foreach ($photoIds as $id) {
    $upload = Upload::find($id);
    if (!$upload) {
        echo " - ID {$id}: MISSING in uploads table\n";
        continue;
    }
    echo " - ID {$id}: file_name=" . ($upload->file_name ?? 'NULL') . ", external_link=" . ($upload->external_link ?? 'NULL') . "\n";
}

if ($product->thumbnail_img) {
    $thumb = Upload::find($product->thumbnail_img);
    echo "\nThumbnail Upload record (thumbnail_img):\n";
    if ($thumb) {
        echo " - ID {$thumb->id}: file_name=" . ($thumb->file_name ?? 'NULL') . ", external_link=" . ($thumb->external_link ?? 'NULL') . "\n";
    } else {
        echo " - Upload ID {$product->thumbnail_img} not found in uploads table.\n";
    }
}
