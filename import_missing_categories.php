<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

// Missing categories to import
$missingCategories = [
    5531 => ['name' => 'Mobile Phone', 'slug' => 'mobile-phone', 'parent' => 0],
    5534 => ['name' => 'Shoes', 'slug' => 'shoes', 'parent' => 0],
    5535 => ['name' => "Men's Shoes", 'slug' => 'mens-shoes', 'parent' => 0],
    5537 => ['name' => 'Television', 'slug' => 'television', 'parent' => 0],
    5539 => ['name' => 'Refrigerator', 'slug' => 'refrigerator', 'parent' => 0],
    5540 => ['name' => 'Washing Machine', 'slug' => 'washing-machine', 'parent' => 0],
    5552 => ['name' => 'MEHAKTA AANCHAL', 'slug' => 'mehakta-aanchal', 'parent' => 0],
    7674 => ['name' => 'Membership', 'slug' => 'membership', 'parent' => 0],
];

echo "=== IMPORTING MISSING CATEGORIES ===\n\n";

$imported = 0;
foreach($missingCategories as $id => $category) {
    try {
        // Check if category already exists by ID
        $existing = App\Models\Category::find($id);
        if ($existing) {
            echo "Category ID {$id} already exists, skipping...\n";
            continue;
        }

        // Determine level based on parent
        $level = 0;
        if ($category['parent'] != 0) {
            $parent = App\Models\Category::find($category['parent']);
            if ($parent) {
                $level = $parent->level + 1;
            }
        }

        // Create new category
        $newCategory = new App\Models\Category();
        $newCategory->id = $id;
        $newCategory->name = $category['name'];
        $newCategory->slug = $category['slug'];
        $newCategory->parent_id = $category['parent'] == 0 ? null : $category['parent'];
        $newCategory->level = $level;
        $newCategory->order_level = $id; // Use ID as order level
        $newCategory->commission_rate = 0;
        $newCategory->banner = null;
        $newCategory->icon = null;
        $newCategory->featured = 0;
        $newCategory->top = 0;
        $newCategory->digital = 0;
        
        $newCategory->save();
        
        echo "✓ Imported: {$category['name']} (ID: {$id}, Parent: {$category['parent']}, Level: {$level})\n";
        $imported++;
        
    } catch (Exception $e) {
        echo "✗ Error importing {$category['name']}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== IMPORT SUMMARY ===\n";
echo "Categories successfully imported: {$imported}\n";
echo "Total categories now in database: " . App\Models\Category::count() . "\n";

// Update parent-child relationships if needed
echo "\n=== UPDATING PARENT-CHILD RELATIONSHIPS ===\n";
$categories = App\Models\Category::whereNotNull('parent_id')->get();
foreach($categories as $category) {
    $parent = App\Models\Category::find($category->parent_id);
    if ($parent && $category->level <= $parent->level) {
        $category->level = $parent->level + 1;
        $category->save();
        echo "Updated level for {$category->name}: {$category->level}\n";
    }
}
