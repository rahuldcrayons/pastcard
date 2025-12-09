<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Product;

echo "=== FIX DUPLICATE PRODUCT IMAGES ===\n\n";

// Find slugs that have more than one product
$duplicateSlugs = Product::select('slug', DB::raw('COUNT(*) as cnt'))
    ->whereNotNull('slug')
    ->groupBy('slug')
    ->havingRaw('COUNT(*) > 1')
    ->orderBy('cnt', 'desc')
    ->get();

if ($duplicateSlugs->isEmpty()) {
    echo "No duplicate slugs found. Nothing to do.\n";
    exit(0);
}

echo "Found " . $duplicateSlugs->count() . " slug(s) with duplicates.\n\n";

$totalUpdated = 0;

foreach ($duplicateSlugs as $row) {
    $slug = $row->slug;
    $products = Product::where('slug', $slug)->orderBy('id')->get();

    // Choose a source product that already has images
    $source = $products->first(function ($p) {
        return !empty($p->thumbnail_img) || !empty($p->photos);
    });

    if (!$source) {
        // None of the duplicates have images; nothing to propagate
        continue;
    }

    echo "Slug: {$slug} ({$products->count()} products)\n";
    echo "  Source ID with images: {$source->id} (thumbnail_img={$source->thumbnail_img}, photos={$source->photos})\n";

    foreach ($products as $product) {
        if ($product->id === $source->id) {
            continue; // skip the source itself
        }

        $beforeThumb = $product->thumbnail_img;
        $beforePhotos = $product->photos;

        // Only fill in when both are empty to avoid overwriting any existing images
        if (empty($product->thumbnail_img) && empty($product->photos)) {
            $product->thumbnail_img = $source->thumbnail_img;
            $product->photos = $source->photos;
            $product->save();
            $totalUpdated++;

            echo "    Updated product ID {$product->id}: thumbnail_img {$beforeThumb} -> {$product->thumbnail_img}, photos {$beforePhotos} -> {$product->photos}\n";
        }
    }

    echo "\n";
}

echo "Done. Total products updated with images: {$totalUpdated}\n";
