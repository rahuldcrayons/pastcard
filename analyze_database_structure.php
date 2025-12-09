<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== COMPREHENSIVE DATABASE ANALYSIS ===\n\n";

echo "📊 1. PRODUCT TABLES ANALYSIS:\n";
echo str_repeat("=", 50) . "\n";

// Analyze products table
if (Schema::hasTable('products')) {
    $productColumns = Schema::getColumnListing('products');
    $productCount = DB::table('products')->count();
    $sampleProduct = DB::table('products')->first();
    
    echo "📦 PRODUCTS TABLE:\n";
    echo "   Total Records: {$productCount}\n";
    echo "   Columns: " . implode(', ', $productColumns) . "\n";
    
    if ($sampleProduct) {
        echo "\n   Sample Product Data:\n";
        foreach ($sampleProduct as $key => $value) {
            if (!is_null($value) && $value !== '') {
                $displayValue = is_string($value) ? substr($value, 0, 50) : $value;
                echo "      {$key}: {$displayValue}\n";
            }
        }
    }
}

// Check for product-related tables
$productRelatedTables = [
    'product_stocks',
    'product_taxes', 
    'product_translations',
    'product_categories',
    'product_attributes',
    'attribute_values',
    'product_variations',
    'product_images'
];

echo "\n📋 PRODUCT RELATED TABLES:\n";
foreach ($productRelatedTables as $table) {
    if (Schema::hasTable($table)) {
        $count = DB::table($table)->count();
        $columns = Schema::getColumnListing($table);
        echo "   ✓ {$table}: {$count} records\n";
        echo "     Columns: " . implode(', ', array_slice($columns, 0, 5)) . "...\n";
    } else {
        echo "   ✗ {$table}: NOT FOUND\n";
    }
}

echo "\n📂 2. CATEGORY STRUCTURE ANALYSIS:\n";
echo str_repeat("=", 50) . "\n";

// Analyze categories
$categories = DB::table('categories')->orderBy('parent_id')->get();
$parentCategories = $categories->where('parent_id', 0);
$childCategories = $categories->where('parent_id', '>', 0);

echo "   Total Categories: {$categories->count()}\n";
echo "   Parent Categories: {$parentCategories->count()}\n";
echo "   Child Categories: {$childCategories->count()}\n";

// Check for orphaned categories
$orphanedCategories = [];
foreach ($childCategories as $child) {
    if (!$categories->contains('id', $child->parent_id)) {
        $orphanedCategories[] = $child;
    }
}
echo "   Orphaned Categories: " . count($orphanedCategories) . "\n";

// Product-Category distribution
echo "\n   PRODUCT-CATEGORY DISTRIBUTION:\n";
$categoryProductCounts = DB::table('products')
    ->select('category_id', DB::raw('count(*) as product_count'))
    ->groupBy('category_id')
    ->orderBy('product_count', 'desc')
    ->limit(10)
    ->get();

foreach ($categoryProductCounts as $cat) {
    $category = DB::table('categories')->find($cat->category_id);
    if ($category) {
        $parent = $category->parent_id > 0 ? " (Child)" : " (Parent)";
        echo "      {$category->name}{$parent}: {$cat->product_count} products\n";
    }
}

echo "\n🏷️ 3. ATTRIBUTE SYSTEM ANALYSIS:\n";
echo str_repeat("=", 50) . "\n";

if (Schema::hasTable('attributes')) {
    $attributes = DB::table('attributes')->get();
    echo "   Total Attributes: {$attributes->count()}\n";
    
    foreach ($attributes->take(5) as $attr) {
        echo "      - {$attr->name}\n";
        if (Schema::hasTable('attribute_values')) {
            $values = DB::table('attribute_values')
                ->where('attribute_id', $attr->id)
                ->count();
            echo "        Values: {$values}\n";
        }
    }
} else {
    echo "   ✗ Attributes table not found\n";
}

echo "\n💰 4. TAX & PRICING ANALYSIS:\n";
echo str_repeat("=", 50) . "\n";

if (Schema::hasTable('taxes')) {
    $taxes = DB::table('taxes')->get();
    echo "   Tax Categories: {$taxes->count()}\n";
    foreach ($taxes as $tax) {
        echo "      - {$tax->name}: {$tax->tax}%\n";
    }
}

// Check product prices
$priceAnalysis = DB::table('products')
    ->select(
        DB::raw('MIN(unit_price) as min_price'),
        DB::raw('MAX(unit_price) as max_price'),
        DB::raw('AVG(unit_price) as avg_price'),
        DB::raw('COUNT(CASE WHEN discount > 0 THEN 1 END) as discounted_products')
    )
    ->first();

