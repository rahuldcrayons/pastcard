<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== QUICK DATABASE ANALYSIS ===\n\n";

// 1. Products Overview
echo "📦 PRODUCTS:\n";
$totalProducts = App\Models\Product::count();
$publishedProducts = App\Models\Product::where('published', 1)->count();
$unpublishedProducts = App\Models\Product::where('published', 0)->count();

echo "Total: {$totalProducts}\n";
echo "Published: {$publishedProducts}\n";
echo "Unpublished: {$unpublishedProducts}\n\n";

// 2. Category Issues
echo "📂 CATEGORIES:\n";
$parentCategories = App\Models\Category::where('parent_id', 0)->count();
$childCategories = App\Models\Category::where('parent_id', '>', 0)->count();

echo "Parent Categories: {$parentCategories}\n";
echo "Child Categories: {$childCategories}\n";

// Products in parent vs child categories
$productsInParent = App\Models\Product::whereHas('category', function($q) {
    $q->where('parent_id', 0);
})->count();

$productsInChild = App\Models\Product::whereHas('category', function($q) {
    $q->where('parent_id', '>', 0);
})->count();

echo "Products in Parent Categories: {$productsInParent}\n";
echo "Products in Child Categories: {$productsInChild}\n\n";

// 3. Main issues
echo "⚠️ MAIN ISSUES:\n";
$issues = [];

if ($productsInParent > $productsInChild * 2) {
    $issues[] = "Most products ({$productsInParent}) are in parent categories instead of specific child categories";
}

$noCategoryProducts = App\Models\Product::whereNull('category_id')->count();
if ($noCategoryProducts > 0) {
    $issues[] = "{$noCategoryProducts} products have no category";
}

$duplicateSkus = App\Models\Product::selectRaw('sku, COUNT(*) as count')
    ->whereNotNull('sku')
    ->groupBy('sku')
    ->having('count', '>', 1)
    ->count();
if ($duplicateSkus > 0) {
    $issues[] = "{$duplicateSkus} duplicate SKUs found";
}

foreach ($issues as $issue) {
    echo "❌ {$issue}\n";
}

// 4. Sample problematic categories
echo "\n📊 PROBLEMATIC CATEGORY DISTRIBUTION:\n";
$problematicCategories = [
    'ANTIQUE COMICS',
    'ARTWORKS', 
    'ANTIQUE MAGAZINES',
    'NOVELS'
];

foreach ($problematicCategories as $catName) {
    $category = App\Models\Category::where('name', $catName)->where('parent_id', 0)->first();
    if ($category) {
        $parentProducts = App\Models\Product::where('category_id', $category->id)->count();
        $children = App\Models\Category::where('parent_id', $category->id)->get();
        $childProducts = 0;
        foreach ($children as $child) {
            $childProducts += App\Models\Product::where('category_id', $child->id)->count();
        }
        echo "{$catName}: {$parentProducts} in parent, {$childProducts} in children\n";
    }
}

echo "\n🔄 REQUIRED ACTIONS:\n";
echo "1. Clear all current products (poorly imported)\n";
echo "2. Properly map WooCommerce fields to Laravel\n";
echo "3. Import with correct category hierarchy\n";
echo "4. Fix published field mapping (boolean)\n";

echo "\nAnalysis complete!\n";
?>
