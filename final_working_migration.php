<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "=== FINAL WORKING MIGRATION ===\n\n";

// Clear existing products
echo "🧹 Clearing existing products...\n";
DB::statement('SET FOREIGN_KEY_CHECKS=0');
DB::table('products')->truncate();
DB::table('product_stocks')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "📥 Starting import from CSV...\n\n";

$csvFile = 'product.csv';
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle, 0, ',', '"', '\\');

// Create column map - ensure we map by exact column position
$columns = [];
foreach ($header as $i => $col) {
    $columns[trim($col)] = $i;
}

echo "Found " . count($columns) . " columns\n";

// Debug: Show actual column names
$keyColumns = ['post_title', 'post_name', 'sku', 'regular_price', 'tax:product_cat'];
foreach ($keyColumns as $key) {
    if (isset($columns[$key])) {
        echo "{$key} at index: " . $columns[$key] . "\n";
    }
}

// Check if we need to use index 0 directly for name
$nameColumnIndex = isset($columns['post_title']) ? $columns['post_title'] : 0;
$skuColumnIndex = isset($columns['sku']) ? $columns['sku'] : 12;

echo "Using name column at index: {$nameColumnIndex}\n";
echo "Using SKU column at index: {$skuColumnIndex}\n\n";

$imported = 0;
$skipped = 0;
$errors = 0;
$rowNum = 0;

