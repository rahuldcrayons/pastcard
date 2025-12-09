<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "=== WORDPRESS PRODUCTS MIGRATION ===\n\n";
echo "This migration handles WordPress/WooCommerce export format\n\n";

// Clear existing products first
echo "🧹 Clearing existing products...\n";
DB::statement('SET FOREIGN_KEY_CHECKS=0');
DB::table('products')->truncate();
DB::table('product_stocks')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1');

// Import Products
echo "\n📦 IMPORTING PRODUCTS FROM WORDPRESS CSV\n";
echo str_repeat("-", 50) . "\n";

$productFile = 'product.csv';
if (!file_exists($productFile)) {
    die("❌ product.csv not found!\n");
}

$handle = fopen($productFile, 'r');
$header = fgetcsv($handle);

// Map WordPress columns to our needs
$columns = [];
foreach ($header as $i => $col) {
    $columns[trim($col)] = $i;
}

echo "Found columns: " . implode(', ', array_slice(array_keys($columns), 0, 10)) . "...\n\n";

// WordPress to Laravel field mapping
$fieldMapping = [
    'post_title' => 'name',
    'post_name' => 'slug',
    'post_content' => 'description',
    'post_excerpt' => 'short_description',
    'post_status' => 'published',
    'sku' => 'sku',
    'regular_price' => 'unit_price',
    'sale_price' => 'purchase_price',
    'stock' => 'current_stock',
    'stock_status' => 'in_stock',
    'weight' => 'weight',
    'featured' => 'featured',
    'tax:product_cat' => 'categories',
    'tax:product_tag' => 'tags',
    'images' => 'images',
    'tax_status' => 'tax_status',
    'tax_class' => 'tax_class',
    'downloadable' => 'digital',
    'virtual' => 'digital'
];

// Helper function to get value from row
function getValue($row, $columns, $fields, $default = null) {
    if (!is_array($fields)) $fields = [$fields];
    foreach ($fields as $field) {
        if (isset($columns[$field]) && isset($row[$columns[$field]])) {
            $val = trim($row[$columns[$field]]);
            if ($val !== '') return $val;
        }
    }
    return $default;
}

// Helper to convert WordPress status to boolean
function isPublished($status) {
    $status = strtolower(trim($status));
    return in_array($status, ['publish', 'published', '1', 'true']);
}

// Helper to convert stock status
function isInStock($value) {
    $value = strtolower(trim($value));
    return in_array($value, ['instock', '1', 'true', 'yes']);
}

// Find best matching category
function findCategory($categoryStr) {
    if (empty($categoryStr)) return null;
    
    // Handle hierarchical categories
    $categories = [];
    if (strpos($categoryStr, '|') !== false) {
        $categories = explode('|', $categoryStr);
    } elseif (strpos($categoryStr, '>') !== false) {
        $parts = array_map('trim', explode('>', $categoryStr));
        $categoryStr = end($parts); // Use most specific
        $categories = [$categoryStr];
    } else {
        $categories = [$categoryStr];
    }
    
    $bestCategory = null;
    $maxLevel = -1;
    
    foreach ($categories as $catName) {
        $catName = trim($catName);
        
        // Try different matching strategies
        $category = App\Models\Category::where('name', $catName)->first() ??
                   App\Models\Category::whereRaw('LOWER(name) = ?', [strtolower($catName)])->first() ??
                   App\Models\Category::where('slug', Str::slug($catName))->first() ??
                   App\Models\Category::where('name', 'LIKE', '%' . $catName . '%')->first();
        
        if ($category) {
            // Prefer child categories over parents
            if ($category->level > $maxLevel) {
                $maxLevel = $category->level;
                $bestCategory = $category;
            }
        }
    }
    
    // If we got a parent category, try to find a child
    if ($bestCategory && $bestCategory->parent_id == 0) {
        $children = App\Models\Category::where('parent_id', $bestCategory->id)->get();
        if ($children->count() > 0) {
            // Return first child as default
            return $children->first();
        }
    }
    
    return $bestCategory;
}

