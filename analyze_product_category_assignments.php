<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== ANALYZING PRODUCT-CATEGORY ASSIGNMENTS ===\n\n";

echo "🔍 CURRENT PRODUCT ASSIGNMENT STATUS:\n\n";

// Check how products are currently assigned
$totalProducts = App\Models\Product::count();
echo "Total products in database: {$totalProducts}\n\n";

// Check assignments to parent vs child vs sub-child categories
$parentCategoryAssignments = App\Models\Product::whereHas('category', function($q) {
    $q->where('parent_id', 0);
})->count();

$childCategoryAssignments = App\Models\Product::whereHas('category', function($q) {
    $q->where('parent_id', '>', 0)->where('level', 1);
})->count();

$subChildCategoryAssignments = App\Models\Product::whereHas('category', function($q) {
    $q->where('parent_id', '>', 0)->where('level', 2);
})->count();

echo "📊 ASSIGNMENT BREAKDOWN:\n";
echo "Products assigned to PARENT categories: {$parentCategoryAssignments}\n";
echo "Products assigned to CHILD categories: {$childCategoryAssignments}\n";
echo "Products assigned to SUB-CHILD categories: {$subChildCategoryAssignments}\n\n";

// Check for products assigned to multiple categories (via category_id field vs product_categories table)
echo "📋 MULTIPLE CATEGORY ASSIGNMENTS:\n";

// Laravel typically uses a single category_id field, let's check the structure
$sampleProduct = App\Models\Product::first();
if ($sampleProduct) {
    echo "Sample product category assignment: ";
    if ($sampleProduct->category_id) {
        $category = App\Models\Category::find($sampleProduct->category_id);
        echo $category ? $category->name . " (ID: {$sampleProduct->category_id})" : "Category not found";
        echo "\n";
        
        // Check if there's a product_categories pivot table
        try {
            $pivotCount = DB::table('product_categories')->count();
            echo "Product-categories pivot table exists with {$pivotCount} entries\n";
        } catch (Exception $e) {
            echo "No product_categories pivot table found - using single category_id field\n";
        }
    }
}

echo "\n🏗️ CATEGORY HIERARCHY ANALYSIS:\n\n";

// Analyze specific category hierarchies and their product assignments
$mainCategories = ['ARTWORKS', 'ANTIQUE COMICS', 'ANTIQUE MAGAZINES', 'NOVELS', 'PHILATELY', 'RARE ITEMS'];

foreach ($mainCategories as $categoryName) {
    $parentCategory = App\Models\Category::where('name', $categoryName)->where('parent_id', 0)->first();
    
    if ($parentCategory) {
        $parentProducts = App\Models\Product::where('category_id', $parentCategory->id)->count();
        echo "📁 {$categoryName} (Parent): {$parentProducts} products\n";
        
        $children = App\Utility\CategoryUtility::get_immediate_children($parentCategory->id);
        foreach ($children as $child) {
            $childProducts = App\Models\Product::where('category_id', $child->id)->count();
            echo "   └── {$child->name} (Child): {$childProducts} products\n";
            
            $subChildren = App\Utility\CategoryUtility::get_immediate_children($child->id);
            foreach ($subChildren as $subChild) {
                $subChildProducts = App\Models\Product::where('category_id', $subChild->id)->count();
                echo "       └── {$subChild->name} (Sub-child): {$subChildProducts} products\n";
            }
        }
        echo "\n";
    }
}

echo "🔍 CHECKING ORIGINAL CSV DATA:\n";

// Check the original product.csv for category assignments
$csvFile = 'product.csv';
if (file_exists($csvFile)) {
    $handle = fopen($csvFile, 'r');
    $header = fgetcsv($handle, 0, ",");
    
    // Find the column index for tax:product_cat
    $categoryColumnIndex = array_search('tax:product_cat', $header);
    
    if ($categoryColumnIndex !== false) {
        echo "Found category column at index {$categoryColumnIndex}\n";
        
        // Sample a few rows to see the category assignment pattern
        $sampleCount = 0;
        while (($row = fgetcsv($handle, 0, ",")) !== FALSE && $sampleCount < 10) {
            if (isset($row[$categoryColumnIndex]) && !empty($row[$categoryColumnIndex])) {
                $categoryData = $row[$categoryColumnIndex];
                $productName = isset($row[1]) ? substr($row[1], 0, 50) . '...' : 'Unknown';
                echo "Product: {$productName}\n";
                echo "Categories: {$categoryData}\n\n";
                $sampleCount++;
            }
        }
    }
    fclose($handle);
} else {
    echo "product.csv not found\n";
}

echo "⚠️ ISSUES IDENTIFIED:\n\n";

$issues = [];

if ($parentCategoryAssignments > ($childCategoryAssignments + $subChildCategoryAssignments) * 2) {
    $issues[] = "Too many products assigned to parent categories instead of specific child categories";
}

if ($childCategoryAssignments == 0 && $subChildCategoryAssignments == 0) {
    $issues[] = "No products assigned to child or sub-child categories - all assigned to parents";
}

// Check for logical assignment issues
$artworks = App\Models\Category::where('name', 'ARTWORKS')->first();
if ($artworks) {
    $artworkProducts = App\Models\Product::where('category_id', $artworks->id)->count();
    $artworkChildren = App\Utility\CategoryUtility::get_immediate_children($artworks->id);
    $childrenProductsTotal = 0;
    foreach ($artworkChildren as $child) {
        $childrenProductsTotal += App\Models\Product::where('category_id', $child->id)->count();
    }
    
    if ($artworkProducts > 0 && $childrenProductsTotal == 0 && $artworkChildren->count() > 0) {
        $issues[] = "ARTWORKS has {$artworkProducts} products but its children have 0 - products should be in specific subcategories";
    }
}

if (empty($issues)) {
    echo "✅ No major issues found with product-category assignments\n";
} else {
    foreach ($issues as $issue) {
        echo "❌ {$issue}\n";
    }
}

echo "\n📋 RECOMMENDATIONS:\n";
echo "1. Reassign products from parent categories to appropriate child categories\n";
echo "2. Use the original CSV category data to make precise assignments\n";
echo "3. Ensure products are assigned to the most specific category level\n";
echo "4. Maintain logical hierarchy (products in 'MANOJ COMICS (SMALL)' not 'ANTIQUE COMICS')\n";
echo "5. Consider implementing multiple category support if needed\n";

echo "\n✅ Analysis completed!\n";
?>
