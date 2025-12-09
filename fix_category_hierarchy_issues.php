<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== FIXING CATEGORY HIERARCHY ISSUES ===\n\n";

// Step 1: Remove duplicate parent categories that should be children only
echo "🧹 REMOVING DUPLICATE PARENT CATEGORIES:\n";

$duplicateParentCategories = [
    'MANOJ COMICS',           // Should only be under ANTIQUE COMICS
    'INDRAJAL COMICS',        // Should only be under ANTIQUE COMICS  
    'CASSETTES',              // Should only be under CASSETTES / CD-DVD / VINYL RECODER
    'AUDIO CASSETTES',        // Should not be parent
    'CD/DVD',                 // Should not be parent
    'VINYL RECORDS',          // Should not be parent
];

foreach ($duplicateParentCategories as $categoryName) {
    $duplicateParents = App\Models\Category::where('name', $categoryName)
        ->where('parent_id', 0)  // Parent categories
        ->get();
        
    foreach ($duplicateParents as $category) {
        // Check if there's a proper child version of this category
        $properChild = App\Models\Category::where('name', $categoryName)
            ->where('parent_id', '>', 0)
            ->first();
            
        if ($properChild) {
            echo "🔄 Moving products from duplicate parent '{$categoryName}' to proper child category\n";
            
            // Move all products from duplicate parent to proper child
            $productsToMove = App\Models\Product::where('category_id', $category->id)->get();
            foreach ($productsToMove as $product) {
                $product->category_id = $properChild->id;
                $product->save();
            }
            
            echo "   ✅ Moved {$productsToMove->count()} products\n";
            
            // Delete the duplicate parent category
            $category->delete();
            echo "   ❌ Deleted duplicate parent category: {$categoryName}\n";
        }
    }
}

echo "\n📊 REASSIGNING PRODUCTS TO PROPER SUBCATEGORIES:\n";

// Step 2: Move products from parent categories to their most appropriate child categories
$categoryMappings = [
    'ANTIQUE COMICS' => [
        'manoj' => 'MANOJ COMICS',
        'diamond' => 'DIAMOND COMICS', 
        'raj comics' => 'RAJ COMICS',
        'amar chitra katha' => 'AMAR CHITRA KATHA',
        'indrajal' => 'INDRAJAL COMICS',
        'fort comics' => 'FORT COMICS',
        'tulsi comics' => 'TULSI COMICS',
        'tinkle' => 'Tinkle Gold',
        'comics freebies' => 'Comics Freebies',
        'phantom' => 'INDRAJAL COMICS',
        'mandrake' => 'INDRAJAL COMICS',
    ],
    'ANTIQUE MAGAZINES' => [
        'nandan' => 'Nandan',
        'nanhe samrat' => 'Nanhe samrat', 
        'bal bhaskar' => 'BAL BHASKAR',
        'balhans' => 'Balhans',
        'champak' => 'Champak',
        'chanda mama' => 'Chanda Mama',
        'aha zindagi' => 'AHA ZINDAGI',
        'hansti duniya' => 'Hansti Duniya',
        'suman saurabh' => 'Suman Saurabh',
        'women' => 'Women\'s Magazines',
        'osho' => 'Osho Times',
        'tell me why' => 'TELL ME WHY',
    ],
    'ARTWORKS' => [
        'miniature' => 'MINIATURE ART',
        'artwork' => 'ARTWORK', 
        'painting' => 'OLD PAINTINGS',
    ],
    'NOVELS' => [
        'english' => 'English Novel',
        'hindi' => 'Hindi Novel',
        'story' => 'STORY BOOK',
    ],
    'CASSETTES / CD-DVD / VINYL RECODER' => [
        'cassette' => 'AUDIO CASSETTES',
        'audio' => 'AUDIO CASSETTES',
        'cd' => 'CD/DVD',
        'dvd' => 'CD/DVD', 
        'vinyl' => 'VINYL RECORDS',
        'record' => 'VINYL RECORDS',
    ]
];