echo "\n   PRICING STATISTICS:\n";
echo "      Min Price: {$priceAnalysis->min_price}\n";
echo "      Max Price: {$priceAnalysis->max_price}\n";
echo "      Avg Price: " . round($priceAnalysis->avg_price, 2) . "\n";
echo "      Products with Discount: {$priceAnalysis->discounted_products}\n";

echo "\n📦 5. STOCK MANAGEMENT:\n";
echo str_repeat("=", 50) . "\n";

if (Schema::hasTable('product_stocks')) {
    $stockAnalysis = DB::table('product_stocks')
        ->select(
            DB::raw('SUM(qty) as total_stock'),
            DB::raw('COUNT(*) as stock_entries'),
            DB::raw('COUNT(DISTINCT product_id) as products_with_stock')
        )
        ->first();
    
    echo "   Total Stock Entries: {$stockAnalysis->stock_entries}\n";
    echo "   Products with Stock: {$stockAnalysis->products_with_stock}\n";
    echo "   Total Quantity: {$stockAnalysis->total_stock}\n";
} else {
    echo "   ✗ Product stocks table not found\n";
}

echo "\n🔍 6. WORDPRESS/WOOCOMMERCE FIELD MAPPING:\n";
echo str_repeat("=", 50) . "\n";

// Check CSV for WooCommerce fields
$csvFile = 'product.csv';
if (file_exists($csvFile)) {
    $handle = fopen($csvFile, 'r');
    $header = fgetcsv($handle, 0, ",");
    fclose($handle);
    
    echo "   WooCommerce CSV Fields Found:\n";
    $wooFields = [
        'sku' => 'sku',
        'name' => 'name', 
        'published' => 'published',
        'visibility' => 'digital',
        'short_description' => 'description',
        'description' => 'description',
        'tax_status' => 'tax',
        'tax_class' => 'tax',
        'in_stock' => 'current_stock',
        'stock' => 'current_stock',
        'weight' => 'weight',
        'regular_price' => 'unit_price',
        'sale_price' => 'purchase_price',
        'categories' => 'category_id',
        'tags' => 'tags',
        'images' => 'photos',
        'attributes' => 'attributes'
    ];
    
    foreach ($header as $field) {
        $laravelField = isset($wooFields[$field]) ? $wooFields[$field] : 'UNMAPPED';
        echo "      {$field} => {$laravelField}\n";
    }
}

echo "\n⚠️ 7. DATA QUALITY ISSUES:\n";
echo str_repeat("=", 50) . "\n";

// Check for issues
$issues = [];

// Products without categories
$noCategoryProducts = DB::table('products')->whereNull('category_id')->count();
if ($noCategoryProducts > 0) {
    $issues[] = "{$noCategoryProducts} products without categories";
}

// Products without SKU
$noSkuProducts = DB::table('products')->whereNull('sku')->orWhere('sku', '')->count();
if ($noSkuProducts > 0) {
    $issues[] = "{$noSkuProducts} products without SKU";
}

// Products without images
$noImageProducts = DB::table('products')->whereNull('thumbnail_img')->count();
if ($noImageProducts > 0) {
    $issues[] = "{$noImageProducts} products without images";
}

// Duplicate SKUs
$duplicateSkus = DB::table('products')
    ->select('sku', DB::raw('count(*) as count'))
    ->whereNotNull('sku')
    ->groupBy('sku')
    ->having('count', '>', 1)
    ->count();
if ($duplicateSkus > 0) {
    $issues[] = "{$duplicateSkus} duplicate SKUs found";
}

if (empty($issues)) {
    echo "   ✅ No major data quality issues found\n";
} else {
    foreach ($issues as $issue) {
        echo "   ❌ {$issue}\n";
    }
}

echo "\n📝 8. RECOMMENDATIONS:\n";
echo str_repeat("=", 50) . "\n";
echo "   1. CLEAR existing products and reimport with proper mapping\n";
echo "   2. Map WooCommerce 'published' field to Laravel 'published' (boolean)\n";
echo "   3. Map 'tax:product_cat' to proper category hierarchy\n";
echo "   4. Convert 'in_stock' status to boolean field\n";
echo "   5. Map product variations properly\n";
echo "   6. Import product images and galleries\n";
echo "   7. Set up proper attribute system for variations\n";
echo "   8. Map shipping classes and tax classes\n";

echo "\n✅ Database analysis completed!\n";
?>
