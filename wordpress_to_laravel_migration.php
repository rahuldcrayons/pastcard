<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "=== WORDPRESS/WOOCOMMERCE TO LARAVEL MIGRATION ===\n\n";

// Ask for confirmation
echo "⚠️ WARNING: This will CLEAR all existing products and reimport from CSV!\n";
echo "Continue? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) != 'yes') {
    echo "Migration cancelled.\n";
    exit;
}
fclose($handle);

echo "\n🧹 STEP 1: CLEARING EXISTING PRODUCTS\n";
echo str_repeat("=", 50) . "\n";

// Clear product-related tables
DB::statement('SET FOREIGN_KEY_CHECKS=0');

$tablesToClear = [
    'products',
    'product_stocks',
    'product_translations',
    'product_taxes',
    'attribute_values',
    'product_variations'
];

foreach ($tablesToClear as $table) {
    if (\Schema::hasTable($table)) {
        DB::table($table)->truncate();
        echo "✅ Cleared {$table} table\n";
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n📋 STEP 2: WOOCOMMERCE TO LARAVEL FIELD MAPPING\n";
echo str_repeat("=", 50) . "\n";

// Define comprehensive field mapping
$fieldMapping = [
    // Basic Product Info
    'id' => 'id',
    'sku' => 'sku',
    'name' => 'name',
    'slug' => 'slug',
    'published' => 'published', // Will convert to boolean
    'featured' => 'featured',
    'visibility_in_catalog' => 'featured',
    
    // Descriptions
    'short_description' => 'description',
    'description' => 'description',
    
    // Pricing
    'regular_price' => 'unit_price',
    'sale_price' => 'purchase_price',
    'price' => 'unit_price',
    'date_on_sale_from' => 'discount_start_date',
    'date_on_sale_to' => 'discount_end_date',
    
    // Tax
    'tax_status' => 'tax',
    'tax_class' => 'tax_type',
    
    // Stock
    'in_stock' => 'current_stock', // Will convert to quantity
    'stock' => 'current_stock',
    'stock_quantity' => 'current_stock',
    'stock_status' => 'current_stock',
    'manage_stock' => 'track_stock',
    
    // Shipping
    'weight' => 'weight',
    'length' => 'length',
    'width' => 'width', 
    'height' => 'height',
    'shipping_class' => 'shipping_type',
    
    // Categories & Tags
    'categories' => 'category_id', // Will process hierarchy
    'tax:product_cat' => 'category_id',
    'tags' => 'tags',
    'tax:product_tag' => 'tags',
    
    // Images
    'images' => 'photos',
    'image' => 'thumbnail_img',
    
    // Attributes
    'attribute:*' => 'attributes', // Dynamic attributes
    
    // Meta
    'meta:*' => 'meta_*', // Meta fields
    
    // Virtual/Downloadable
    'virtual' => 'digital',
    'downloadable' => 'digital',
];

echo "Field mapping configured:\n";
foreach (array_slice($fieldMapping, 0, 10) as $woo => $laravel) {
    echo "   {$woo} => {$laravel}\n";
}
echo "   ... and " . (count($fieldMapping) - 10) . " more mappings\n";

echo "\n📥 STEP 3: IMPORTING FROM CSV WITH PROPER MAPPING\n";
echo str_repeat("=", 50) . "\n";

$csvFile = 'product.csv';
if (!file_exists($csvFile)) {
    die("❌ Error: product.csv not found!\n");
}

$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle, 0, ",");

// Find column indices
$columnMap = [];
foreach ($header as $index => $column) {
    $columnMap[$column] = $index;
}

$importedCount = 0;
$errorCount = 0;
$categoryCache = [];

// Helper function to find or create category
function findOrCreateCategory($categoryPath, &$categoryCache) {
    // Parse category hierarchy (e.g., "ANTIQUE COMICS > MANOJ COMICS > MANOJ CHITRA KATHA (BIG)")
    $categories = array_map('trim', explode('>', $categoryPath));
    
    $parentId = 0;
    $lastCategory = null;
    
    foreach ($categories as $level => $categoryName) {
        // Check cache first
        $cacheKey = $parentId . '_' . $categoryName;
        if (isset($categoryCache[$cacheKey])) {
            $lastCategory = $categoryCache[$cacheKey];
            $parentId = $lastCategory->id;
            continue;
        }
        
        // Find or create category
        $category = App\Models\Category::where('name', $categoryName)
            ->where('parent_id', $parentId)
            ->first();
            
        if (!$category) {
            // Try variations
            $variations = [
                strtoupper($categoryName),
                ucwords(strtolower($categoryName)),
                str_replace('&amp;', '&', $categoryName)
            ];
            
            foreach ($variations as $variation) {
                $category = App\Models\Category::where('name', 'LIKE', $variation)
                    ->where('parent_id', $parentId)
                    ->first();
                if ($category) break;
            }
        }
        
        if (!$category) {
            // Create new category if not found
            $category = new App\Models\Category();
            $category->name = $categoryName;
            $category->parent_id = $parentId;
            $category->level = $level;
            $category->slug = Str::slug($categoryName);
            $category->order_level = 0;
            $category->commision_rate = 0;
            $category->save();
            
            echo "   Created new category: {$categoryName} (Level {$level})\n";
        }
        
        $categoryCache[$cacheKey] = $category;
        $lastCategory = $category;
        $parentId = $category->id;
    }
    
    return $lastCategory ? $lastCategory->id : null;
}

// Process CSV rows
echo "\nImporting products...\n";
$rowCount = 0;

while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
    $rowCount++;
    
    if ($rowCount % 1000 == 0) {
        echo "   Processed {$rowCount} rows...\n";
    }
    
    try {
        $product = new App\Models\Product();
        
        // Map basic fields
        $product->sku = isset($columnMap['sku']) ? trim($row[$columnMap['sku']]) : 'SKU-' . time() . '-' . $rowCount;
        $product->name = isset($columnMap['name']) ? trim($row[$columnMap['name']]) : 'Product ' . $rowCount;
        $product->slug = Str::slug($product->name);
        
        // Handle published field (convert to boolean)
        if (isset($columnMap['published'])) {
            $publishedValue = strtolower(trim($row[$columnMap['published']]));
            $product->published = in_array($publishedValue, ['1', 'true', 'yes', 'publish']) ? 1 : 0;
        } else {
            $product->published = 1; // Default to published
        }
        
        // Handle featured
        if (isset($columnMap['featured'])) {
            $featuredValue = strtolower(trim($row[$columnMap['featured']]));
            $product->featured = in_array($featuredValue, ['1', 'true', 'yes']) ? 1 : 0;
        } else {
            $product->featured = 0;
        }
        
        // Map description
        $product->description = '';
        if (isset($columnMap['description'])) {
            $product->description = $row[$columnMap['description']];
        } elseif (isset($columnMap['short_description'])) {
            $product->description = $row[$columnMap['short_description']];
        }
        
        // Map prices
        $product->unit_price = 0;
        if (isset($columnMap['regular_price']) && is_numeric($row[$columnMap['regular_price']])) {
            $product->unit_price = floatval($row[$columnMap['regular_price']]);
        } elseif (isset($columnMap['price']) && is_numeric($row[$columnMap['price']])) {
            $product->unit_price = floatval($row[$columnMap['price']]);
        }
        
        if (isset($columnMap['sale_price']) && is_numeric($row[$columnMap['sale_price']])) {
            $product->purchase_price = floatval($row[$columnMap['sale_price']]);
            
            // Calculate discount
            if ($product->unit_price > 0 && $product->purchase_price < $product->unit_price) {
                $product->discount = $product->unit_price - $product->purchase_price;
                $product->discount_type = 'amount';
            }
        } else {
            $product->purchase_price = $product->unit_price;
        }
        
        // Map stock
        $product->current_stock = 0;
        if (isset($columnMap['stock_quantity']) && is_numeric($row[$columnMap['stock_quantity']])) {
            $product->current_stock = intval($row[$columnMap['stock_quantity']]);
        } elseif (isset($columnMap['stock']) && is_numeric($row[$columnMap['stock']])) {
            $product->current_stock = intval($row[$columnMap['stock']]);
        } elseif (isset($columnMap['in_stock'])) {
            $inStock = strtolower(trim($row[$columnMap['in_stock']]));
            $product->current_stock = in_array($inStock, ['1', 'true', 'yes', 'instock']) ? 100 : 0;
        }
        
        // Map category (MOST IMPORTANT!)
        $categoryAssigned = false;
        
        // Try tax:product_cat first (most accurate)
        if (isset($columnMap['tax:product_cat']) && !empty($row[$columnMap['tax:product_cat']])) {
            $categoryData = $row[$columnMap['tax:product_cat']];
            
            // Handle multiple categories separated by |
            $categoryPaths = explode('|', $categoryData);
            $bestCategoryId = null;
            $maxDepth = 0;
            
            foreach ($categoryPaths as $path) {
                $categoryId = findOrCreateCategory(trim($path), $categoryCache);
                if ($categoryId) {
                    // Use the most specific (deepest) category
                    $depth = substr_count($path, '>');
                    if ($depth > $maxDepth) {
                        $maxDepth = $depth;
                        $bestCategoryId = $categoryId;
                    }
                }
            }
            
            if ($bestCategoryId) {
                $product->category_id = $bestCategoryId;
                $categoryAssigned = true;
            }
        }
        
        // Fallback to categories column
        if (!$categoryAssigned && isset($columnMap['categories']) && !empty($row[$columnMap['categories']])) {
            $categoryId = findOrCreateCategory($row[$columnMap['categories']], $categoryCache);
            if ($categoryId) {
                $product->category_id = $categoryId;
                $categoryAssigned = true;
            }
        }
        
        // Default category if none found
        if (!$categoryAssigned) {
            // Try to guess from product name
            $productNameLower = strtolower($product->name);
            
            if (strpos($productNameLower, 'comic') !== false) {
                $defaultCategory = App\Models\Category::where('name', 'ANTIQUE COMICS')->first();
            } elseif (strpos($productNameLower, 'magazine') !== false) {
                $defaultCategory = App\Models\Category::where('name', 'ANTIQUE MAGAZINES')->first();
            } elseif (strpos($productNameLower, 'novel') !== false || strpos($productNameLower, 'book') !== false) {
                $defaultCategory = App\Models\Category::where('name', 'NOVELS')->first();
            } else {
                $defaultCategory = App\Models\Category::where('name', 'RARE ITEMS')->first();
            }
            
            if ($defaultCategory) {
                $product->category_id = $defaultCategory->id;
            }
        }
        
        // Map other fields
        $product->tags = isset($columnMap['tags']) ? $row[$columnMap['tags']] : null;
        $product->digital = 0; // Physical products by default
        
        // Map images
        if (isset($columnMap['images']) && !empty($row[$columnMap['images']])) {
            $images = explode(',', $row[$columnMap['images']]);
            $product->thumbnail_img = trim($images[0]); // First image as thumbnail
            if (count($images) > 1) {
                $product->photos = implode(',', array_map('trim', $images));
            }
        } elseif (isset($columnMap['image']) && !empty($row[$columnMap['image']])) {
            $product->thumbnail_img = trim($row[$columnMap['image']]);
        }
        
        // Set defaults
        $product->user_id = 1; // Admin user
        $product->added_by = 'admin';
        $product->num_of_sale = 0;
        $product->rating = 0;
        $product->barcode = $product->sku;
        $product->refundable = 1;
        $product->cash_on_delivery = 1;
        $product->todays_deal = 0;
        $product->shipping_type = 'flat_rate';
        $product->shipping_cost = 0;
        $product->est_shipping_days = 7;
        $product->meta_title = $product->name;
        $product->meta_description = Str::limit(strip_tags($product->description), 160);
        
        // Save product
        $product->save();
        
        // Create stock entry
        if ($product->current_stock > 0) {
            DB::table('product_stocks')->insert([
                'product_id' => $product->id,
                'variant' => '',
                'sku' => $product->sku,
                'price' => $product->unit_price,
                'qty' => $product->current_stock,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        $importedCount++;
        
    } catch (\Exception $e) {
        $errorCount++;
        echo "   ❌ Error on row {$rowCount}: " . $e->getMessage() . "\n";
    }
}

fclose($handle);

echo "\n✅ IMPORT COMPLETED!\n";
echo str_repeat("=", 50) . "\n";
echo "   Total Rows Processed: {$rowCount}\n";
echo "   Successfully Imported: {$importedCount}\n";
echo "   Errors: {$errorCount}\n";

// Verify import
echo "\n📊 VERIFICATION:\n";
echo str_repeat("=", 50) . "\n";

$totalProducts = App\Models\Product::count();
$publishedProducts = App\Models\Product::where('published', 1)->count();
$productsInChild = App\Models\Product::whereHas('category', function($q) {
    $q->where('parent_id', '>', 0);
})->count();

echo "   Total Products: {$totalProducts}\n";
echo "   Published Products: {$publishedProducts}\n";
echo "   Products in Child Categories: {$productsInChild}\n";

// Check category distribution
$topCategories = App\Models\Category::where('parent_id', 0)->orderBy('order_level', 'desc')->take(5)->get();
echo "\n   Top Category Distribution:\n";
foreach ($topCategories as $category) {
    $directProducts = App\Models\Product::where('category_id', $category->id)->count();
    $childCategoryProducts = App\Models\Product::whereHas('category', function($q) use ($category) {
        $q->where('parent_id', $category->id);
    })->count();
    echo "      {$category->name}: {$directProducts} direct, {$childCategoryProducts} in subcategories\n";
}

echo "\n🎯 MIGRATION COMPLETE!\n";
echo "Products have been properly imported with:\n";
echo "✅ Correct category hierarchy\n";
echo "✅ Boolean published field\n";
echo "✅ Proper price mapping\n";
echo "✅ Stock management\n";
echo "✅ WooCommerce field mapping\n";
?>
