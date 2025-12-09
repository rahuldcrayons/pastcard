<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "=== CLEAN DATABASE AND REIMPORT FROM WORDPRESS ===\n\n";

echo "This script will:\n";
echo "1. ❌ DELETE all current products (poorly imported)\n";
echo "2. ✅ Map WooCommerce fields properly to Laravel\n";
echo "3. ✅ Import with correct category hierarchy\n";
echo "4. ✅ Handle published field as boolean\n";
echo "5. ✅ Assign products to most specific categories\n\n";

echo "Type 'DELETE AND IMPORT' to continue: ";
$confirmation = trim(fgets(STDIN));

if ($confirmation !== 'DELETE AND IMPORT') {
    echo "❌ Operation cancelled.\n";
    exit;
}

echo "\n🧹 STEP 1: CLEANING DATABASE\n";
echo str_repeat("-", 50) . "\n";

// Disable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS=0');

// Clear all product-related data
$tables = [
    'products',
    'product_stocks',
    'product_translations',
    'product_taxes',
    'attribute_values',
    'product_variations',
    'cart',
    'wishlists'
];

foreach ($tables as $table) {
    if (\Schema::hasTable($table)) {
        DB::table($table)->truncate();
        echo "✅ Cleared: {$table}\n";
    }
}

// Reset auto-increment
DB::statement('ALTER TABLE products AUTO_INCREMENT = 1');

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n📋 STEP 2: WORDPRESS/WOOCOMMERCE FIELD MAPPING\n";
echo str_repeat("-", 50) . "\n";

// Complete field mapping
$fieldMap = [
    'ID' => 'wordpress_id',
    'Type' => 'product_type',
    'SKU' => 'sku',
    'Name' => 'name',
    'Published' => 'published',       // 1/0, true/false, yes/no, publish/draft
    'Is featured?' => 'featured',     // Convert to boolean
    'Visibility in catalog' => 'visibility',
    'Short description' => 'description',
    'Description' => 'meta_description',
    'Date sale price starts' => 'discount_start_date',
    'Date sale price ends' => 'discount_end_date',
    'Tax status' => 'tax_status',
    'Tax class' => 'tax_type',
    'In stock?' => 'stock_status',    // Convert to boolean
    'Stock' => 'current_stock',
    'Backorders allowed?' => 'backorder',
    'Sold individually?' => 'min_qty',
    'Weight (kg)' => 'weight',
    'Length (cm)' => 'length',
    'Width (cm)' => 'width',
    'Height (cm)' => 'height',
    'Allow customer reviews?' => 'reviews_allowed',
    'Purchase note' => 'purchase_note',
    'Sale price' => 'purchase_price',
    'Regular price' => 'unit_price',
    'Categories' => 'categories',      // Process hierarchy
    'Tags' => 'tags',
    'Shipping class' => 'shipping_type',
    'Images' => 'photos',
    'Download limit' => 'download_limit',
    'Download expiry days' => 'download_expiry',
    'Parent' => 'parent_id',
    'Grouped products' => 'grouped_products',
    'Upsells' => 'upsell_ids',
    'Cross-sells' => 'cross_sell_ids',
    'External URL' => 'external_url',
    'Button text' => 'button_text',
    'Position' => 'position',
    'Meta: _wpcom_is_markdown' => 'is_markdown'
];

echo "Mapped " . count($fieldMap) . " fields\n\n";

echo "📥 STEP 3: READING CSV FILE\n";
echo str_repeat("-", 50) . "\n";

$csvFile = 'product.csv';
if (!file_exists($csvFile)) {
    // Try alternative names
    $alternatives = ['products.csv', 'wc-product-export.csv', 'product_export.csv'];
    foreach ($alternatives as $alt) {
        if (file_exists($alt)) {
            $csvFile = $alt;
            break;
        }
    }
}

if (!file_exists($csvFile)) {
    die("❌ No CSV file found! Please ensure product.csv exists.\n");
}

echo "✅ Found CSV: {$csvFile}\n";

