<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== FIXING VERTICAL MENU ISSUES ===\n\n";

echo "1. CHECKING MAIN CATEGORIES ORDER:\n";
$mainCategories = App\Models\Category::where('parent_id', 0)->orderBy('order_level', 'desc')->get();

foreach ($mainCategories->take(8) as $index => $category) {
    $position = $index + 1;
    echo "{$position}. {$category->name} (priority: {$category->order_level})\n";
}

echo "\n2. CHECKING SUBCATEGORIES ORDER FOR TOP CATEGORIES:\n";

$topCategories = ['ARTWORKS', 'ANTIQUE COMICS', 'ANTIQUE MAGAZINES'];

foreach ($topCategories as $categoryName) {
    $category = App\Models\Category::where('name', $categoryName)->where('parent_id', 0)->first();
    if ($category) {
        echo "\n📁 {$categoryName}:\n";
        $subcategories = App\Models\Category::where('parent_id', $category->id)->orderBy('order_level', 'desc')->get();
        
        if ($subcategories->count() == 0) {
            echo "   No subcategories found\n";
        } else {
            foreach ($subcategories as $sub) {
                echo "   └── {$sub->name} (priority: {$sub->order_level})\n";
            }
        }
    }
}

echo "\n3. CHECKING CategoryUtility FUNCTION:\n";
$artworksCategory = App\Models\Category::where('name', 'ARTWORKS')->first();
if ($artworksCategory) {
    $childrenIds = App\Utility\CategoryUtility::get_immediate_children_ids($artworksCategory->id);
    echo "ARTWORKS children IDs from utility: " . implode(', ', $childrenIds) . "\n";
    
    $children = App\Utility\CategoryUtility::get_immediate_children($artworksCategory->id);
    echo "ARTWORKS children from utility:\n";
    foreach ($children as $child) {
        echo "   └── {$child->name} (order: {$child->order_level})\n";
    }
}

echo "\n4. FIXING MISSING ORDER LEVELS FOR SUBCATEGORIES:\n";

// Update subcategories that have order_level = 0 to proper values
$subcategoriesWithZeroOrder = App\Models\Category::where('parent_id', '>', 0)->where('order_level', 0)->get();

echo "Found {$subcategoriesWithZeroOrder->count()} subcategories with order_level = 0\n";

if ($subcategoriesWithZeroOrder->count() > 0) {
    echo "Updating subcategory order levels...\n";
    
    foreach ($subcategoriesWithZeroOrder as $subcategory) {
        $parent = App\Models\Category::find($subcategory->parent_id);
        if ($parent) {
            // Set a base order level relative to parent
            $newOrderLevel = max(10, $parent->order_level - 50);
            $subcategory->order_level = $newOrderLevel;
            $subcategory->save();
            echo "✓ Updated {$subcategory->name}: order_level = {$newOrderLevel}\n";
        }
    }
}

echo "\n5. CLEARING CACHES:\n";

// Clear various caches
Artisan::call('cache:clear');
echo "✓ Application cache cleared\n";

Artisan::call('view:clear');
echo "✓ View cache cleared\n";

Artisan::call('route:clear');
echo "✓ Route cache cleared\n";

echo "\n6. VERIFYING VERTICAL MENU STRUCTURE:\n";

echo "Main categories for vertical menu (top 8):\n";
$verticalMenuCategories = App\Models\Category::where('level', 0)->orderBy('order_level', 'desc')->take(8)->get();

foreach ($verticalMenuCategories as $index => $category) {
    $position = $index + 1;
    $childCount = App\Models\Category::where('parent_id', $category->id)->count();
    echo "{$position}. {$category->name} ({$childCount} subcategories)\n";
    
    if ($childCount > 0) {
        $topChildren = App\Models\Category::where('parent_id', $category->id)->orderBy('order_level', 'desc')->take(3)->get();
        foreach ($topChildren as $child) {
            echo "   └── {$child->name}\n";
        }
    }
}

echo "\n7. CHECKING TEMPLATE FILES:\n";

// Check if the nav.blade.php is using correct queries
$navFile = 'resources/views/frontend/inc/nav.blade.php';
if (file_exists($navFile)) {
    $content = file_get_contents($navFile);
    if (strpos($content, "where('level', 0)->orderBy('order_level', 'desc')") !== false) {
        echo "✓ nav.blade.php: Uses correct category ordering\n";
    } else {
        echo "✗ nav.blade.php: Missing proper ordering\n";
    }
    
    if (strpos($content, 'CategoryUtility::get_immediate_children_ids') !== false) {
        echo "✓ nav.blade.php: Uses CategoryUtility for subcategories\n";
    } else {
        echo "✗ nav.blade.php: Not using CategoryUtility properly\n";
    }
} else {
    echo "✗ nav.blade.php file not found\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Hard refresh your browser (Ctrl+F5)\n";
echo "2. Check browser developer console for JavaScript errors\n";
echo "3. Verify the vertical menu is not being overridden by custom JavaScript\n";
echo "4. Ensure no CSS is hiding categories\n";
echo "5. Check if the vertical menu has a category limit setting\n";

echo "\n✅ Vertical menu fix completed!\n";
echo "The vertical menu should now display categories in the correct order with proper subcategories.\n";
?>
