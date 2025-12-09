<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== CONTINUE WORDPRESS PRODUCT MIGRATION ===\n\n";

// Configuration
$csvFile = 'product.csv';
$batchSize = 100;
$skipProducts = 1000; // Skip first 1000 already imported
$maxNewProducts = 5000; // Import next 5000 products
$processed = 0;
$imported = 0;
$errors = 0;
$skipped = 0;

echo "CSV File: {$csvFile}\n";
echo "Skip first: {$skipProducts} products\n"; 
echo "Import next: {$maxNewProducts} products\n";
echo "Batch size: {$batchSize}\n\n";

// Get existing info
echo "Current database status:\n";
echo "Products: " . App\Models\Product::count() . "\n";
echo "Categories: " . App\Models\Category::count() . "\n\n";

// Prepare category mapping
$categories = App\Models\Category::pluck('id', 'name')->toArray();
$defaultCategoryId = 178; // RARE ITEMS

// Get admin user
$adminUser = App\Models\User::where('user_type', 'admin')->first();
echo "Admin user ID: {$adminUser->id}\n\n";

// Check for existing products to avoid duplicates
$existingIds = App\Models\Product::pluck('id')->toArray();
$maxExistingId = !empty($existingIds) ? max($existingIds) : 0;
echo "Highest existing product ID: {$maxExistingId}\n\n";

