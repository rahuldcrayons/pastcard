<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== CREATING WORDPRESS CATEGORY STRUCTURE ===\n\n";

// Complete category structure from WordPress
$categoryStructure = [
    'ARTWORKS' => [
        'ARTWORK',
        'MINIATURE ART', 
        'OLD PAINTINGS'
    ],
    'PHILATELY' => [],
    'ANTIQUE COMICS' => [
        'MANOJ COMICS',
        'MANOJ CHITRA KATHA (BIG)',
        'MANOJ COMICS (SMALL)',
        'AMAR CHITRA KATHA',
        'DIAMOND COMICS',
        'RAJ COMICS',
        'FORT COMICS',
        'TULSI COMICS',
        'INDRAJAL COMICS',
        'OTHER COMICS',
        'COMICS FREEBIES'
    ],
    'ANTIQUE MAGAZINES' => [
        'NANDAN',
        'NANHE SAMRAT',
        'BAL BHASKAR',
        'MEHAKTA AANCHAL',
        'BALHANS',
        'CHAMPAK',
        'OTHER MAGAZINES',
        'CHANDA MAMA',
        'AHA ZINDAGI',
        'HANSTI DUNIYA',
        'SUMAN SAURABH',
        'WOMEN\'S MAGAZINES',
        'OSHO TIMES'
    ],
    'NOVELS' => [
        'ENGLISH NOVEL',
        'HINDI NOVEL',
        'STORY BOOK',
        'OTHER BOOKS'
    ],
    'RARE ITEMS' => [
        'ENCYCLOPEDIA',
        'OLD DIARIES',
        'DICTIONARIES',
        'OLD PHOTOGRAPHS',
        'OTHER ITEMS',
        'DAMAGED ITEMS',
        'SPORTS COLLECTIBLES'
    ],
    'TOYS' => [
        'OLD TOYS'
    ],
    'MUSIC COLLECTIBLES' => [
        'AUDIO CASSETTES',
        'CD/DVD',
        'VINYL RECORDS'
    ]
];

$createdCategories = 0;
$createdSubcategories = 0;
$existingCategories = 0;

function createSlug($name) {
    return \Str::slug(strtolower($name));
}

foreach ($categoryStructure as $mainCategoryName => $subcategories) {
    echo "Processing main category: {$mainCategoryName}\n";
    
    // Check if main category exists
    $mainCategory = App\Models\Category::where('name', $mainCategoryName)->first();
    
    if (!$mainCategory) {
        // Create main category
        $mainCategory = new App\Models\Category();
        $mainCategory->name = $mainCategoryName;
        $mainCategory->slug = createSlug($mainCategoryName);
        $mainCategory->parent_id = 0;
        $mainCategory->level = 0;
        $mainCategory->order_level = 0;
        $mainCategory->digital = 0;
        $mainCategory->save();
        
        // Create translation
        App\Models\CategoryTranslation::create([
            'lang' => env('DEFAULT_LANGUAGE', 'en'),
            'category_id' => $mainCategory->id,
            'name' => $mainCategoryName
        ]);
        
        echo "  ✓ Created main category: {$mainCategoryName} (ID: {$mainCategory->id})\n";
        $createdCategories++;
    } else {
        echo "  ✓ Main category exists: {$mainCategoryName} (ID: {$mainCategory->id})\n";
        $existingCategories++;
    }
    
    // Create subcategories
    foreach ($subcategories as $subcategoryName) {
        echo "    Processing subcategory: {$subcategoryName}\n";
        
        // Check if subcategory exists
        $subcategory = App\Models\Category::where('name', $subcategoryName)
                                        ->where('parent_id', $mainCategory->id)
                                        ->first();
        
        if (!$subcategory) {
            // Create subcategory
            $subcategory = new App\Models\Category();
            $subcategory->name = $subcategoryName;
            $subcategory->slug = createSlug($subcategoryName);
            $subcategory->parent_id = $mainCategory->id;
            $subcategory->level = 1;
            $subcategory->order_level = 0;
            $subcategory->digital = 0;
            $subcategory->save();
            
            // Create translation  
            App\Models\CategoryTranslation::create([
                'lang' => env('DEFAULT_LANGUAGE', 'en'),
                'category_id' => $subcategory->id,
                'name' => $subcategoryName
            ]);
            
            echo "      ✓ Created subcategory: {$subcategoryName} (ID: {$subcategory->id})\n";
            $createdSubcategories++;
        } else {
            echo "      ✓ Subcategory exists: {$subcategoryName} (ID: {$subcategory->id})\n";
        }
    }
    
    echo "\n";
}

echo "=== CATEGORY STRUCTURE SUMMARY ===\n";
echo "Main categories created: {$createdCategories}\n";
echo "Subcategories created: {$createdSubcategories}\n";
echo "Existing categories: {$existingCategories}\n";
echo "Total new categories: " . ($createdCategories + $createdSubcategories) . "\n\n";

// Display the complete structure
echo "=== COMPLETE CATEGORY STRUCTURE ===\n";
$mainCategories = App\Models\Category::where('parent_id', 0)->orderBy('name')->get();

foreach ($mainCategories as $main) {
    $productCount = App\Models\Product::where('category_id', $main->id)->count();
    echo "📁 {$main->name} ({$main->slug}) - {$productCount} products\n";
    
    $subcategories = App\Models\Category::where('parent_id', $main->id)->orderBy('name')->get();
    foreach ($subcategories as $sub) {
        $subProductCount = App\Models\Product::where('category_id', $sub->id)->count();
        echo "   └── {$sub->name} ({$sub->slug}) - {$subProductCount} products\n";
    }
    echo "\n";
}

echo "=== SUCCESS! ===\n";
echo "✓ WordPress category structure recreated in Laravel\n";
echo "✓ Proper parent-child relationships established\n";
echo "✓ All categories have proper slugs and translations\n";
echo "✓ Ready for organized product navigation\n";
?>
