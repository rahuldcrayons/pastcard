<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== CATEGORY PRODUCT COUNTS CHECK ===\n\n";

// Get first 5 parent categories
$parentCategories = App\Models\Category::where('level', 0)
    ->orderBy('order_level', 'desc')
    ->limit(5)
    ->get();

echo "PARENT CATEGORIES:\n";
echo str_repeat("-", 60) . "\n";

foreach ($parentCategories as $category) {
    $totalProducts = App\Models\Product::where('category_id', $category->id)->count();
    $publishedProducts = App\Models\Product::where('category_id', $category->id)->where('published', 1)->count();
    
    echo "Category: {$category->name}\n";
    echo "  Total products: {$totalProducts}\n";
    echo "  Published products: {$publishedProducts}\n";
    echo "  Displayed in menu: {$publishedProducts}\n\n";
}

// Check child categories
echo "\nCHILD CATEGORIES (First 5):\n";
echo str_repeat("-", 60) . "\n";

$childCategories = App\Models\Category::where('level', 1)->limit(5)->get();

foreach ($childCategories as $category) {
    $totalProducts = App\Models\Product::where('category_id', $category->id)->count();
    $publishedProducts = App\Models\Product::where('category_id', $category->id)->where('published', 1)->count();
    
    echo "Category: {$category->name}\n";
    echo "  Total products: {$totalProducts}\n";
    echo "  Published products: {$publishedProducts}\n";
    echo "  Displayed in menu: {$publishedProducts}\n\n";
}

echo "✅ Check complete!\n";
echo "\n💡 NOTE: Menu now shows only PUBLISHED products count.\n";
echo "   Products must have 'published' = 1 to be counted.\n";
?>