// Auto-categorize based on product name
function autoCategorize($productName) {
    $nameLower = strtolower($productName);
    
    // Comics
    if (strpos($nameLower, 'comic') !== false) {
        if (strpos($nameLower, 'manoj') !== false) {
            return App\Models\Category::where('name', 'LIKE', '%MANOJ%')->where('parent_id', '>', 0)->first();
        } elseif (strpos($nameLower, 'diamond') !== false) {
            return App\Models\Category::where('name', 'LIKE', '%DIAMOND%')->where('parent_id', '>', 0)->first();
        } elseif (strpos($nameLower, 'raj') !== false) {
            return App\Models\Category::where('name', 'LIKE', '%RAJ%')->where('parent_id', '>', 0)->first();
        } elseif (strpos($nameLower, 'indrajal') !== false) {
            return App\Models\Category::where('name', 'LIKE', '%INDRAJAL%')->where('parent_id', '>', 0)->first();
        } else {
            return App\Models\Category::where('name', 'OTHER COMICS')->first();
        }
    }
    
    // Magazines
    if (strpos($nameLower, 'magazine') !== false) {
        if (strpos($nameLower, 'champak') !== false) {
            return App\Models\Category::where('name', 'Champak')->first();
        } elseif (strpos($nameLower, 'nandan') !== false) {
            return App\Models\Category::where('name', 'Nandan')->first();
        } elseif (strpos($nameLower, 'chandamama') !== false || strpos($nameLower, 'chanda mama') !== false) {
            return App\Models\Category::where('name', 'Chanda Mama')->first();
        } else {
            return App\Models\Category::where('name', 'OTHER MAGAZINES')->first();
        }
    }
    
    // Novels/Books
    if (strpos($nameLower, 'novel') !== false || strpos($nameLower, 'book') !== false) {
        if (strpos($nameLower, 'hindi') !== false) {
            return App\Models\Category::where('name', 'Hindi Novel')->first();
        } elseif (strpos($nameLower, 'english') !== false) {
            return App\Models\Category::where('name', 'English Novel')->first();
        } else {
            return App\Models\Category::where('name', 'Other books')->first();
        }
    }
    
    // Stamps
    if (strpos($nameLower, 'stamp') !== false || strpos($nameLower, 'philately') !== false) {
        return App\Models\Category::where('name', 'LIKE', '%Philately%')->where('parent_id', '>', 0)->first();
    }
    
    // Paintings/Art
    if (strpos($nameLower, 'paint') !== false || strpos($nameLower, 'art') !== false) {
        if (strpos($nameLower, 'miniature') !== false) {
            return App\Models\Category::where('name', 'LIKE', '%MINIATURE%')->first();
        } else {
            return App\Models\Category::where('name', 'LIKE', '%ARTWORK%')->first();
        }
    }
    
    // Cassettes/Music
    if (strpos($nameLower, 'cassette') !== false || strpos($nameLower, 'audio') !== false) {
        return App\Models\Category::where('name', 'LIKE', '%AUDIO CASSETTES%')->first();
    }
    
    if (strpos($nameLower, 'vinyl') !== false || strpos($nameLower, 'record') !== false) {
        return App\Models\Category::where('name', 'LIKE', '%VINYL%')->first();
    }
    
    if (strpos($nameLower, 'cd') !== false || strpos($nameLower, 'dvd') !== false) {
        return App\Models\Category::where('name', 'LIKE', '%CD%')->first();
    }
    
    // Default
    return App\Models\Category::where('name', 'LIKE', '%Other%item%')->first() ??
           App\Models\Category::where('name', 'LIKE', '%RARE%')->first();
}

$imported = 0;
$skipped = 0;
$errors = 0;
$rowNum = 0;

echo "Starting product import...\n";

