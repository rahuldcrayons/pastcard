<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== MASS WORDPRESS PRODUCT MIGRATION ===\n\n";

// Configuration - Adjust these as needed
$csvFile = 'product.csv';
$batchSize = 200;
$productsPerRun = 10000; // Import 10K products per run
$startFromLine = null; // Auto-detect from database

// Get current status
$currentProductCount = App\Models\Product::count();
$existingIds = App\Models\Product::pluck('id')->toArray();
$maxExistingId = !empty($existingIds) ? max($existingIds) : 0;

echo "Current database status:\n";
echo "Products in database: {$currentProductCount}\n";
echo "Highest product ID: {$maxExistingId}\n";
echo "Target: Import {$productsPerRun} more products\n\n";

// Setup
$categories = App\Models\Category::pluck('id', 'name')->toArray();
$adminUser = App\Models\User::where('user_type', 'admin')->first();
$defaultCategoryId = 178;

$processed = 0;
$imported = 0;
$errors = 0;
$skipped = 0;

echo "Starting mass migration...\n";

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    // Skip header
    $header = str_getcsv(preg_replace('/^\xEF\xBB\xBF/', '', fgets($handle)), ",");
    
    $batch = [];
    $lineNumber = 1;
    
    while (($data = fgetcsv($handle, 15000, ",")) !== FALSE) {
        $lineNumber++;
        
        if ($imported >= $productsPerRun) break;
        if (count($data) !== count($header)) continue;
        
        $wp = array_combine($header, $data);
        
        // Skip invalid products
        if (empty($wp['post_title']) || empty($wp['ID']) || $wp['post_status'] !== 'publish') {
            continue;
        }
        
        $productId = intval($wp['ID']);
        
        // Skip if already exists
        if (in_array($productId, $existingIds)) {
            $skipped++;
            continue;
        }
        
        $processed++;
        
        try {
            // Enhanced category detection
            $categoryId = smartCategoryDetection($wp, $categories, $defaultCategoryId);
            
            // Enhanced image processing
            $images = enhancedImageExtraction($wp);

            $productData = [
                'id' => $productId,
                'name' => trim(substr($wp['post_title'], 0, 255)),
                'slug' => generateUniqueSlug($wp, $productId),
                'added_by' => 'admin',
                'user_id' => $adminUser->id,
                'category_id' => $categoryId,
                'brand_id' => null,
                'description' => processDescription($wp['post_content'] ?? ''),
                'unit_price' => smartPriceParser($wp['regular_price'] ?? 0),
                'purchase_price' => smartPriceParser($wp['regular_price'] ?? 0) * 0.75,
                'unit' => 'pc',
                'current_stock' => parseStock($wp['stock'] ?? 1),
                'published' => 1,
                'approved' => 1,
                'featured' => rand(0, 10) === 0 ? 1 : 0, // 10% featured
                'digital' => (($wp['downloadable'] ?? '') === 'yes') ? 1 : 0,
                'shipping_cost' => calculateShipping($wp),
                'meta_title' => substr($wp['post_title'], 0, 255),
                'meta_description' => generateMetaDescription($wp),
                'sku' => generateSKU($wp),
                'discount' => 0,
                'discount_type' => 'flat',
                'photos' => !empty($images) ? json_encode($images) : null,
                'thumbnail_img' => !empty($images) ? $images[0] : null,
                'tags' => extractTags($wp),
                'created_at' => smartDateParser($wp['post_date'] ?? ''),
                'updated_at' => now(),
            ];

            // Smart discount calculation
            $discount = calculateDiscount($wp);
            if ($discount > 0) {
                $productData['discount'] = $discount;
            }

            $batch[] = $productData;
            
            if (count($batch) >= $batchSize) {
                $batchImported = processBatchAdvanced($batch);
                $imported += $batchImported;
                
                if ($imported % 1000 === 0) {
                    echo "🚀 Milestone: {$imported} products imported (Line: {$lineNumber})\n";
                } else {
                    echo "✓ Imported {$imported} products (Line: {$lineNumber})\n";
                }
                
                $batch = [];
                
                // Memory management
                if ($imported % 5000 === 0) {
                    gc_collect_cycles();
                }
            }
            
        } catch (Exception $e) {
            $errors++;
            if ($errors <= 50) {
                echo "✗ Error line {$lineNumber}: " . substr($e->getMessage(), 0, 100) . "\n";
            }
        }
    }
    
    // Final batch
    if (!empty($batch)) {
        $imported += processBatchAdvanced($batch);
    }
    
    fclose($handle);
}

// Final summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 MASS MIGRATION COMPLETE! 🎉\n";
echo str_repeat("=", 50) . "\n";
echo "📊 STATISTICS:\n";
echo "CSV Lines processed: " . number_format($lineNumber) . "\n";
echo "Products processed: " . number_format($processed) . "\n";
echo "Products skipped (duplicates): " . number_format($skipped) . "\n";
echo "New products imported: " . number_format($imported) . "\n";
echo "Errors encountered: " . number_format($errors) . "\n";
echo "Success rate: " . round(($imported / max(1, $processed)) * 100, 2) . "%\n\n";