echo "Starting migration...\n";

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    // Read and clean header
    $headerLine = fgets($handle);
    $headerLine = preg_replace('/^\xEF\xBB\xBF/', '', $headerLine);
    $header = str_getcsv($headerLine, ",");
    
    echo "CSV fields: " . count($header) . "\n";
    
    $batch = [];
    $lineNumber = 1;
    
    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
        $lineNumber++;
        
        if (count($data) !== count($header)) continue;
        
        $wp = array_combine($header, $data);
        
        // Skip invalid or unpublished products
        if (empty($wp['post_title']) || empty($wp['ID']) || $wp['post_status'] !== 'publish') {
            continue;
        }
        
        $productId = intval($wp['ID']);
        
        // Skip if already exists or if we haven't reached skip threshold
        if (in_array($productId, $existingIds) || $processed < $skipProducts) {
            if ($processed < $skipProducts) {
                $skipped++;
            }
            $processed++;
            continue;
        }
        
        // Stop if we've reached our limit
        if ($imported >= $maxNewProducts) {
            break;
        }
        
        $processed++;
        
        try {
            // Determine category
            $categoryId = determineCategoryId($wp, $categories, $defaultCategoryId);
            
            // Process images
            $imageUrls = extractImageUrls($wp['images'] ?? '');

            // Map WordPress to Laravel
            $productData = [
                'id' => $productId,
                'name' => trim($wp['post_title']),
                'slug' => makeUniqueSlug($wp),
                'added_by' => 'admin',
                'user_id' => $adminUser->id,
                'category_id' => $categoryId,
                'brand_id' => null,
                'description' => cleanHtml($wp['post_content'] ?? ''),
                'unit_price' => parsePrice($wp['regular_price'] ?? 0),
                'purchase_price' => parsePrice($wp['regular_price'] ?? 0) * 0.8,
                'unit' => 'pc',
                'current_stock' => parseInt($wp['stock'] ?? 1),
                'published' => 1,
                'approved' => 1,
                'featured' => 0,
                'digital' => (($wp['downloadable'] ?? '') === 'yes') ? 1 : 0,
                'shipping_cost' => parsePrice($wp['weight'] ?? 0) * 10,
                'meta_title' => substr($wp['post_title'], 0, 255),
                'meta_description' => substr(strip_tags($wp['post_excerpt'] ?? ''), 0, 500),
                'sku' => !empty($wp['sku']) ? $wp['sku'] : 'WP-' . $wp['ID'],
                'discount' => 0,
                'discount_type' => 'flat',
                'photos' => !empty($imageUrls) ? json_encode($imageUrls) : null,
                'thumbnail_img' => !empty($imageUrls) ? $imageUrls[0] : null,
                'created_at' => parseDate($wp['post_date'] ?? ''),
                'updated_at' => now(),
            ];

            // Handle discounts
            if (!empty($wp['sale_price']) && !empty($wp['regular_price'])) {
                $regular = parsePrice($wp['regular_price']);
                $sale = parsePrice($wp['sale_price']);
                if ($sale > 0 && $sale < $regular) {
                    $productData['discount'] = $regular - $sale;
                }
            }

            $batch[] = $productData;
            
            if (count($batch) >= $batchSize) {
                $batchImported = processBatch($batch);
                $imported += $batchImported;
                echo "✓ Processed line {$lineNumber}, imported {$imported} new products\n";
                $batch = [];
            }
            
        } catch (Exception $e) {
            $errors++;
            if ($errors < 20) {
                echo "✗ Error on line {$lineNumber}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Process final batch
    if (!empty($batch)) {
        $imported += processBatch($batch);
    }
    
    fclose($handle);
}

// Final summary
echo "\n=== MIGRATION COMPLETE ===\n";
echo "CSV lines processed: {$lineNumber}\n";
echo "Products skipped (first 1000): {$skipped}\n";
echo "Total processed: {$processed}\n";
echo "New products imported: {$imported}\n";
echo "Errors: {$errors}\n";
echo "Total products in database: " . App\Models\Product::count() . "\n";
echo "Total product stocks: " . App\Models\ProductStock::count() . "\n";

// Helper functions
function processBatch($batch) {
    $success = 0;
    foreach ($batch as $data) {
        try {
            $product = App\Models\Product::create($data);
            
            App\Models\ProductStock::create([
                'product_id' => $product->id,
                'variant' => '',
                'sku' => $product->sku,
                'price' => $product->unit_price,
                'qty' => max(1, $product->current_stock),
            ]);
            
            $success++;
        } catch (Exception $e) {
            // Handle duplicate ID errors gracefully
        }
    }
    return $success;
}

function determineCategoryId($wp, $categories, $default) {
    $title = strtolower($wp['post_title']);
    $content = strtolower($wp['post_content'] ?? '');
    
    // Enhanced category mapping
    $mapping = [
        'ANTIQUE COMICS' => ['comic', 'manoj', 'diamond', 'raj comics', 'amar chitra', 'indrajal', 'tinkle'],
        'ANTIQUE MAGAZINES' => ['magazine', 'nandan', 'champak', 'balhans', 'chanda mama', 'nanhe samrat'],
        'PHILATELY' => ['stamp', 'postage', 'postal', 'philately'],
        'NOVELS' => ['novel', 'book', 'story', 'kitab'],
        'AUDIO CASSETTES' => ['cassette', 'tape', 'audio', 'music', 'record'],
        'CD/DVD' => ['cd', 'dvd', 'disc'],
        'OLD TOYS' => ['toy', 'doll', 'game'],
        'OLD DIARIES' => ['diary', 'calendar'],
        'ARTWORKS' => ['painting', 'art', 'canvas'],
        'RARE ITEMS' => ['rare', 'antique', 'vintage', 'collectible', 'old'],
    ];
    
    foreach ($mapping as $categoryName => $keywords) {
        if (isset($categories[$categoryName])) {
            foreach ($keywords as $keyword) {
                if (strpos($title, $keyword) !== false || strpos($content, $keyword) !== false) {
                    return $categories[$categoryName];
                }
            }
        }
    }
    
    return $default;
}

function makeUniqueSlug($wp) {
    $slug = !empty($wp['post_name']) ? $wp['post_name'] : \Str::slug($wp['post_title']);
    return substr($slug, 0, 100) . '-' . $wp['ID'];
}

function extractImageUrls($imageString) {
    $urls = [];
    if (preg_match_all('/https?:\/\/[^\s!]+\.(jpg|jpeg|png|gif|webp)/i', $imageString, $matches)) {
        return array_slice($matches[0], 0, 5);
    }
    return $urls;
}

function cleanHtml($content) {
    return substr(strip_tags(str_replace(['&nbsp;', '<br>', '<br/>'], [' ', ' ', ' '], $content)), 0, 5000);
}

function parsePrice($price) {
    return floatval(preg_replace('/[^0-9.]/', '', $price));
}

function parseInt($value) {
    return intval(preg_replace('/[^0-9]/', '', $value)) ?: 1;
}

function parseDate($date) {
    try {
        return !empty($date) ? \Carbon\Carbon::parse($date) : now();
    } catch (Exception $e) {
        return now();
    }
}
?>