foreach ($categoryMappings as $parentName => $mappings) {
    $parentCategory = App\Models\Category::where('name', $parentName)->where('parent_id', 0)->first();
    
    if (!$parentCategory) {
        continue;
    }
    
    echo "\n📁 Processing {$parentName}:\n";
    
    $productsToReassign = App\Models\Product::where('category_id', $parentCategory->id)->get();
    echo "   Found {$productsToReassign->count()} products to reassign\n";
    
    $reassignedCount = 0;
    
    foreach ($productsToReassign as $product) {
        $productName = strtolower($product->name);
        $bestMatch = null;
        
        // Find the best matching subcategory based on product name
        foreach ($mappings as $keyword => $subcategoryName) {
            if (strpos($productName, strtolower($keyword)) !== false) {
                $subcategory = App\Models\Category::where('name', $subcategoryName)
                    ->where('parent_id', $parentCategory->id)
                    ->first();
                    
                if ($subcategory) {
                    $bestMatch = $subcategory;
                    break;
                }
            }
        }
        
        // If we found a matching subcategory, reassign the product
        if ($bestMatch) {
            $product->category_id = $bestMatch->id;
            $product->save();
            $reassignedCount++;
        }
    }
    
    echo "   ✅ Reassigned {$reassignedCount} products to specific subcategories\n";
}

echo "\n🔧 FIXING SUB-CHILD CATEGORY RELATIONSHIPS:\n";

// Step 3: Fix sub-child relationships for MANOJ COMICS and INDRAJAL COMICS
$subcategoryFixes = [
    'MANOJ COMICS' => [
        'MANOJ CHITRA KATHA (BIG)',
        'MANOJ COMICS (SMALL)'
    ],
    'INDRAJAL COMICS' => [
        'INDRAJAL COMICS(PHANTOM)',  
        'INDRAJAL COMICS(MANDRAKE)'
    ]
];

foreach ($subcategoryFixes as $parentName => $subChildren) {
    $parentCategory = App\Models\Category::where('name', $parentName)->first();
    
    if ($parentCategory) {
        echo "🔄 Fixing sub-children for {$parentName}:\n";
        
        foreach ($subChildren as $subChildName) {
            $subChild = App\Models\Category::where('name', $subChildName)->first();
            
            if ($subChild && $subChild->parent_id != $parentCategory->id) {
                $subChild->parent_id = $parentCategory->id;
                $subChild->level = 2;
                $subChild->save();
                echo "   ✅ Fixed parent relationship: {$subChildName} → {$parentName}\n";
            }
        }
    }
}

echo "\n📈 FINAL VERIFICATION:\n";

// Verify the improvements
$testCategories = ['ANTIQUE COMICS', 'ANTIQUE MAGAZINES', 'ARTWORKS', 'NOVELS'];

foreach ($testCategories as $categoryName) {
    $parentCategory = App\Models\Category::where('name', $categoryName)->where('parent_id', 0)->first();
    if ($parentCategory) {
        $parentProducts = App\Models\Product::where('category_id', $parentCategory->id)->count();
        echo "📁 {$categoryName}: {$parentProducts} products (parent)\n";
        
        $children = App\Models\Category::where('parent_id', $parentCategory->id)->get();
        $totalChildProducts = 0;
        
        foreach ($children as $child) {
            $childProducts = App\Models\Product::where('category_id', $child->id)->count();
            $totalChildProducts += $childProducts;
            if ($childProducts > 0) {
                echo "   ✅ {$child->name}: {$childProducts} products\n";
            }
            
            // Check for sub-children
            $subChildren = App\Models\Category::where('parent_id', $child->id)->get();
            foreach ($subChildren as $subChild) {
                $subChildProducts = App\Models\Product::where('category_id', $subChild->id)->count();
                if ($subChildProducts > 0) {
                    echo "       └── {$subChild->name}: {$subChildProducts} products\n";
                    $totalChildProducts += $subChildProducts;
                }
            }
        }
        
        echo "   📊 Total in subcategories: {$totalChildProducts}\n\n";
    }
}

echo "✅ CATEGORY HIERARCHY FIX COMPLETED!\n";
echo "\nImprovements made:\n";
echo "• Removed duplicate parent categories\n";
echo "• Properly structured parent-child-subchild hierarchy\n"; 
echo "• Reassigned products to specific subcategories based on name matching\n";
echo "• Fixed sub-child category relationships\n";
echo "• Eliminated navigation duplicates\n";
?>
