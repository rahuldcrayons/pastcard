<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== FIXING CATEGORY STRUCTURE FROM CSV DATA ===\n\n";

$csvFile = 'product.csv';
$handle = fopen($csvFile, 'r');

if (!$handle) {
    die("Could not open CSV file\n");
}

// Read and clean header
$headerLine = fgets($handle);
$headerLine = preg_replace('/^\xEF\xBB\xBF/', '', $headerLine);
$header = str_getcsv($headerLine, ",");

$categoryColumnIndex = array_search('tax:product_cat', $header);
$idColumnIndex = array_search('ID', $header);

if ($categoryColumnIndex === false || $idColumnIndex === false) {
    die("Required columns not found\n");
}

// Build complete category structure from CSV
$categoryHierarchies = [];
$productCategories = []; // product_id => [categories]

echo "Processing CSV to build category structure...\n";

$processedProducts = 0;
while (($row = fgetcsv($handle, 0, ",")) !== FALSE && $processedProducts < 10000) {
    if (count($row) <= max($categoryColumnIndex, $idColumnIndex)) {
        continue;
    }
    
    $productId = $row[$idColumnIndex];
    $categoryData = $row[$categoryColumnIndex];
    
    if (empty($categoryData) || empty($productId)) {
        continue;
    }
    
    $processedProducts++;
    
    // Parse category hierarchies for this product
    $productCategoryAssignments = [];
    $categoryGroups = explode('|', $categoryData);
    
    foreach ($categoryGroups as $categoryGroup) {
        $categoryGroup = trim($categoryGroup);
        if (empty($categoryGroup)) continue;
        
        if (strpos($categoryGroup, '>') !== false) {
            // Hierarchy like "ANTIQUE COMICS > RAJ COMICS"
            $hierarchy = array_map('trim', explode('>', $categoryGroup));
            $categoryHierarchies[] = $hierarchy;
            
            // Product belongs to the deepest category in hierarchy
            $deepestCategory = end($hierarchy);
            $productCategoryAssignments[] = $deepestCategory;
        } else {
            // Single category
            $productCategoryAssignments[] = $categoryGroup;
        }
    }
    
    $productCategories[$productId] = $productCategoryAssignments;
    
    if ($processedProducts % 1000 == 0) {
        echo "Processed {$processedProducts} products...\n";
    }
}

fclose($handle);

echo "Processed {$processedProducts} products\n\n";

// Build parent-child relationships
$parentChildMap = [];
foreach ($categoryHierarchies as $hierarchy) {
    for ($i = 0; $i < count($hierarchy) - 1; $i++) {
        $parent = trim($hierarchy[$i]);
        $child = trim($hierarchy[$i + 1]);
        
        if (!empty($parent) && !empty($child)) {
            $parentChildMap[$parent][$child] = true;
        }
    }
}

echo "=== CREATING/UPDATING CATEGORY STRUCTURE ===\n";

// Function to create slug
function createSlug($name) {
    return \Str::slug(strtolower($name));
}

// Function to find or create category
function findOrCreateCategory($name, $parentId = 0) {
    $category = App\Models\Category::where('name', $name)->where('parent_id', $parentId)->first();
    
    if (!$category) {
        $category = new App\Models\Category();
        $category->name = $name;
        $category->slug = createSlug($name);
        $category->parent_id = $parentId;
        $category->level = $parentId > 0 ? 1 : 0;
        $category->order_level = 0;
        $category->digital = 0;
        $category->save();
        
        // Create translation
        App\Models\CategoryTranslation::create([
            'lang' => env('DEFAULT_LANGUAGE', 'en'),
            'category_id' => $category->id,
            'name' => $name
        ]);
        
        echo "  ✓ Created category: {$name} (ID: {$category->id})\n";
    }
    
    return $category;
}

$createdCategories = 0;

// First create all parent categories
foreach ($parentChildMap as $parentName => $children) {
    findOrCreateCategory($parentName);
}

// Then create child categories with proper parent relationships
foreach ($parentChildMap as $parentName => $children) {
    $parentCategory = App\Models\Category::where('name', $parentName)->where('parent_id', 0)->first();
    
    if ($parentCategory) {
        foreach ($children as $childName => $dummy) {
            findOrCreateCategory($childName, $parentCategory->id);
        }
    }
}

echo "\n=== REASSIGNING PRODUCTS TO CORRECT CATEGORIES ===\n";

$productUpdates = 0;
$multiCategoryProducts = 0;

foreach ($productCategories as $productId => $categories) {
    $product = App\Models\Product::find($productId);
    
    if (!$product) {
        continue;
    }
    
    if (count($categories) > 1) {
        $multiCategoryProducts++;
    }
    
    // Assign to the first/primary category
    $primaryCategoryName = $categories[0];
    $primaryCategory = App\Models\Category::where('name', $primaryCategoryName)->first();
    
    if ($primaryCategory && $product->category_id != $primaryCategory->id) {
        $product->category_id = $primaryCategory->id;
        $product->save();
        $productUpdates++;
    }
    
    if ($productUpdates % 500 == 0 && $productUpdates > 0) {
        echo "Updated {$productUpdates} products...\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Products updated: {$productUpdates}\n";
echo "Products with multiple categories: {$multiCategoryProducts}\n";
echo "Total categories in DB: " . App\Models\Category::count() . "\n";

echo "\n=== FINAL CATEGORY STRUCTURE ===\n";
$mainCategories = App\Models\Category::where('parent_id', 0)->orderBy('name')->get();

foreach ($mainCategories as $main) {
    $productCount = App\Models\Product::where('category_id', $main->id)->count();
    echo "📁 {$main->name} - {$productCount} products\n";
    
    $subcategories = App\Models\Category::where('parent_id', $main->id)->orderBy('name')->get();
    foreach ($subcategories as $sub) {
        $subProductCount = App\Models\Product::where('category_id', $sub->id)->count();
        echo "   └── {$sub->name} - {$subProductCount} products\n";
    }
    echo "\n";
}

echo "✅ Category structure updated based on CSV data!\n";
?>