echo "📈 DATABASE TOTALS:\n";
echo "Total products: " . number_format(App\Models\Product::count()) . "\n";
echo "Total stocks: " . number_format(App\Models\ProductStock::count()) . "\n";
echo "Memory used: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n";

// Enhanced helper functions
function processBatchAdvanced($batch) {
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
            // Silent fail for duplicates
        }
    }
    return $success;
}

function smartCategoryDetection($wp, $categories, $default) {
    $text = strtolower($wp['post_title'] . ' ' . ($wp['post_content'] ?? ''));
    
    $mappings = [
        'ANTIQUE COMICS' => ['comic', 'manoj', 'diamond', 'raj comics', 'amar chitra', 'indrajal', 'tinkle', 'chacha chaudhary'],
        'ANTIQUE MAGAZINES' => ['magazine', 'nandan', 'champak', 'balhans', 'chanda mama', 'nanhe samrat', 'suman saurabh'],
        'PHILATELY' => ['stamp', 'postage', 'postal', 'philately', 'envelope', 'gandhi stamp'],
        'NOVELS' => ['novel', 'book', 'story', 'kitab', 'pustak', 'granth'],
        'AUDIO CASSETTES' => ['cassette', 'tape', 'audio', 'music', 'record', 'vinyl'],
        'CD/DVD' => ['cd', 'dvd', 'disc', 'vcd'],
        'OLD TOYS' => ['toy', 'doll', 'game', 'khilona'],
        'OLD DIARIES' => ['diary', 'calendar', 'agenda'],
        'ARTWORKS' => ['painting', 'art', 'canvas', 'sketch', 'portrait'],
        'ANTIQUE PAINTINGS' => ['painting', 'canvas', 'art work', 'sketch'],
    ];
    
    foreach ($mappings as $categoryName => $keywords) {
        if (isset($categories[$categoryName])) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    return $categories[$categoryName];
                }
            }
        }
    }
    return $default;
}

function enhancedImageExtraction($wp) {
    $imageString = $wp['images'] ?? '';
    $urls = [];
    
    if (preg_match_all('/https?:\/\/[^\s!]+\.(jpg|jpeg|png|gif|webp|bmp)/i', $imageString, $matches)) {
        $urls = array_unique($matches[0]);
        return array_slice($urls, 0, 8);
    }
    return $urls;
}

function generateUniqueSlug($wp, $id) {
    $slug = !empty($wp['post_name']) ? $wp['post_name'] : \Str::slug($wp['post_title']);
    return substr($slug, 0, 80) . '-' . $id;
}

function processDescription($content) {
    $clean = strip_tags(str_replace(['&nbsp;', '<br>', '<br/>', '\n', '\r'], [' ', ' ', ' ', ' ', ' '], $content));
    return substr(trim($clean), 0, 8000);
}

function smartPriceParser($price) {
    $cleaned = preg_replace('/[^0-9.]/', '', $price);
    return floatval($cleaned) ?: 0;
}

function parseStock($stock) {
    return max(1, intval(preg_replace('/[^0-9]/', '', $stock)));
}

function calculateShipping($wp) {
    $weight = smartPriceParser($wp['weight'] ?? 0);
    return $weight > 0 ? min(500, $weight * 15) : 50;
}

function generateMetaDescription($wp) {
    $excerpt = strip_tags($wp['post_excerpt'] ?? '');
    if (empty($excerpt)) {
        $excerpt = strip_tags(substr($wp['post_content'] ?? '', 0, 300));
    }
    return substr(trim($excerpt), 0, 500);
}

function generateSKU($wp) {
    return !empty($wp['sku']) ? $wp['sku'] : 'PC-' . $wp['ID'];
}

function extractTags($wp) {
    $tags = [];
    $text = strtolower($wp['post_title']);
    
    $commonTags = ['antique', 'vintage', 'rare', 'collectible', 'old', 'classic', 'original'];
    foreach ($commonTags as $tag) {
        if (strpos($text, $tag) !== false) {
            $tags[] = $tag;
        }
    }
    
    return !empty($tags) ? implode(',', $tags) : null;
}

function calculateDiscount($wp) {
    if (!empty($wp['sale_price']) && !empty($wp['regular_price'])) {
        $regular = smartPriceParser($wp['regular_price']);
        $sale = smartPriceParser($wp['sale_price']);
        if ($sale > 0 && $sale < $regular) {
            return $regular - $sale;
        }
    }
    return 0;
}

function smartDateParser($date) {
    try {
        return !empty($date) ? \Carbon\Carbon::parse($date) : now()->subDays(rand(1, 365));
    } catch (Exception $e) {
        return now()->subDays(rand(1, 365));
    }
}
?>
