<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== CURRENT CATEGORY STRUCTURE ===\n\n";

$mainCategories = App\Models\Category::where('parent_id', 0)->orderBy('name')->get();

foreach ($mainCategories as $main) {
    $productCount = App\Models\Product::where('category_id', $main->id)->count();
    echo "📁 {$main->name} (ID: {$main->id}) - {$productCount} products\n";
    
    $subcategories = App\Models\Category::where('parent_id', $main->id)->orderBy('name')->get();
    if ($subcategories->count() > 0) {
        foreach ($subcategories as $sub) {
            $subProductCount = App\Models\Product::where('category_id', $sub->id)->count();
            echo "   └── {$sub->name} (ID: {$sub->id}) - {$subProductCount} products\n";
        }
    }
    echo "\n";
}

echo "=== SUMMARY ===\n";
echo "Total categories: " . App\Models\Category::count() . "\n";
echo "Main categories: " . App\Models\Category::where('parent_id', 0)->count() . "\n";
echo "Subcategories: " . App\Models\Category::where('parent_id', '>', 0)->count() . "\n";
echo "Total products: " . App\Models\Product::count() . "\n";
?>
