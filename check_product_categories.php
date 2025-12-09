<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== PRODUCT CATEGORY ASSIGNMENT CHECK ===\n\n";

// Quick check of database structure
echo "📋 DATABASE STRUCTURE CHECK:\n";
$sampleProduct = App\Models\Product::first();
if ($sampleProduct) {
    echo "Product ID: {$sampleProduct->id}\n";
    echo "Category ID: {$sampleProduct->category_id}\n";
    
    $category = App\Models\Category::find($sampleProduct->category_id);
    if ($category) {
        echo "Category: {$category->name}\n";
        echo "Parent ID: {$category->parent_id}\n";
        echo "Level: {$category->level}\n\n";
    }
}

// Check assignment distribution
echo "📊 ASSIGNMENT ANALYSIS:\n";

$parentAssigned = App\Models\Product::join('categories', 'products.category_id', '=', 'categories.id')
    ->where('categories.parent_id', 0)->count();

$childAssigned = App\Models\Product::join('categories', 'products.category_id', '=', 'categories.id')
    ->where('categories.parent_id', '>', 0)->count();

echo "Products assigned to parent categories: {$parentAssigned}\n";
echo "Products assigned to child categories: {$childAssigned}\n\n";

// Check specific categories
echo "🔍 SPECIFIC CATEGORY ANALYSIS:\n\n";

$problematicAssignments = [];

// Check ANTIQUE COMICS hierarchy
$antiqueComics = App\Models\Category::where('name', 'ANTIQUE COMICS')->where('parent_id', 0)->first();
if ($antiqueComics) {
    $parentProducts = App\Models\Product::where('category_id', $antiqueComics->id)->count();
    echo "ANTIQUE COMICS (parent): {$parentProducts} products\n";
    
    $children = App\Models\Category::where('parent_id', $antiqueComics->id)->get();
    foreach ($children->take(5) as $child) {
        $childProducts = App\Models\Product::where('category_id', $child->id)->count();
        echo "  └── {$child->name}: {$childProducts} products\n";
    }
    
    if ($parentProducts > 1000 && $children->count() > 0) {
        $problematicAssignments[] = "ANTIQUE COMICS has {$parentProducts} products but has {$children->count()} subcategories";
    }
    echo "\n";
}

// Check ARTWORKS hierarchy  
$artworks = App\Models\Category::where('name', 'ARTWORKS')->where('parent_id', 0)->first();
if ($artworks) {
    $parentProducts = App\Models\Product::where('category_id', $artworks->id)->count();
    echo "ARTWORKS (parent): {$parentProducts} products\n";
    
    $children = App\Models\Category::where('parent_id', $artworks->id)->get();
    foreach ($children as $child) {
        $childProducts = App\Models\Product::where('category_id', $child->id)->count();
        echo "  └── {$child->name}: {$childProducts} products\n";
    }
    
    if ($parentProducts > 500 && $children->count() > 0) {
        $problematicAssignments[] = "ARTWORKS has {$parentProducts} products but has {$children->count()} subcategories";
    }
    echo "\n";
}

echo "⚠️ ISSUES FOUND:\n";
foreach ($problematicAssignments as $issue) {
    echo "❌ {$issue}\n";
}

if (empty($problematicAssignments)) {
    echo "✅ No obvious issues found\n";
}

echo "\n📋 NEXT STEPS NEEDED:\n";
echo "1. Reassign products from parent to appropriate child categories\n";
echo "2. Use original product.csv category data for accurate assignments\n"; 
echo "3. Create script to fix product-category assignments\n";

echo "\n✅ Quick analysis completed!\n";
?>
