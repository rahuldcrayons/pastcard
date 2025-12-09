<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== PRODUCT ASSIGNMENT PROGRESS CHECK ===\n\n";

// Check current distribution
$parentAssigned = App\Models\Product::join('categories', 'products.category_id', '=', 'categories.id')
    ->where('categories.parent_id', 0)->count();

$childAssigned = App\Models\Product::join('categories', 'products.category_id', '=', 'categories.id')
    ->where('categories.parent_id', '>', 0)->count();

echo "📊 CURRENT DISTRIBUTION:\n";
echo "Products in parent categories: {$parentAssigned}\n";
echo "Products in child categories: {$childAssigned}\n\n";

echo "🔍 DETAILED CATEGORY BREAKDOWN:\n\n";

$testCategories = ['ANTIQUE COMICS', 'ARTWORKS', 'ANTIQUE MAGAZINES', 'NOVELS', 'PHILATELY'];

foreach ($testCategories as $categoryName) {
    $parentCategory = App\Models\Category::where('name', $categoryName)->where('parent_id', 0)->first();
    if ($parentCategory) {
        $parentProducts = App\Models\Product::where('category_id', $parentCategory->id)->count();
        echo "📁 {$categoryName}: {$parentProducts} products (parent level)\n";
        
        $children = App\Models\Category::where('parent_id', $parentCategory->id)->get();
        $totalChildProducts = 0;
        
        foreach ($children as $child) {
            $childProducts = App\Models\Product::where('category_id', $child->id)->count();
            $totalChildProducts += $childProducts;
            if ($childProducts > 0) {
                echo "  ✅ {$child->name}: {$childProducts} products\n";
            }
        }
        
        if ($totalChildProducts > 0) {
            echo "  📈 Total in subcategories: {$totalChildProducts}\n";
        } else {
            echo "  ⚠️ No products in subcategories yet\n";
        }
        echo "\n";
    }
}

$totalProducts = App\Models\Product::count();
$assignedProducts = $parentAssigned + $childAssigned;
$improvementPercentage = $childAssigned > 0 ? round(($childAssigned / $assignedProducts) * 100, 2) : 0;

echo "📈 SUMMARY:\n";
echo "Total products: {$totalProducts}\n";
echo "Products properly assigned: {$assignedProducts}\n";
echo "Improvement: {$improvementPercentage}% now in specific subcategories\n";

if ($childAssigned > 1000) {
    echo "\n✅ SIGNIFICANT PROGRESS MADE!\n";
    echo "Products are being successfully moved to appropriate subcategories.\n";
} else {
    echo "\n⏳ ASSIGNMENT IN PROGRESS...\n";
    echo "The reassignment script is still running.\n";
}

echo "\n🎯 EXPECTED FINAL RESULT:\n";
echo "• Products moved from broad categories (ANTIQUE COMICS) to specific ones (MANOJ COMICS)\n";
echo "• Better product discovery in category navigation\n";
echo "• More accurate product counts in menus\n";
echo "• Improved SEO and user experience\n";
?>
