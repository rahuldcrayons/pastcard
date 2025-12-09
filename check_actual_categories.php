<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CATEGORIES WITH ACTUAL PRODUCTS ===\n\n";

// Get categories that actually have products
$categoriesWithProducts = DB::table('products')
    ->join('categories', 'products.category_id', '=', 'categories.id')
    ->select('categories.id', 'categories.name', 'categories.level', 'categories.parent_id', 
             DB::raw('COUNT(products.id) as total_count'),
             DB::raw('SUM(CASE WHEN products.published = 1 THEN 1 ELSE 0 END) as published_count'))
    ->groupBy('categories.id', 'categories.name', 'categories.level', 'categories.parent_id')
    ->orderBy('total_count', 'desc')
    ->limit(20)
    ->get();

echo "TOP 20 CATEGORIES WITH PRODUCTS:\n";
echo str_repeat("=", 70) . "\n";
echo str_pad("Category Name", 40) . str_pad("Level", 10) . str_pad("Total", 10) . str_pad("Published", 10) . "\n";
echo str_repeat("-", 70) . "\n";

foreach ($categoriesWithProducts as $cat) {
    $levelText = $cat->level == 0 ? 'Parent' : ($cat->level == 1 ? 'Child' : 'SubChild');
    echo str_pad(substr($cat->name, 0, 38), 40) . 
         str_pad($levelText, 10) . 
         str_pad($cat->total_count, 10) . 
         str_pad($cat->published_count, 10) . "\n";
}

echo "\n";

// Check if there are parent categories without products
$parentCategoriesNoProducts = App\Models\Category::where('level', 0)
    ->whereNotIn('id', function($query) {
        $query->select('category_id')->from('products');
    })
    ->pluck('name')
    ->toArray();

if (count($parentCategoriesNoProducts) > 0) {
    echo "\n⚠️  PARENT CATEGORIES WITHOUT PRODUCTS:\n";
    echo str_repeat("-", 50) . "\n";
    foreach ($parentCategoriesNoProducts as $name) {
        echo "- {$name}\n";
    }
}

echo "\n✅ Analysis complete!\n";
echo "\n💡 NOTES:\n";
echo "- Menu counts now show only PUBLISHED products\n";
echo "- Categories with 0 products will show (0) in the menu\n";
echo "- Total imported products: " . App\Models\Product::count() . "\n";
echo "- Published products: " . App\Models\Product::where('published', 1)->count() . "\n";
?>