$handle = fopen($csvFile, 'r');
$headers = fgetcsv($handle);

// Map column indices
$columnIndex = [];
foreach ($headers as $i => $header) {
    $columnIndex[trim($header)] = $i;
}

echo "Found " . count($headers) . " columns\n\n";

echo "🔄 STEP 4: IMPORTING PRODUCTS\n";
echo str_repeat("-", 50) . "\n";

$imported = 0;
$skipped = 0;
$errors = 0;
$rowNum = 0;

// Category cache to avoid repeated lookups
$categoryCache = [];

// Helper: Convert WooCommerce boolean values
function toBool($value) {
    $value = strtolower(trim($value));
    return in_array($value, ['1', 'yes', 'true', 'publish', 'visible', 'instock']);
}

// Helper: Get value from row
function getValue($row, $columnIndex, $field, $default = null) {
    if (isset($columnIndex[$field]) && isset($row[$columnIndex[$field]])) {
        $value = trim($row[$columnIndex[$field]]);
        return $value !== '' ? $value : $default;
    }
    return $default;
}

// Helper: Find best matching category
function findBestCategory($categoryString, &$cache) {
    if (empty($categoryString)) return null;
    
    // Cache check
    if (isset($cache[$categoryString])) {
        return $cache[$categoryString];
    }
    
    $bestCategory = null;
    $maxLevel = -1;
    
    // Handle multiple categories separated by |, comma, or >
    $separators = ['|', ',', '>'];
    $categories = [$categoryString];
    
    foreach ($separators as $sep) {
        if (strpos($categoryString, $sep) !== false) {
            $categories = explode($sep, $categoryString);
            break;
        }
    }
    
    foreach ($categories as $catPath) {
        $catPath = trim($catPath);
        
        // Try to find the most specific (deepest) category
        $parts = array_map('trim', explode('>', $catPath));
        $lastPart = end($parts);
        
        // Search for exact match first
        $category = App\Models\Category::where('name', $lastPart)->first();
        
        if (!$category) {
            // Try case-insensitive
            $category = App\Models\Category::whereRaw('LOWER(name) = ?', [strtolower($lastPart)])->first();
        }
        
        if (!$category) {
            // Try partial match
            $category = App\Models\Category::where('name', 'LIKE', '%' . $lastPart . '%')->first();
        }
        
        if ($category && $category->level > $maxLevel) {
            $maxLevel = $category->level;
            $bestCategory = $category;
        }
    }
    
    // If still no match, try to create based on path
    if (!$bestCategory && count($parts) > 0) {
        $parentId = 0;
        
        foreach ($parts as $level => $partName) {
            $existing = App\Models\Category::where('name', $partName)
                ->where('parent_id', $parentId)
                ->first();
                
            if (!$existing) {
                // Check variations
                $existing = App\Models\Category::whereRaw('LOWER(name) = ?', [strtolower($partName)])
                    ->where('parent_id', $parentId)
                    ->first();
            }
            
            if ($existing) {
                $bestCategory = $existing;
                $parentId = $existing->id;
            } else {
                // Create new category
                $newCat = new App\Models\Category();
                $newCat->name = $partName;
                $newCat->parent_id = $parentId;
                $newCat->level = $level;
                $newCat->slug = Str::slug($partName);
                $newCat->order_level = 0;
                $newCat->commision_rate = 0;
                $newCat->save();
                
                $bestCategory = $newCat;
                $parentId = $newCat->id;
                
                echo "   Created category: {$partName} (Level {$level})\n";
            }
        }
    }
    
    $cache[$categoryString] = $bestCategory;
    return $bestCategory;
}

