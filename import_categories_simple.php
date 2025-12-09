<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

// Get existing category to see what fields are available
$existingCategory = App\Models\Category::first();
echo "Sample category fields:\n";
if ($existingCategory) {
    foreach($existingCategory->toArray() as $field => $value) {
        echo "- {$field}\n";
    }
}

echo "\n=== IMPORTING MISSING CATEGORIES ===\n\n";

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

        // Create new category with only essential fields
        $newCategory = new App\Models\Category();
        $newCategory->id = $id;
        $newCategory->name = $category['name'];
        $newCategory->slug = $category['slug'];
        $newCategory->parent_id = $category['parent'] == 0 ? null : $category['parent'];
        $newCategory->level = $level;
        $newCategory->order_level = $id; // Use ID as order level
        
        // Try to set other fields if they exist
        if (method_exists($newCategory, 'setAttribute')) {
            try { $newCategory->banner = null; } catch(Exception $e) {}
            try { $newCategory->icon = null; } catch(Exception $e) {}
            try { $newCategory->featured = 0; } catch(Exception $e) {}
            try { $newCategory->top = 0; } catch(Exception $e) {}
            try { $newCategory->digital = 0; } catch(Exception $e) {}
        }
        
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
