<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== WORDPRESS TO LARAVEL PRODUCT MIGRATION (FIXED) ===\n\n";

// Step 1: Clear existing dummy products
echo "Step 1: Clearing existing dummy products...\n";
$existingProductsCount = App\Models\Product::count();
echo "Found {$existingProductsCount} existing products to clear.\n";

// Clear related data first
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::table('product_stocks')->truncate();
DB::table('product_taxes')->truncate();  
DB::table('product_translations')->truncate();
DB::table('products')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "✓ Cleared all existing products and related data.\n\n";

// Step 2: Prepare category mapping
echo "Step 2: Preparing category mapping...\n";
$categories = App\Models\Category::pluck('name', 'id')->toArray();
echo "Available categories: " . count($categories) . "\n";

// Step 3: Create default admin user if not exists
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
    echo "✓ Created admin user.\n";
} else {
    echo "✓ Admin user exists.\n";
}

// Step 4: Start product migration
echo "\nStep 4: Starting product migration...\n";
$csvFile = 'exported.csv';
$batchSize = 50;
$processed = 0;
$imported = 0;
$errors = 0;

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    // Read and clean header (remove BOM)
    $headerLine = fgets($handle);
    $headerLine = preg_replace('/^\xEF\xBB\xBF/', '', $headerLine); // Remove UTF-8 BOM
    $header = str_getcsv($headerLine, ",");
    
    echo "CSV Header fields: " . count($header) . "\n";
    echo "First 5 fields: " . implode(", ", array_slice($header, 0, 5)) . "\n\n";
    
    $batch = [];
    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE && $processed < 200) { // Limited for testing
        $processed++;
        
        try {
            // Ensure data array matches header size
            if (count($data) !== count($header)) {
                continue;
            }

            $wpProduct = array_combine($header, $data);
            
            // Skip if essential fields are missing
            if (empty($wpProduct['post_title']) || empty($wpProduct['ID'])) {
                continue;
            }

            // Map WordPress fields to Laravel
            $productData = [
                'id' => intval($wpProduct['ID']),
                'name' => trim($wpProduct['post_title']),
                'slug' => !empty($wpProduct['post_name']) ? $wpProduct['post_name'] : \Str::slug($wpProduct['post_title']),
                'added_by' => 'admin',
                'user_id' => $adminUser->id,
                'category_id' => 178, // Default to "RARE ITEMS" category
                'brand_id' => null,
                'description' => !empty($wpProduct['post_content']) ? $wpProduct['post_content'] : '',
                'unit_price' => !empty($wpProduct['regular_price']) ? floatval($wpProduct['regular_price']) : 0,
                'purchase_price' => !empty($wpProduct['regular_price']) ? floatval($wpProduct['regular_price']) * 0.8 : 0,
                'unit' => 'pc',
                'current_stock' => !empty($wpProduct['stock']) ? intval($wpProduct['stock']) : 1,
                'published' => ($wpProduct['post_status'] === 'publish') ? 1 : 0,
                'approved' => 1,
                'featured' => 0,
                'digital' => (!empty($wpProduct['downloadable']) && $wpProduct['downloadable'] === 'yes') ? 1 : 0,
                'shipping_cost' => !empty($wpProduct['weight']) ? floatval($wpProduct['weight']) * 10 : 0,
                'meta_title' => substr($wpProduct['post_title'], 0, 255),
                'meta_description' => !empty($wpProduct['post_excerpt']) ? substr($wpProduct['post_excerpt'], 0, 500) : '',
                'sku' => !empty($wpProduct['sku']) ? $wpProduct['sku'] : 'WP-' . $wpProduct['ID'],
                'discount' => 0,
                'discount_type' => 'flat',
                'created_at' => !empty($wpProduct['post_date']) ? $wpProduct['post_date'] : now(),
                'updated_at' => now(),
            ];

            // Handle sale price as discount
            if (!empty($wpProduct['sale_price']) && !empty($wpProduct['regular_price'])) {
                $regularPrice = floatval($wpProduct['regular_price']);
                $salePrice = floatval($wpProduct['sale_price']);
                if ($salePrice < $regularPrice && $salePrice > 0) {
                    $productData['discount'] = $regularPrice - $salePrice;
                    $productData['discount_type'] = 'flat';
                }
            }

            $batch[] = $productData;
            
            // Process batch
            if (count($batch) >= $batchSize) {
                $batchImported = processBatch($batch);
                $imported += $batchImported;
                echo "✓ Processed {$processed} products, imported {$imported}...\n";
                $batch = [];
            }
            
        } catch (Exception $e) {
            $errors++;
            echo "✗ Error processing product: " . $e->getMessage() . "\n";
        }
    }
    
    // Process remaining batch
    if (!empty($batch)) {
        $batchImported = processBatch($batch);
        $imported += $batchImported;
    }
    
    fclose($handle);
}

echo "\n=== MIGRATION SUMMARY ===\n";
echo "Total processed: {$processed}\n";
echo "Successfully imported: {$imported}\n";
echo "Errors: {$errors}\n";
echo "Current products in database: " . App\Models\Product::count() . "\n";

// Process batch function
function processBatch($batch) {
    $success = 0;
    foreach ($batch as $productData) {
        try {
            // Create product
            $product = App\Models\Product::create($productData);
            
            // Create product stock
            App\Models\ProductStock::create([
                'product_id' => $product->id,
                'variant' => '',
                'sku' => $product->sku,
                'price' => $product->unit_price,
                'qty' => $product->current_stock > 0 ? $product->current_stock : 1,
            ]);
            
            $success++;
            
        } catch (Exception $e) {
            echo "✗ Error creating product ID {$productData['id']}: " . $e->getMessage() . "\n";
        }
    }
    return $success;
}
?>