// Process rows
while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
    $rowNum++;
    
    if ($rowNum % 1000 == 0) {
        echo "Processing row {$rowNum}... ({$imported} imported)\n";
    }
    
    try {
        // Get product name using the determined index
        $name = isset($row[$nameColumnIndex]) ? trim($row[$nameColumnIndex]) : '';
        
        // Skip if no name
        if (empty($name)) {
            $skipped++;
            continue;
        }
        
        // Get SKU using the determined index
        $sku = isset($row[$skuColumnIndex]) ? trim($row[$skuColumnIndex]) : '';
        if (empty($sku)) {
            $sku = 'PROD-' . $rowNum;
        }
        
        // Check if product already exists
        if (App\Models\Product::where('sku', $sku)->exists()) {
            $skipped++;
            continue;
        }
        
        $product = new App\Models\Product();
        
        // Basic info
        $product->sku = $sku;
        $product->name = $name;
        $slugIndex = isset($columns['post_name']) ? $columns['post_name'] : 1;
        $product->slug = isset($row[$slugIndex]) ? $row[$slugIndex] : Str::slug($name);
        
        // Status
        $statusIndex = isset($columns['post_status']) ? $columns['post_status'] : 6;
        $status = isset($row[$statusIndex]) ? $row[$statusIndex] : 'publish';
        $product->published = ($status == 'publish') ? 1 : 0;
        
        // Description
        $contentIndex = isset($columns['post_content']) ? $columns['post_content'] : 4;
        $excerptIndex = isset($columns['post_excerpt']) ? $columns['post_excerpt'] : 5;
        $description = '';
        if (isset($row[$contentIndex]) && !empty($row[$contentIndex])) {
            $description = strip_tags($row[$contentIndex]);
        } elseif (isset($row[$excerptIndex]) && !empty($row[$excerptIndex])) {
            $description = strip_tags($row[$excerptIndex]);
        }
        $product->description = $description;
        $product->meta_description = Str::limit($description, 160);
        
        // Prices
        $priceIndex = isset($columns['regular_price']) ? $columns['regular_price'] : 18;
        $salePriceIndex = isset($columns['sale_price']) ? $columns['sale_price'] : 19;
        $regularPrice = isset($row[$priceIndex]) ? $row[$priceIndex] : '0';
        $salePrice = isset($row[$salePriceIndex]) ? $row[$salePriceIndex] : '';
        
        $product->unit_price = is_numeric($regularPrice) ? floatval($regularPrice) : 0;
        
        if (!empty($salePrice) && is_numeric($salePrice) && floatval($salePrice) > 0) {
            $product->purchase_price = floatval($salePrice);
            if ($product->purchase_price < $product->unit_price) {
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
        $stockIndex = isset($columns['stock']) ? $columns['stock'] : 17;
        $stockStatusIndex = isset($columns['stock_status']) ? $columns['stock_status'] : 26;
        $stock = isset($row[$stockIndex]) ? $row[$stockIndex] : '';
        $stockStatus = isset($row[$stockStatusIndex]) ? $row[$stockStatusIndex] : 'instock';
        
        if (is_numeric($stock)) {
            $product->current_stock = intval($stock);
        } else {
            $product->current_stock = ($stockStatus == 'instock') ? 100 : 0;
        }
        
        // Category
        $categoryIndex = isset($columns['tax:product_cat']) ? $columns['tax:product_cat'] : 49;
        $categoryStr = isset($row[$categoryIndex]) ? $row[$categoryIndex] : '';
        $categoryId = 1; // Default category
        
        if (!empty($categoryStr)) {
            // Parse category string
            $categories = explode('|', $categoryStr);
            
            foreach ($categories as $catPath) {
                // Handle hierarchical categories (e.g., "ANTIQUE COMICS > MANOJ COMICS")
                if (strpos($catPath, '>') !== false) {
                    $parts = array_map('trim', explode('>', $catPath));
                    $catName = end($parts); // Get the most specific category
                } else {
                    $catName = trim($catPath);
                }
                
                // Find category in database
                $category = App\Models\Category::where('name', $catName)
                    ->orWhere('name', 'LIKE', '%' . $catName . '%')
                    ->orderBy('level', 'desc') // Prefer child categories
                    ->first();
                
                if ($category) {
                    $categoryId = $category->id;
                    break;
                }
            }
        }
        
        // Auto-categorize if no category found
        if ($categoryId == 1) {
            $nameLower = strtolower($name);
            
            if (strpos($nameLower, 'comic') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%COMIC%')
                    ->where('parent_id', '>', 0)->first();
            } elseif (strpos($nameLower, 'magazine') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%MAGAZINE%')
                    ->where('parent_id', '>', 0)->first();
            } elseif (strpos($nameLower, 'stamp') !== false || strpos($nameLower, 'postage') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%PHILATELY%')->first();
            } elseif (strpos($nameLower, 'painting') !== false || strpos($nameLower, 'art') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%ART%')
                    ->where('parent_id', '>', 0)->first();
            } elseif (strpos($nameLower, 'book') !== false || strpos($nameLower, 'novel') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%NOVEL%')
                    ->orWhere('name', 'LIKE', '%book%')
                    ->where('parent_id', '>', 0)->first();
            } elseif (strpos($nameLower, 'diary') !== false || strpos($nameLower, 'journal') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%DIAR%')->first();
            } elseif (strpos($nameLower, 'cassette') !== false || strpos($nameLower, 'audio') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%CASSETTE%')->first();
            } elseif (strpos($nameLower, 'vinyl') !== false || strpos($nameLower, 'record') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%VINYL%')->first();
            } else {
                $cat = App\Models\Category::where('name', 'LIKE', '%RARE%')->first();
            }
            
            if ($cat) {
                $categoryId = $cat->id;
            }
        }
        
        $product->category_id = $categoryId;
        
        // Tags
        $tagsIndex = isset($columns['tax:product_tag']) ? $columns['tax:product_tag'] : 50;
        $product->tags = isset($row[$tagsIndex]) ? $row[$tagsIndex] : '';
        
        // Tax
        $taxStatusIndex = isset($columns['tax_status']) ? $columns['tax_status'] : 31;
        $taxStatus = isset($row[$taxStatusIndex]) ? $row[$taxStatusIndex] : 'taxable';
        $product->tax = ($taxStatus == 'taxable') ? 1 : 0;
        $product->tax_type = 'percent';
        
        // Digital/Virtual
        $downloadableIndex = isset($columns['downloadable']) ? $columns['downloadable'] : 15;
        $virtualIndex = isset($columns['virtual']) ? $columns['virtual'] : 16;
        $downloadable = isset($row[$downloadableIndex]) ? $row[$downloadableIndex] : 'no';
        $virtual = isset($row[$virtualIndex]) ? $row[$virtualIndex] : 'no';
        $product->digital = ($downloadable == 'yes' || $virtual == 'yes') ? 1 : 0;
        
        // Images
        $imagesIndex = isset($columns['images']) ? $columns['images'] : 41;
        $images = isset($row[$imagesIndex]) ? $row[$imagesIndex] : '';
        if ($images) {
            // Extract URLs from image string
            if (preg_match('/https?:\/\/[^\s!]+/', $images, $matches)) {
                $product->thumbnail_img = $matches[0];
                $product->photos = $matches[0];
            }
        }
        
        // Weight
        $weightIndex = isset($columns['weight']) ? $columns['weight'] : 20;
        $product->weight = isset($row[$weightIndex]) ? floatval($row[$weightIndex]) : 0;
        
        // Required fields
        $product->user_id = 1;
        $product->added_by = 'admin';
        $product->num_of_sale = 0;
        $product->rating = 0;
        $product->barcode = $product->sku;
        $product->refundable = 1;
        $product->featured = 0;
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
        
        // Show first few imports
        if ($imported <= 10) {
            $catName = App\Models\Category::find($product->category_id)->name ?? 'Unknown';
            echo "✅ Imported: {$product->name} → {$catName} (Price: {$product->unit_price})\n";
        }
        
    } catch (\Exception $e) {
        $errors++;
        if ($errors <= 10) {
            echo "❌ Error on row {$rowNum}: " . $e->getMessage() . "\n";
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
echo "\n📊 FINAL VERIFICATION\n";
echo str_repeat("=", 50) . "\n";

$stats = [
    'Total Products' => App\Models\Product::count(),
    'Published' => App\Models\Product::where('published', 1)->count(),
    'With Stock' => App\Models\Product::where('current_stock', '>', 0)->count(),
    'With Images' => App\Models\Product::whereNotNull('thumbnail_img')->count(),
    'In Parent Categories' => App\Models\Product::whereHas('category', fn($q) => $q->where('parent_id', 0))->count(),
    'In Child Categories' => App\Models\Product::whereHas('category', fn($q) => $q->where('parent_id', '>', 0))->count(),
];

foreach ($stats as $label => $value) {
    echo "{$label}: {$value}\n";
}

// Top categories with products
echo "\n📂 TOP CATEGORIES WITH PRODUCTS\n";
$topCategories = DB::table('products')
    ->join('categories', 'products.category_id', '=', 'categories.id')
    ->select('categories.name', 'categories.parent_id', DB::raw('count(products.id) as count'))
    ->groupBy('categories.id', 'categories.name', 'categories.parent_id')
    ->orderBy('count', 'desc')
    ->limit(10)
    ->get();

foreach ($topCategories as $cat) {
    $type = $cat->parent_id == 0 ? ' (Parent)' : ' (Child)';
    echo "{$cat->name}{$type}: {$cat->count} products\n";
}

echo "\n✨ Migration completed successfully!\n";

// Clear caches
Artisan::call('cache:clear');
Artisan::call('view:clear');
echo "🧹 Caches cleared\n";

echo "\n🎯 Products are now properly imported with correct categories!\n";
?>
