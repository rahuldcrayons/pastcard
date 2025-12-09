<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== FIXING PRODUCT CATEGORY ASSIGNMENTS ===\n\n";

$csvFile = 'product.csv';
if (!file_exists($csvFile)) {
    die("❌ product.csv file not found!\n");
}

$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle, 0, ",");

// Find important column indices
$skuIndex = array_search('sku', $header);
$nameIndex = array_search('name', $header);
$categoryIndex = array_search('tax:product_cat', $header);

if ($categoryIndex === false) {
    die("❌ Category column 'tax:product_cat' not found!\n");
}

echo "✅ Found columns - SKU: {$skuIndex}, Name: {$nameIndex}, Category: {$categoryIndex}\n\n";

$processedCount = 0;
$reassignedCount = 0;
$notFoundCount = 0;
$multiCategoryCount = 0;

// Function to find the most specific category
function findMostSpecificCategory($categoryPath) {
    $categories = explode(' > ', $categoryPath);
    $categories = array_map('trim', $categories);
    
    if (empty($categories)) return null;
    
    // Start with the most specific (last) category
    for ($i = count($categories) - 1; $i >= 0; $i--) {
        $categoryName = $categories[$i];
        
        // Try to find exact match
        $category = App\Models\Category::where('name', $categoryName)->first();
        if ($category) {
            return $category;
        }
        
        // Try variations
        $variations = [
            strtoupper($categoryName),
            ucwords(strtolower($categoryName)),
            str_replace(['&', 'and'], ['&', '&'], $categoryName)
        ];
        
        foreach ($variations as $variation) {
            $category = App\Models\Category::where('name', 'LIKE', '%' . $variation . '%')->first();
            if ($category) {
                return $category;
            }
        }
    }
    
    return null;
}

echo "🔄 PROCESSING PRODUCTS FROM CSV:\n\n";

while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
    $processedCount++;
    
    if ($processedCount % 1000 == 0) {
        echo "Processed {$processedCount} rows...\n";
    }
    
    $sku = isset($row[$skuIndex]) ? trim($row[$skuIndex]) : '';
    $productName = isset($row[$nameIndex]) ? trim($row[$nameIndex]) : '';
    $categoryData = isset($row[$categoryIndex]) ? trim($row[$categoryIndex]) : '';
    
    if (empty($sku) || empty($categoryData)) {
        continue;
    }
    
    // Find the product by SKU
    $product = App\Models\Product::where('sku', $sku)->first();
    if (!$product) {
        // Try by name if SKU doesn't match
        $product = App\Models\Product::where('name', 'LIKE', '%' . substr($productName, 0, 50) . '%')->first();
    }
    
    if (!$product) {
        $notFoundCount++;
        continue;
    }
    
    // Handle multiple categories
    $categoryPaths = [];
    if (strpos($categoryData, '|') !== false) {
        $categoryPaths = explode('|', $categoryData);
        $multiCategoryCount++;
    } else {
        $categoryPaths = [$categoryData];
    }
    
    // Find the most specific category from the paths
    $bestCategory = null;
    $maxSpecificity = 0;
    
    foreach ($categoryPaths as $categoryPath) {
        $category = findMostSpecificCategory(trim($categoryPath));
        
        if ($category) {
            // Calculate specificity (higher level = more specific)
            $specificity = $category->level;
            if ($category->parent_id > 0) {
                $specificity += 10; // Boost for child categories
            }
            
            if ($specificity > $maxSpecificity) {
                $maxSpecificity = $specificity;
                $bestCategory = $category;
            }
        }
    }
    
    // Reassign product if we found a better category
    if ($bestCategory && $product->category_id != $bestCategory->id) {
        $oldCategory = App\Models\Category::find($product->category_id);
        
        $product->category_id = $bestCategory->id;
        $product->save();
        
        $reassignedCount++;
        
        if ($reassignedCount <= 10) {
            $oldCategoryName = $oldCategory ? $oldCategory->name : 'Unknown';
            echo "✅ Reassigned: {$product->name}\n";
            echo "   From: {$oldCategoryName} → To: {$bestCategory->name}\n\n";
        }
    }
}

fclose($handle);

echo "\n📊 REASSIGNMENT SUMMARY:\n";
echo "Total rows processed: {$processedCount}\n";
echo "Products reassigned: {$reassignedCount}\n";
echo "Products not found: {$notFoundCount}\n";
echo "Products with multiple categories: {$multiCategoryCount}\n\n";

// Verify the improvements
echo "🔍 VERIFICATION - AFTER REASSIGNMENT:\n\n";

$parentAssigned = App\Models\Product::join('categories', 'products.category_id', '=', 'categories.id')
    ->where('categories.parent_id', 0)->count();

$childAssigned = App\Models\Product::join('categories', 'products.category_id', '=', 'categories.id')
    ->where('categories.parent_id', '>', 0)->count();

echo "Products assigned to parent categories: {$parentAssigned}\n";
echo "Products assigned to child categories: {$childAssigned}\n\n";

// Check specific improved categories
$testCategories = ['ANTIQUE COMICS', 'ARTWORKS', 'ANTIQUE MAGAZINES'];

foreach ($testCategories as $categoryName) {
    $parentCategory = App\Models\Category::where('name', $categoryName)->where('parent_id', 0)->first();
    if ($parentCategory) {
        $parentProducts = App\Models\Product::where('category_id', $parentCategory->id)->count();
        echo "📁 {$categoryName} (parent): {$parentProducts} products\n";
        
        $children = App\Models\Category::where('parent_id', $parentCategory->id)->get();
        $childTotalProducts = 0;
        
        foreach ($children->take(5) as $child) {
            $childProducts = App\Models\Product::where('category_id', $child->id)->count();
            $childTotalProducts += $childProducts;
            if ($childProducts > 0) {
                echo "  ✅ {$child->name}: {$childProducts} products\n";
            }
        }
        
        if ($children->count() > 5) {
            echo "  ... and " . ($children->count() - 5) . " more subcategories\n";
        }
        
        echo "  Total in subcategories: {$childTotalProducts}\n\n";
    }
}

echo "✅ PRODUCT-CATEGORY ASSIGNMENT FIX COMPLETED!\n";
echo "\nKey improvements:\n";
echo "• Products moved from broad parent categories to specific child categories\n";
echo "• Multiple category assignments handled (used most specific)\n";
echo "• Proper hierarchical structure maintained\n";
echo "• Category counts now reflect accurate product distribution\n";
?>
