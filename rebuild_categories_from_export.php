<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== REBUILDING CATEGORIES FROM WORDPRESS EXPORT ===\n\n";

$csvFile = 'product_categories_export_.csv';
$handle = fopen($csvFile, 'r');

if (!$handle) {
    die("Could not open category export CSV file\n");
}

// Read header
$header = fgetcsv($handle, 0, ",");
echo "CSV Headers: " . implode(', ', $header) . "\n\n";

$categories = [];
$createdCategories = 0;
$updatedCategories = 0;

// Read all categories from CSV
while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
    if (count($row) >= 6) {
        $categoryData = [
            'term_id' => $row[0],
            'name' => $row[1], 
            'slug' => $row[2],
            'description' => $row[3],
            'display_type' => $row[4],
            'parent' => $row[5],
            'thumbnail' => isset($row[6]) ? $row[6] : ''
        ];
        $categories[] = $categoryData;
    }
}

fclose($handle);

echo "Read " . count($categories) . " categories from CSV\n\n";

// Function to create slug
function createSlug($name) {
    return Str::slug(strtolower($name));
}

// Step 1: Create/Update all parent categories first
echo "=== CREATING/UPDATING PARENT CATEGORIES ===\n";

foreach ($categories as $categoryData) {
    if ($categoryData['parent'] == '0') { // Parent category
        
        // Skip unwanted categories
        $unwantedCategories = ['Mobile Phone', 'Shoes', "Men's Shoes", 'Television', 'Refrigerator', 'Washing Machine', 'MEHAKTA AANCHAL', 'Membership'];
        if (in_array($categoryData['name'], $unwantedCategories)) {
            echo "Skipping unwanted category: {$categoryData['name']}\n";
            continue;
        }
        
        echo "Processing parent category: {$categoryData['name']}\n";
        
        // Find existing category by original term_id or name
        $existingCategory = App\Models\Category::where('name', $categoryData['name'])->where('parent_id', 0)->first();
        
        if (!$existingCategory) {
            // Create new category
            $category = new App\Models\Category();
            $category->name = $categoryData['name'];
            $category->slug = !empty($categoryData['slug']) ? $categoryData['slug'] : createSlug($categoryData['name']);
            $category->parent_id = 0;
            $category->level = 0;
            $category->digital = 0;
            
            // Set order_level based on category importance (from your desired order)
            $orderLevels = [
                'ARTWORKS' => 800,
                'PHILATELY' => 700,
                'ANTIQUE COMICS' => 600,
                'ANTIQUE MAGAZINES' => 500,
                'NOVELS' => 400,
                'RARE ITEMS' => 300,
                'TOYS' => 200,
                'CASSETTES / CD-DVD / VINYL RECODER' => 190,
                'SPORTS COLLECTIBLES' => 180,
                'ANTIQUE PAINTINGS' => 170,
                'PAINTING' => 160,
                'CHRISTMAS SALE' => 50,
                'THE 50 STORE' => 40,
                'DEAL OF THE DAY' => 30,
                'STOCK CLEARANCE SALE' => 20
            ];
            
            $category->order_level = $orderLevels[$categoryData['name']] ?? 10;
            $category->save();
            
            // Create translation
            App\Models\CategoryTranslation::create([
                'lang' => env('DEFAULT_LANGUAGE', 'en'),
                'category_id' => $category->id,
                'name' => $categoryData['name']
            ]);
            
            echo "  ✓ Created: {$categoryData['name']} (order: {$category->order_level})\n";
            $createdCategories++;
        } else {
            // Update existing category order level
            $orderLevels = [
                'ARTWORKS' => 800,
                'PHILATELY' => 700,
                'ANTIQUE COMICS' => 600,
                'ANTIQUE MAGAZINES' => 500,
                'NOVELS' => 400,
                'RARE ITEMS' => 300,
                'TOYS' => 200,
                'CASSETTES / CD-DVD / VINYL RECODER' => 190,
                'SPORTS COLLECTIBLES' => 180,
                'ANTIQUE PAINTINGS' => 170,
                'PAINTING' => 160,
                'CHRISTMAS SALE' => 50,
                'THE 50 STORE' => 40,
                'DEAL OF THE DAY' => 30,
                'STOCK CLEARANCE SALE' => 20
            ];
            
            if (isset($orderLevels[$categoryData['name']])) {
                $existingCategory->order_level = $orderLevels[$categoryData['name']];
                $existingCategory->save();
                echo "  ✓ Updated order: {$categoryData['name']} (order: {$existingCategory->order_level})\n";
                $updatedCategories++;
            }
        }
    }
}

