<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== ANALYZING WORDPRESS CATEGORY STRUCTURE FROM CSV ===\n\n";

$csvFile = 'product.csv';
$handle = fopen($csvFile, 'r');

if (!$handle) {
    die("Could not open CSV file\n");
}

// Read and clean header (remove BOM)
$headerLine = fgets($handle);
$headerLine = preg_replace('/^\xEF\xBB\xBF/', '', $headerLine);
$header = str_getcsv($headerLine, ",");

// Find the tax:product_cat column index
$categoryColumnIndex = array_search('tax:product_cat', $header);

if ($categoryColumnIndex === false) {
    die("Could not find tax:product_cat column\n");
}

echo "Found tax:product_cat at column index: {$categoryColumnIndex}\n\n";

$categoryHierarchies = [];
$allCategories = [];
$productCount = 0;
$processedCount = 0;

// Process CSV rows
while (($row = fgetcsv($handle, 0, ",")) !== FALSE && $processedCount < 1000) {
    $productCount++;
    
    if (count($row) <= $categoryColumnIndex) {
        continue;
    }
    
    $categoryData = $row[$categoryColumnIndex];
    
    if (empty($categoryData)) {
        continue;
    }
    
    $processedCount++;
    
    // Split multiple category assignments (separated by |)
    $categoryGroups = explode('|', $categoryData);
    
    foreach ($categoryGroups as $categoryGroup) {
        $categoryGroup = trim($categoryGroup);
        if (empty($categoryGroup)) continue;
        
        // Check if it's a hierarchy (contains >)
        if (strpos($categoryGroup, '>') !== false) {
            $hierarchy = array_map('trim', explode('>', $categoryGroup));
            $categoryHierarchies[] = $hierarchy;
            
            // Add all categories in the hierarchy
            foreach ($hierarchy as $category) {
                if (!empty($category)) {
                    $allCategories[$category] = ($allCategories[$category] ?? 0) + 1;
                }
            }
        } else {
            // Single category
            $allCategories[$categoryGroup] = ($allCategories[$categoryGroup] ?? 0) + 1;
        }
    }
    
    // Show first few examples
    if ($processedCount <= 5) {
        echo "Product {$processedCount}: {$categoryData}\n";
    }
}

fclose($handle);

echo "\n=== ANALYSIS RESULTS ===\n";
echo "Products processed: {$processedCount}\n";
echo "Unique category hierarchies: " . count($categoryHierarchies) . "\n";
echo "Total unique categories: " . count($allCategories) . "\n\n";

echo "=== TOP CATEGORIES BY USAGE ===\n";
arsort($allCategories);
$topCategories = array_slice($allCategories, 0, 20, true);
foreach ($topCategories as $category => $count) {
    echo "• {$category}: {$count} products\n";
}

echo "\n=== SAMPLE CATEGORY HIERARCHIES ===\n";
$uniqueHierarchies = array_unique(array_map(function($h) { return implode(' > ', $h); }, $categoryHierarchies));
$sampleHierarchies = array_slice($uniqueHierarchies, 0, 15);

foreach ($sampleHierarchies as $hierarchy) {
    echo "• {$hierarchy}\n";
}

echo "\n=== PARENT-CHILD RELATIONSHIPS ===\n";
$parentChildMap = [];

foreach ($categoryHierarchies as $hierarchy) {
    for ($i = 0; $i < count($hierarchy) - 1; $i++) {
        $parent = trim($hierarchy[$i]);
        $child = trim($hierarchy[$i + 1]);
        
        if (!empty($parent) && !empty($child)) {
            if (!isset($parentChildMap[$parent])) {
                $parentChildMap[$parent] = [];
            }
            $parentChildMap[$parent][$child] = ($parentChildMap[$parent][$child] ?? 0) + 1;
        }
    }
}

foreach ($parentChildMap as $parent => $children) {
    echo "📁 {$parent}\n";
    foreach ($children as $child => $count) {
        echo "   └── {$child} ({$count} products)\n";
    }
    echo "\n";
}

echo "=== RECOMMENDED ACTIONS ===\n";
echo "1. Update category parent-child relationships based on CSV data\n";
echo "2. Reassign products to correct categories including subcategories\n";
echo "3. Handle products with multiple category assignments\n";
?>