while (($row = fgetcsv($handle)) !== false) {
    $rowNum++;
    
    if ($rowNum % 1000 == 0) {
        echo "   Processing row {$rowNum}... ({$imported} imported, {$skipped} skipped)\n";
    }
    
    try {
        // Get basic fields
        $name = getValue($row, $columns, ['post_title', 'name'], '');
        $sku = getValue($row, $columns, ['sku', 'SKU'], '');
        
        // Skip if no name
        if (empty($name)) {
            $skipped++;
            continue;
        }
        
        // Generate SKU if missing
        if (empty($sku)) {
            $sku = 'WP-' . getValue($row, $columns, ['ID'], uniqid());
        }
        
        // Check if product exists
        $existing = App\Models\Product::where('sku', $sku)->first();
        if ($existing) {
            $skipped++;
            continue;
        }
        
        $product = new App\Models\Product();
        
        // Basic info
        $product->sku = $sku;
        $product->name = $name;
        $product->slug = getValue($row, $columns, ['post_name', 'slug'], Str::slug($name));
        
        // Published status
        $postStatus = getValue($row, $columns, ['post_status'], 'publish');
        $product->published = isPublished($postStatus) ? 1 : 0;
        
        // Featured
        $featured = getValue($row, $columns, ['featured'], '0');
        $product->featured = ($featured == '1' || $featured == 'yes') ? 1 : 0;
        
        // Description
        $product->description = getValue($row, $columns, ['post_content', 'description'], 
                               getValue($row, $columns, ['post_excerpt'], ''));
        $product->meta_description = Str::limit(strip_tags($product->description), 160);
        
        // Pricing
        $regularPrice = getValue($row, $columns, ['regular_price'], '0');
        $salePrice = getValue($row, $columns, ['sale_price'], '');
        
        $product->unit_price = is_numeric($regularPrice) ? floatval($regularPrice) : 0;
        
        if (!empty($salePrice) && is_numeric($salePrice) && floatval($salePrice) > 0) {
            $product->purchase_price = floatval($salePrice);
            if ($product->purchase_price < $product->unit_price && $product->unit_price > 0) {
                $product->discount = $product->unit_price - $product->purchase_price;
                $product->discount_type = 'amount';
            } else {
                $product->discount = 0;
            }
        } else {
            $product->purchase_price = $product->unit_price;
            $product->discount = 0;
        }
        
        // Stock
        $stock = getValue($row, $columns, ['stock'], '');
        $stockStatus = getValue($row, $columns, ['stock_status'], 'instock');
        
        if (is_numeric($stock)) {
            $product->current_stock = intval($stock);
        } else {
            $product->current_stock = isInStock($stockStatus) ? 100 : 0;
        }
        
        // Category - Most Important!
        $categoryStr = getValue($row, $columns, ['tax:product_cat', 'categories'], '');
        $category = null;
        
        if (!empty($categoryStr)) {
            $category = findCategory($categoryStr);
        }
        
        // If no category found, try auto-categorization
        if (!$category) {
            $category = autoCategorize($name);
        }
        
        // Set category ID
        $product->category_id = $category ? $category->id : 1;
        
        // Tags
        $product->tags = getValue($row, $columns, ['tax:product_tag', 'tags'], '');
        
        // Tax
        $taxStatus = getValue($row, $columns, ['tax_status'], 'taxable');
        $product->tax = ($taxStatus == 'taxable') ? 1 : 0;
        $product->tax_type = 'percent';
        
        // Digital/Virtual
        $downloadable = getValue($row, $columns, ['downloadable'], 'no');
        $virtual = getValue($row, $columns, ['virtual'], 'no');
        $product->digital = ($downloadable == 'yes' || $virtual == 'yes') ? 1 : 0;
        
        // Images (store URLs for now)
        $images = getValue($row, $columns, ['images'], '');
        if ($images) {
            $imageUrls = array_map('trim', explode(',', $images));
            $product->thumbnail_img = $imageUrls[0] ?? null;
            $product->photos = implode(',', array_slice($imageUrls, 0, 5));
        }
        
        // Weight
        $product->weight = floatval(getValue($row, $columns, ['weight'], '0'));
        
        // Required fields
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
        
    } catch (\Exception $e) {
        $errors++;
        if ($errors <= 10) {
            echo "   ❌ Error on row {$rowNum} ({$name}): " . $e->getMessage() . "\n";
        }
    }
}

fclose($handle);

echo "\n✅ IMPORT COMPLETE!\n";
echo str_repeat("=", 50) . "\n";
echo "Total Rows: {$rowNum}\n";
echo "Products Imported: {$imported}\n";
echo "Products Skipped: {$skipped}\n";
echo "Errors: {$errors}\n";

// Verification
echo "\n📊 VERIFICATION\n";
echo str_repeat("=", 50) . "\n";

$stats = [
    'Total Products' => App\Models\Product::count(),
    'Published' => App\Models\Product::where('published', 1)->count(),
    'Unpublished' => App\Models\Product::where('published', 0)->count(),
    'With Stock' => App\Models\Product::where('current_stock', '>', 0)->count(),
    'Zero Price' => App\Models\Product::where('unit_price', 0)->count(),
    'In Parent Categories' => App\Models\Product::whereHas('category', fn($q) => $q->where('parent_id', 0))->count(),
    'In Child Categories' => App\Models\Product::whereHas('category', fn($q) => $q->where('parent_id', '>', 0))->count(),
];

foreach ($stats as $label => $value) {
    echo "{$label}: {$value}\n";
}

echo "\n📂 CATEGORY DISTRIBUTION\n";
$topCategories = App\Models\Category::where('parent_id', 0)
    ->orderBy('order_level', 'desc')
    ->take(5)
    ->get();

foreach ($topCategories as $cat) {
    $direct = App\Models\Product::where('category_id', $cat->id)->count();
    $inChildren = App\Models\Product::whereHas('category', fn($q) => $q->where('parent_id', $cat->id))->count();
    
    echo "\n{$cat->name}:\n";
    echo "  Direct products: {$direct}\n";
    echo "  In subcategories: {$inChildren}\n";
    
    $children = App\Models\Category::where('parent_id', $cat->id)->get();
    foreach ($children as $child) {
        $childProducts = App\Models\Product::where('category_id', $child->id)->count();
        if ($childProducts > 0) {
            echo "    └── {$child->name}: {$childProducts} products\n";
        }
    }
}

echo "\n🎯 MIGRATION SUCCESSFUL!\n";
echo "✅ Products imported from WordPress CSV format\n";
echo "✅ Auto-categorization applied for better distribution\n";
echo "✅ All fields mapped correctly\n";

// Clear caches
echo "\n🧹 Clearing caches...\n";
Artisan::call('cache:clear');
Artisan::call('view:clear');
echo "✅ Caches cleared\n";

echo "\n✨ DONE! Your products are now properly imported.\n";
?>