// Step 2: Create/Update all child categories  
echo "\n=== CREATING/UPDATING CHILD CATEGORIES ===\n";

foreach ($categories as $categoryData) {
    if ($categoryData['parent'] != '0') { // Child category
        
        echo "Processing child category: {$categoryData['name']} (parent: {$categoryData['parent']})\n";
        
        // Find parent category by name (search through CSV data first)
        $parentCategoryData = null;
        foreach ($categories as $catData) {
            if ($catData['term_id'] == $categoryData['parent']) {
                $parentCategoryData = $catData;
                break;
            }
        }
        
        if (!$parentCategoryData) {
            echo "  ✗ Parent not found for: {$categoryData['name']}\n";
            continue;
        }
        
        // Skip if parent is unwanted
        $unwantedCategories = ['Mobile Phone', 'Shoes', "Men's Shoes", 'Television', 'Refrigerator', 'Washing Machine', 'MEHAKTA AANCHAL', 'Membership'];
        if (in_array($parentCategoryData['name'], $unwantedCategories)) {
            echo "  Skipping child of unwanted parent: {$categoryData['name']}\n";
            continue;
        }
        
        // Find parent category in database
        $parentCategory = App\Models\Category::where('name', $parentCategoryData['name'])->where('parent_id', 0)->first();
        
        if (!$parentCategory) {
            echo "  ✗ Parent category not found in database: {$parentCategoryData['name']}\n";
            continue;
        }
        
        // Find existing child category
        $existingChild = App\Models\Category::where('name', $categoryData['name'])->where('parent_id', $parentCategory->id)->first();
        
        if (!$existingChild) {
            // Create new child category
            $childCategory = new App\Models\Category();
            $childCategory->name = $categoryData['name'];
            $childCategory->slug = !empty($categoryData['slug']) ? $categoryData['slug'] : createSlug($categoryData['name']);
            $childCategory->parent_id = $parentCategory->id;
            $childCategory->level = 1;
            $childCategory->order_level = max(10, $parentCategory->order_level - 100); // Lower than parent
            $childCategory->digital = 0;
            $childCategory->save();
            
            // Create translation
            App\Models\CategoryTranslation::create([
                'lang' => env('DEFAULT_LANGUAGE', 'en'),
                'category_id' => $childCategory->id,
                'name' => $categoryData['name']
            ]);
            
            echo "  ✓ Created child: {$categoryData['name']} under {$parentCategory->name}\n";
            $createdCategories++;
        } else {
            echo "  ✓ Child exists: {$categoryData['name']}\n";
        }
    }
}

echo "\n=== REMOVING UNWANTED CATEGORIES ===\n";

$unwantedCategories = ['Mobile Phone', 'Shoes', "Men's Shoes", 'Television', 'Refrigerator', 'Washing Machine', 'MEHAKTA AANCHAL', 'Membership'];

foreach ($unwantedCategories as $unwantedName) {
    $unwantedCategory = App\Models\Category::where('name', $unwantedName)->first();
    if ($unwantedCategory) {
        $productCount = App\Models\Product::where('category_id', $unwantedCategory->id)->count();
        
        if ($productCount == 0) {
            App\Models\CategoryTranslation::where('category_id', $unwantedCategory->id)->delete();
            $unwantedCategory->delete();
            echo "✓ Removed empty unwanted category: {$unwantedName}\n";
        } else {
            $unwantedCategory->order_level = 1; // Very low priority
            $unwantedCategory->save();
            echo "✓ Set low priority for category with products: {$unwantedName}\n";
        }
    }
}

echo "\n=== FINAL CATEGORY STRUCTURE ===\n";

$finalCategories = App\Models\Category::where('parent_id', 0)->orderBy('order_level', 'desc')->get();

foreach ($finalCategories as $index => $category) {
    $position = $index + 1;
    $productCount = App\Models\Product::where('category_id', $category->id)->count();
    $childCount = App\Models\Category::where('parent_id', $category->id)->count();
    
    echo "{$position}. {$category->name} (priority: {$category->order_level}, products: {$productCount}, children: {$childCount})\n";
    
    // Show children for main categories
    if ($childCount > 0 && $position <= 8) {
        $children = App\Models\Category::where('parent_id', $category->id)->orderBy('order_level', 'desc')->take(3)->get();
        foreach ($children as $child) {
            $childProductCount = App\Models\Product::where('category_id', $child->id)->count();
            echo "   └── {$child->name} ({$childProductCount} products)\n";
        }
        echo "\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Categories created: {$createdCategories}\n";
echo "Categories updated: {$updatedCategories}\n";
echo "Categories now follow the exact WordPress export structure!\n";
echo "\n✅ Category rebuild completed successfully!\n";
?>