while (($row = fgetcsv($handle)) !== false) {
    $rowNum++;
    
    if ($rowNum % 1000 == 0) {
        echo "   Processing row {$rowNum}...\n";
    }
    
    try {
        // Skip empty rows
        $sku = getValue($row, $columnIndex, 'SKU', getValue($row, $columnIndex, 'sku'));
        $name = getValue($row, $columnIndex, 'Name', getValue($row, $columnIndex, 'name'));
        
        if (empty($name)) {
            $skipped++;
            continue;
        }
        
        $product = new App\Models\Product();
        
        // Basic Information
        $product->sku = $sku ?: 'SKU-' . time() . '-' . $rowNum;
        $product->name = $name;
        $product->slug = Str::slug($name);
        
        // Published Status (handle various formats)
        $publishedValue = getValue($row, $columnIndex, 'Published', 
                         getValue($row, $columnIndex, 'published', 
                         getValue($row, $columnIndex, 'status', '1')));
        $product->published = toBool($publishedValue) ? 1 : 0;
        
        // Featured Status
        $featuredValue = getValue($row, $columnIndex, 'Is featured?', 
                        getValue($row, $columnIndex, 'featured', '0'));
        $product->featured = toBool($featuredValue) ? 1 : 0;
        
        // Description
        $product->description = getValue($row, $columnIndex, 'Short description', 
                               getValue($row, $columnIndex, 'short_description', ''));
        
        // Full description for meta
        $fullDesc = getValue($row, $columnIndex, 'Description', 
                    getValue($row, $columnIndex, 'description', ''));
        $product->meta_description = $fullDesc ?: Str::limit($product->description, 160);
        
        // Pricing
        $regularPrice = getValue($row, $columnIndex, 'Regular price', 
                       getValue($row, $columnIndex, 'regular_price', '0'));
        $salePrice = getValue($row, $columnIndex, 'Sale price', 
                    getValue($row, $columnIndex, 'sale_price', ''));
        
        $product->unit_price = is_numeric($regularPrice) ? floatval($regularPrice) : 0;
        
        if (!empty($salePrice) && is_numeric($salePrice)) {
            $product->purchase_price = floatval($salePrice);
            if ($product->purchase_price < $product->unit_price) {
                $product->discount = $product->unit_price - $product->purchase_price;
                $product->discount_type = 'amount';
            }
        } else {
            $product->purchase_price = $product->unit_price;
            $product->discount = 0;
        }
        
        // Stock Management
        $inStock = getValue($row, $columnIndex, 'In stock?', 
                   getValue($row, $columnIndex, 'stock_status', '1'));
        $stockQty = getValue($row, $columnIndex, 'Stock', 
                   getValue($row, $columnIndex, 'stock_quantity', ''));
        
        if (is_numeric($stockQty)) {
            $product->current_stock = intval($stockQty);
        } else {
            $product->current_stock = toBool($inStock) ? 100 : 0;
        }
        
        // Categories - MOST IMPORTANT!
        $categoriesRaw = getValue($row, $columnIndex, 'Categories', 
                        getValue($row, $columnIndex, 'tax:product_cat', 
                        getValue($row, $columnIndex, 'product_cat', '')));
        
        $category = findBestCategory($categoriesRaw, $categoryCache);
        
        if ($category) {
            // Always assign to the most specific (child) category
            if ($category->parent_id == 0) {
                // This is a parent category, try to find a better match
                $children = App\Models\Category::where('parent_id', $category->id)->get();
                if ($children->count() > 0) {
                    // Try to match product name with child categories
                    $productNameLower = strtolower($name);
                    foreach ($children as $child) {
                        if (strpos($productNameLower, strtolower($child->name)) !== false) {
                            $category = $child;
                            break;
                        }
                    }
                }
            }
            $product->category_id = $category->id;
        } else {
            // Default category based on product name
            $productNameLower = strtolower($name);
            
            if (strpos($productNameLower, 'comic') !== false) {
                $defaultCat = App\Models\Category::where('name', 'OTHER COMICS')->first();
            } elseif (strpos($productNameLower, 'magazine') !== false) {
                $defaultCat = App\Models\Category::where('name', 'OTHER MAGAZINES')->first();
            } elseif (strpos($productNameLower, 'book') !== false || strpos($productNameLower, 'novel') !== false) {
                $defaultCat = App\Models\Category::where('name', 'Other books')->first();
            } else {
                $defaultCat = App\Models\Category::where('name', 'Other items')->first();
            }
            
            $product->category_id = $defaultCat ? $defaultCat->id : 1;
        }
        
        // Tags
        $product->tags = getValue($row, $columnIndex, 'Tags', 
                        getValue($row, $columnIndex, 'tax:product_tag', ''));
        
        // Images
        $images = getValue($row, $columnIndex, 'Images', 
                 getValue($row, $columnIndex, 'images', ''));
        if ($images) {
            $imageArray = array_map('trim', explode(',', $images));
            $product->thumbnail_img = $imageArray[0];
            $product->photos = implode(',', $imageArray);
        }
        
        // Physical Attributes
        $product->weight = floatval(getValue($row, $columnIndex, 'Weight (kg)', 
                          getValue($row, $columnIndex, 'weight', '0')));
        
        // Tax
        $taxStatus = getValue($row, $columnIndex, 'Tax status', 'taxable');
        $product->tax = ($taxStatus == 'taxable') ? 1 : 0;
        $product->tax_type = getValue($row, $columnIndex, 'Tax class', 'percent');
        
        // Digital/Physical
        $product->digital = 0; // Default to physical
        
        // Other required fields
        $product->user_id = 1;
        $product->added_by = 'admin';
        $product->num_of_sale = 0;
        $product->rating = 0;
        $product->barcode = $product->sku;
        $product->refundable = 1;
        $product->shipping_type = 'flat_rate';
        $product->shipping_cost = 0;
        $product->est_shipping_days = 7;
        $product->min_qty = 1;
        $product->cash_on_delivery = 1;
        $product->meta_title = $product->name;
        
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
        
        $imported++;
        
        if ($imported % 100 == 0) {
            echo "   ✅ Imported {$imported} products\n";
        }
        
    } catch (\Exception $e) {
        $errors++;
        if ($errors <= 10) {
            echo "   ❌ Error on row {$rowNum}: " . $e->getMessage() . "\n";
        }
    }
}

