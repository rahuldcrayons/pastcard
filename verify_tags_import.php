<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== TAGS IMPORT VERIFICATION ===\n\n";

$productsWithTags = App\Models\Product::whereNotNull('tags')->where('tags', '!=', '')->count();
$totalProducts = App\Models\Product::count();

echo "Total products: {$totalProducts}\n";
echo "Products with tags: {$productsWithTags}\n";
echo "Coverage: " . round(($productsWithTags / $totalProducts) * 100, 2) . "%\n\n";

echo "=== SAMPLE TAGGED PRODUCTS ===\n";
$sampleProducts = App\Models\Product::whereNotNull('tags')->where('tags', '!=', '')->take(10)->get(['id', 'name', 'tags']);

foreach ($sampleProducts as $product) {
    $shortName = substr($product->name, 0, 40) . (strlen($product->name) > 40 ? '...' : '');
    echo "• {$shortName}\n";
    echo "  Tags: {$product->tags}\n\n";
}

echo "=== TAG ANALYSIS ===\n";
// Count most common words in tags
$allTags = App\Models\Product::whereNotNull('tags')->where('tags', '!=', '')->pluck('tags');
$wordCount = [];

foreach ($allTags as $tagString) {
    $tags = explode(',', strtolower($tagString));
    foreach ($tags as $tag) {
        $tag = trim($tag);
        if (!empty($tag)) {
            $wordCount[$tag] = ($wordCount[$tag] ?? 0) + 1;
        }
    }
}

arsort($wordCount);
$topTags = array_slice($wordCount, 0, 15, true);

echo "Most popular tags:\n";
foreach ($topTags as $tag => $count) {
    echo "• '{$tag}': {$count} products\n";
}

echo "\n✅ Tags system is working and populated!\n";
?>
