<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "=== DIRECT IMPORT (SIMPLIFIED) ===\n\n";

// Clear existing products
echo "🧹 Clearing products...\n";
DB::statement('SET FOREIGN_KEY_CHECKS=0');
DB::table('products')->truncate();
DB::table('product_stocks')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "📥 Importing from CSV...\n\n";

$csvFile = 'product.csv';
$handle = fopen($csvFile, 'r');

// Skip header
$header = fgetcsv($handle, 0, ',', '"', '\\');

$imported = 0;
$skipped = 0;
$errors = 0;
$rowNum = 0;

// Process each row
while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
    $rowNum++;
    
    if ($rowNum % 1000 == 0) {
        echo "Row {$rowNum}: {$imported} imported, {$skipped} skipped\n";
    }
    
    try {
        // Direct column access - we know these positions work
        $name = isset($row[0]) ? trim($row[0]) : '';
        
        // Skip empty names
        if (empty($name)) {
            $skipped++;
            continue;
        }
        
        // Get or generate SKU
        $sku = isset($row[12]) && !empty(trim($row[12])) ? trim($row[12]) : 'P-' . $rowNum;
        
        // Skip if exists
        if (App\Models\Product::where('sku', $sku)->exists()) {
            $skipped++;
            continue;
        }
        
        $product = new App\Models\Product();
        
        // Basic info
        $product->sku = $sku;
        $product->name = $name;
        $product->slug = isset($row[1]) && !empty($row[1]) ? $row[1] : Str::slug($name);
        
        // Status
        $product->published = (isset($row[6]) && $row[6] == 'publish') ? 1 : 0;
        
        // Description
        $description = '';
        if (isset($row[4]) && !empty($row[4])) {
            $description = strip_tags($row[4]);
        } elseif (isset($row[5]) && !empty($row[5])) {
            $description = strip_tags($row[5]);
        }
        $product->description = substr($description, 0, 5000); // Limit length
        $product->meta_description = Str::limit($description, 160);
        
        // Prices
        $product->unit_price = (isset($row[18]) && is_numeric($row[18])) ? floatval($row[18]) : 0;
        $product->purchase_price = (isset($row[19]) && is_numeric($row[19])) ? floatval($row[19]) : $product->unit_price;
        
        if ($product->purchase_price < $product->unit_price && $product->unit_price > 0) {
            $product->discount = $product->unit_price - $product->purchase_price;
            $product->discount_type = 'amount';
        } else {
            $product->discount = 0;
        }
        
        // Stock
        if (isset($row[17]) && is_numeric($row[17])) {
            $product->current_stock = intval($row[17]);
        } else {
            $product->current_stock = (isset($row[26]) && $row[26] == 'instock') ? 100 : 0;
        }
        
        // Category - simplified logic
        $categoryId = 1; // Default
        $categoryStr = isset($row[49]) ? $row[49] : '';
        
        if (!empty($categoryStr)) {
            // Try to find a matching category
            $categories = explode('|', $categoryStr);
            foreach ($categories as $catPath) {
                // Get the last part of the path
                if (strpos($catPath, '>') !== false) {
                    $parts = explode('>', $catPath);
                    $catName = trim(end($parts));
                } else {
                    $catName = trim($catPath);
                }
                
                // Find in database
                $cat = App\Models\Category::where('name', $catName)
                    ->orWhere('name', 'LIKE', '%' . $catName . '%')
                    ->orderBy('level', 'desc')
                    ->first();
                
                if ($cat) {
                    $categoryId = $cat->id;
                    break;
                }
            }
        }
        
        // Auto-categorize if still default
        if ($categoryId == 1) {
            $nameLower = strtolower($name);
            
            if (strpos($nameLower, 'comic') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%COMIC%')->where('level', '>', 0)->first();
            } elseif (strpos($nameLower, 'magazine') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%MAGAZINE%')->where('level', '>', 0)->first();
            } elseif (strpos($nameLower, 'stamp') !== false || strpos($nameLower, 'postage') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%PHILATELY%')->first();
            } elseif (strpos($nameLower, 'painting') !== false || strpos($nameLower, 'art') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%ART%')->where('level', '>', 0)->first();
            } elseif (strpos($nameLower, 'book') !== false || strpos($nameLower, 'novel') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%book%')->where('level', '>', 0)->first();
            } elseif (strpos($nameLower, 'cassette') !== false || strpos($nameLower, 'audio') !== false) {
                $cat = App\Models\Category::where('name', 'LIKE', '%CASSETTE%')->first();
            }
            
            if (isset($cat) && $cat) {
                $categoryId = $cat->id;
            }
        }
        
        $product->category_id = $categoryId;
        
        // Tags
        $product->tags = isset($row[50]) ? substr($row[50], 0, 500) : '';
        
        // Tax
        $product->tax = (isset($row[31]) && $row[31] == 'taxable') ? 1 : 0;
        $product->tax_type = 'percent';
        
        // Digital
        $product->digital = 0;
        if ((isset($row[15]) && $row[15] == 'yes') || (isset($row[16]) && $row[16] == 'yes')) {
            $product->digital = 1;
        }
        
        // Images
        if (isset($row[41]) && !empty($row[41])) {
            if (preg_match('/https?:\/\/[^\s!]+/', $row[41], $matches)) {
                $product->thumbnail_img = $matches[0];
                $product->photos = $matches[0];
            }
        }
        
        // Weight
        $product->weight = (isset($row[20]) && is_numeric($row[20])) ? floatval($row[20]) : 0;
        
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
        $product->meta_title = substr($product->name, 0, 190);
        
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
        
        // Show first few imports
        if ($imported <= 10) {
            $catName = App\Models\Category::find($product->category_id)->name ?? 'Default';
            echo "✅ #{$imported}: {$product->name} → {$catName} (₹{$product->unit_price})\n";
        }
        
    } catch (\Exception $e) {
        $errors++;
        if ($errors <= 5) {
            echo "❌ Error row {$rowNum}: " . $e->getMessage() . "\n";
        }
    }
}

fclose($handle);

echo "\n=================\n";
echo "✅ IMPORT DONE!\n";
echo "=================\n";
echo "Imported: {$imported}\n";
echo "Skipped: {$skipped}\n";
echo "Errors: {$errors}\n";
echo "Total rows: {$rowNum}\n";

// Show results
echo "\n📊 DATABASE STATS:\n";
$total = App\Models\Product::count();
$published = App\Models\Product::where('published', 1)->count();
$withStock = App\Models\Product::where('current_stock', '>', 0)->count();
$inChild = App\Models\Product::whereHas('category', fn($q) => $q->where('parent_id', '>', 0))->count();

echo "Total products: {$total}\n";
echo "Published: {$published}\n";
echo "With stock: {$withStock}\n";
echo "In child categories: {$inChild}\n";

// Show category distribution
echo "\n📂 TOP CATEGORIES:\n";
$topCats = DB::table('products')
    ->join('categories', 'products.category_id', '=', 'categories.id')
    ->select('categories.name', DB::raw('count(products.id) as cnt'))
    ->groupBy('categories.id', 'categories.name')
    ->orderBy('cnt', 'desc')
    ->limit(10)
    ->get();

foreach ($topCats as $cat) {
    echo "  {$cat->name}: {$cat->cnt}\n";
}

echo "\n✅ All done! Products imported successfully.\n";
?>