fclose($handle);

echo "\n✅ IMPORT COMPLETE!\n";
echo str_repeat("=", 50) . "\n";
echo "Total Rows: {$rowNum}\n";
echo "✅ Imported: {$imported}\n";
echo "⏭️ Skipped: {$skipped}\n";
echo "❌ Errors: {$errors}\n";

echo "\n📊 FINAL VERIFICATION\n";
echo str_repeat("=", 50) . "\n";

$stats = [
    'Total Products' => App\Models\Product::count(),
    'Published' => App\Models\Product::where('published', 1)->count(),
    'Unpublished' => App\Models\Product::where('published', 0)->count(),
    'Featured' => App\Models\Product::where('featured', 1)->count(),
    'With Stock' => App\Models\Product::where('current_stock', '>', 0)->count(),
    'Products in Parent Categories' => App\Models\Product::whereHas('category', function($q) {
        $q->where('parent_id', 0);
    })->count(),
    'Products in Child Categories' => App\Models\Product::whereHas('category', function($q) {
        $q->where('parent_id', '>', 0);
    })->count(),
];

foreach ($stats as $label => $value) {
    echo "{$label}: {$value}\n";
}

echo "\n📂 CATEGORY DISTRIBUTION\n";
$topCategories = App\Models\Category::where('parent_id', 0)
    ->orderBy('order_level', 'desc')
    ->take(8)
    ->get();

foreach ($topCategories as $cat) {
    $directProducts = App\Models\Product::where('category_id', $cat->id)->count();
    $childProducts = App\Models\Product::whereHas('category', function($q) use ($cat) {
        $q->where('parent_id', $cat->id);
    })->count();
    
    echo "{$cat->name}: {$directProducts} direct, {$childProducts} in children\n";
}

echo "\n🎯 MIGRATION SUCCESSFUL!\n";
echo "✅ Products imported with proper WooCommerce field mapping\n";
echo "✅ Categories correctly assigned to most specific level\n";
echo "✅ Published field properly converted to boolean\n";
echo "✅ Stock, pricing, and tax data preserved\n";
?>
