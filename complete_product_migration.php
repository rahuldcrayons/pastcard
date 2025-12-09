<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== COMPLETE WORDPRESS TO LARAVEL PRODUCT MIGRATION ===\n\n";

// Configuration
$csvFile = 'exported.csv';
$batchSize = 50;
$maxProducts = 1000; // Set to -1 for all products
$processed = 0;
$imported = 0;
$errors = 0;

// Step 1: Clear existing dummy products
echo "Step 1: Clearing existing dummy products...\n";
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::table('product_stocks')->truncate();
DB::table('product_taxes')->truncate();  
DB::table('product_translations')->truncate();
DB::table('products')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');
echo "✓ Cleared all existing products and related data.\n\n";

// Step 2: Get category mapping
echo "Step 2: Preparing category mapping...\n";
$categories = App\Models\Category::pluck('id', 'name')->toArray();
$defaultCategoryId = 178; // RARE ITEMS
echo "Available categories: " . count($categories) . "\n";
echo "Default category ID: {$defaultCategoryId}\n\n";

// Step 3: Ensure admin user
echo "Step 3: Ensuring admin user exists...\n";
$adminUser = App\Models\User::where('user_type', 'admin')->first();
if (!$adminUser) {
    $adminUser = App\Models\User::create([
        'name' => 'Admin',
        'email' => 'admin@pastcart.com', 
        'password' => md5('password123'),
        'user_type' => 'admin',
        'email_verified_at' => now(),
    ]);
}
echo "✓ Admin user ready (ID: {$adminUser->id})\n\n";

// Step 4: Start migration
echo "Step 4: Starting product migration...\n";

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    // Read and clean header
    $headerLine = fgets($handle);
    $headerLine = preg_replace('/^\xEF\xBB\xBF/', '', $headerLine);
    $header = str_getcsv($headerLine, ",");
    
    echo "CSV fields: " . count($header) . "\n";
    echo "Processing products...\n\n";
    
    $batch = [];
    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
        if ($maxProducts > 0 && $processed >= $maxProducts) break;
        
        $processed++;
        
        try {
            if (count($data) !== count($header)) continue;
            
            $wp = array_combine($header, $data);
            
            // Skip invalid products
            if (empty($wp['post_title']) || empty($wp['ID']) || 
                $wp['post_status'] !== 'publish') {
                continue;
            }

            // Determine category by product content/title
            $categoryId = determineCategoryId($wp, $categories, $defaultCategoryId);
            
            // Process images
            $imageUrls = [];
            if (!empty($wp['images'])) {
                $imageUrls = extractImageUrls($wp['images']);
            }

            // Map WordPress to Laravel
            $productData = [
                'id' => intval($wp['ID']),
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
                echo "✓ Processed {$processed}, imported {$imported}\n";
                $batch = [];
            }
            
        } catch (Exception $e) {
            $errors++;
            if ($errors < 10) {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Final batch
    if (!empty($batch)) {
        $imported += processBatch($batch);
    }
    
    fclose($handle);
}

// Final summary
echo "\n=== MIGRATION COMPLETE ===\n";
echo "Total processed: {$processed}\n";
echo "Successfully imported: {$imported}\n";
echo "Errors: {$errors}\n";
echo "Products in database: " . App\Models\Product::count() . "\n";
echo "Product stocks created: " . App\Models\ProductStock::count() . "\n";

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
            // Silent fail for batch processing
        }
    }
    return $success;
}

function determineCategoryId($wp, $categories, $default) {
    $title = strtolower($wp['post_title']);
    $content = strtolower($wp['post_content'] ?? '');
    
    // Category mapping based on keywords
    $mapping = [
        'ANTIQUE COMICS' => ['comic', 'manoj', 'diamond', 'raj comics', 'amar chitra'],
        'ANTIQUE MAGAZINES' => ['magazine', 'nandan', 'champak', 'balhans'],
        'PHILATELY' => ['stamp', 'postage', 'postal'],
        'NOVELS' => ['novel', 'book', 'story'],
        'RARE ITEMS' => ['rare', 'antique', 'vintage', 'collectible'],
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
    return substr($slug, 0, 100);
}

function extractImageUrls($imageString) {
    $urls = [];
    if (preg_match_all('/https?:\/\/[^\s!]+\.(jpg|jpeg|png|gif|webp)/i', $imageString, $matches)) {
        return array_slice($matches[0], 0, 5); // Max 5 images
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
