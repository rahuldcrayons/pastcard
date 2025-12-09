<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== REMOVING PROBLEMATIC CATEGORIES ===\n\n";

// Find categories that might be causing issues
$problematicCategories = [
    'Membership',
    'membership', 
    'MEHAKTA AANCHAL' // This also appeared at top with high priority
];

foreach ($problematicCategories as $categoryName) {
    $category = App\Models\Category::where('name', $categoryName)->first();
    if ($category) {
        echo "Found problematic category: {$category->name} (order_level: {$category->order_level})\n";
        
        // Set very low priority or delete if no products
        $productCount = App\Models\Product::where('category_id', $category->id)->count();
        echo "Products in this category: {$productCount}\n";
        
        if ($productCount == 0) {
            echo "Deleting empty category: {$category->name}\n";
            App\Models\CategoryTranslation::where('category_id', $category->id)->delete();
            $category->delete();
        } else {
            echo "Setting low priority for category with products: {$category->name}\n";
            $category->order_level = 5; // Very low priority
            $category->save();
        }
        echo "\n";
    }
}

// Also check for any categories with extremely high order_level that shouldn't be there
$highPriorityCategories = App\Models\Category::where('parent_id', 0)->where('order_level', '>', 1000)->get();

foreach ($highPriorityCategories as $category) {
    echo "Found category with very high priority: {$category->name} (order_level: {$category->order_level})\n";
    $productCount = App\Models\Product::where('category_id', $category->id)->count();
    echo "Products: {$productCount}\n";
    
    if ($productCount == 0) {
        echo "Deleting: {$category->name}\n";
        App\Models\CategoryTranslation::where('category_id', $category->id)->delete();
        $category->delete();
    } else {
        echo "Setting normal priority: {$category->name}\n";
        $category->order_level = 50; // Normal priority
        $category->save();
    }
    echo "\n";
}

echo "=== CURRENT TOP 8 CATEGORIES ===\n";
$topCategories = App\Models\Category::where('parent_id', 0)->orderBy('order_level', 'desc')->take(8)->get();

foreach ($topCategories as $index => $category) {
    $position = $index + 1;
    $productCount = App\Models\Product::where('category_id', $category->id)->count();
    echo "{$position}. {$category->name} (priority: {$category->order_level}, products: {$productCount})\n";
}

echo "\n✅ Cleanup completed!\n";
?>
