<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

// Read CSV file
$csvFile = 'product_categories_export_2025-10-14-06-30-10.csv';
$csvCategories = [];

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    $header = fgetcsv($handle, 1000, ","); // Skip header
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if (count($data) >= 6) {
            $csvCategories[$data[0]] = [
                'term_id' => $data[0],
                'name' => $data[1],
                'slug' => $data[2],
                'description' => $data[3],
                'display_type' => $data[4],
                'parent' => $data[5],
                'thumbnail' => isset($data[6]) ? $data[6] : ''
            ];
        }
    }
    fclose($handle);
}

// Get current database categories
$dbCategories = [];
$categories = App\Models\Category::all();
foreach($categories as $cat) {
    $dbCategories[$cat->id] = $cat;
}

echo "=== CATEGORY COMPARISON ANALYSIS ===\n\n";

echo "CSV Categories: " . count($csvCategories) . "\n";
echo "DB Categories: " . count($dbCategories) . "\n\n";

// Find missing categories in DB
$missingInDB = [];
foreach($csvCategories as $csvId => $csvCat) {
    if (!isset($dbCategories[$csvId])) {
        $missingInDB[$csvId] = $csvCat;
    }
}

echo "=== MISSING CATEGORIES IN DATABASE ===\n";
foreach($missingInDB as $missing) {
    echo "ID: {$missing['term_id']}, Name: {$missing['name']}, Slug: {$missing['slug']}, Parent: {$missing['parent']}\n";
}

// Find extra categories in DB
echo "\n=== EXTRA CATEGORIES IN DATABASE (not in CSV) ===\n";
foreach($dbCategories as $dbId => $dbCat) {
    if (!isset($csvCategories[$dbId])) {
        echo "ID: {$dbId}, Name: {$dbCat->name}, Slug: {$dbCat->slug}, Parent: {$dbCat->parent_id}\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Categories to import: " . count($missingInDB) . "\n";
echo "Total categories after import: " . (count($dbCategories) + count($missingInDB)) . "\n";
