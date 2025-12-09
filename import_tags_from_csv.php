<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== IMPORTING TAGS FROM CSV ===\n\n";

$csvFile = 'product.csv';
$handle = fopen($csvFile, 'r');

if (!$handle) {
    die("Could not open CSV file\n");
}

// Read and clean header
$headerLine = fgets($handle);
$headerLine = preg_replace('/^\xEF\xBB\xBF/', '', $headerLine);
$header = str_getcsv($headerLine, ",");

// Find required column indexes
$idColumnIndex = array_search('ID', $header);
$tagColumnIndex = array_search('tax:product_tag', $header);

if ($idColumnIndex === false || $tagColumnIndex === false) {
    die("Required columns not found\n");
}

echo "Found columns - ID: {$idColumnIndex}, Tags: {$tagColumnIndex}\n\n";

$processedProducts = 0;
$updatedProducts = 0;
$totalTags = 0;
$uniqueTags = [];

echo "Processing products and importing tags...\n";

while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
    if (count($row) <= max($idColumnIndex, $tagColumnIndex)) {
        continue;
    }
    
    $productId = trim($row[$idColumnIndex]);
    $tagsData = trim($row[$tagColumnIndex]);
    
    if (empty($productId)) {
        continue;
    }
    
    $processedProducts++;
    
    // Find the product in database
    $product = App\Models\Product::find($productId);
    
    if (!$product) {
        continue;
    }
    
    // Process tags data
    if (!empty($tagsData)) {
        // Clean and format tags
        $tags = $tagsData;
        
        // Sometimes tags might be separated by commas or other delimiters
        // Let's standardize them
        $tags = str_replace(['|', ';'], ',', $tags);
        $tags = preg_replace('/\s*,\s*/', ', ', $tags); // Clean spacing around commas
        $tags = trim($tags);
        
        if (!empty($tags)) {
            $product->tags = $tags;
            $product->save();
            $updatedProducts++;
            
            // Count unique tags for statistics
            $tagArray = array_map('trim', explode(',', $tags));
            foreach ($tagArray as $tag) {
                if (!empty($tag)) {
                    $uniqueTags[strtolower($tag)] = true;
                    $totalTags++;
                }
            }
        }
    }
    
    if ($processedProducts % 1000 == 0) {
        echo "Processed {$processedProducts} products, updated {$updatedProducts}...\n";
    }
}

fclose($handle);

echo "\n=== IMPORT SUMMARY ===\n";
echo "Products processed: {$processedProducts}\n";
echo "Products updated with tags: {$updatedProducts}\n";
echo "Total tag assignments: {$totalTags}\n";
echo "Unique tags: " . count($uniqueTags) . "\n";

// Show most common tags
echo "\n=== SAMPLE TAGS ===\n";
$sampleProducts = App\Models\Product::whereNotNull('tags')->where('tags', '!=', '')->take(10)->get(['id', 'name', 'tags']);
foreach ($sampleProducts as $product) {
    $shortName = substr($product->name, 0, 40) . (strlen($product->name) > 40 ? '...' : '');
    echo "• {$shortName}: {$product->tags}\n";
}

// Show some popular tags
echo "\n=== POPULAR TAGS ===\n";
$tagCounts = [];
foreach ($uniqueTags as $tag => $dummy) {
    $count = App\Models\Product::where('tags', 'LIKE', "%{$tag}%")->count();
    if ($count > 5) { // Only show tags used more than 5 times
        $tagCounts[$tag] = $count;
    }
}

arsort($tagCounts);
$topTags = array_slice($tagCounts, 0, 15, true);

foreach ($topTags as $tag => $count) {
    echo "• {$tag}: {$count} products\n";
}

echo "\n=== VERIFICATION ===\n";
$productsWithTags = App\Models\Product::whereNotNull('tags')->where('tags', '!=', '')->count();
echo "Products now having tags: {$productsWithTags}\n";

echo "\n✅ Tags import completed successfully!\n";
echo "Products can now be searched by tags in addition to names and descriptions.\n";
?>
