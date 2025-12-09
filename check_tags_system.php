<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== CHECKING TAGS SYSTEM ===\n\n";

// Check if products table has tags column
$productsColumns = Schema::getColumnListing('products');
echo "Products table columns related to tags:\n";
foreach ($productsColumns as $column) {
    if (stripos($column, 'tag') !== false) {
        echo "✓ Found: {$column}\n";
    }
}

$hasTagsColumn = in_array('tags', $productsColumns);
echo "\nTags column in products table: " . ($hasTagsColumn ? "YES" : "NO") . "\n";

// Check if there's a separate tags table
$tableNames = DB::select('SHOW TABLES');
$hasTagsTable = false;
$hasProductTagsTable = false;

foreach ($tableNames as $table) {
    $tableName = array_values((array)$table)[0];
    if (stripos($tableName, 'tag') !== false) {
        echo "✓ Found table: {$tableName}\n";
        if ($tableName === 'tags') $hasTagsTable = true;
        if ($tableName === 'product_tags') $hasProductTagsTable = true;
    }
}

echo "\nTags table exists: " . ($hasTagsTable ? "YES" : "NO") . "\n";
echo "Product-Tags pivot table: " . ($hasProductTagsTable ? "YES" : "NO") . "\n";

// Check current tags in products if column exists
if ($hasTagsColumn) {
    echo "\n=== CURRENT TAGS ANALYSIS ===\n";
    $productsWithTags = App\Models\Product::whereNotNull('tags')->where('tags', '!=', '')->count();
    echo "Products with tags: {$productsWithTags}\n";
    
    if ($productsWithTags > 0) {
        echo "\nSample tags from products:\n";
        $sampleProducts = App\Models\Product::whereNotNull('tags')->where('tags', '!=', '')->take(5)->get(['id', 'name', 'tags']);
        foreach ($sampleProducts as $product) {
            echo "• {$product->name}: {$product->tags}\n";
        }
    }
}

// Check CSV for tax:product_tag column
echo "\n=== CHECKING CSV FOR TAGS DATA ===\n";
$csvFile = 'product.csv';
if (file_exists($csvFile)) {
    $handle = fopen($csvFile, 'r');
    $headerLine = fgets($handle);
    $headerLine = preg_replace('/^\xEF\xBB\xBF/', '', $headerLine);
    $header = str_getcsv($headerLine, ",");
    
    $tagColumnIndex = array_search('tax:product_tag', $header);
    echo "tax:product_tag column in CSV: " . ($tagColumnIndex !== false ? "YES (index: {$tagColumnIndex})" : "NO") . "\n";
    
    if ($tagColumnIndex !== false) {
        echo "\nSample tags from CSV:\n";
        $count = 0;
        while (($row = fgetcsv($handle, 0, ",")) !== FALSE && $count < 5) {
            if (count($row) > $tagColumnIndex && !empty($row[$tagColumnIndex])) {
                echo "• Product {$row[3]}: {$row[$tagColumnIndex]}\n";
                $count++;
            }
        }
    }
    
    fclose($handle);
} else {
    echo "CSV file not found\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
if (!$hasTagsColumn) {
    echo "❌ Products table missing 'tags' column - needs migration\n";
}
if ($tagColumnIndex !== false && ($productsWithTags == 0 || !$hasTagsColumn)) {
    echo "❌ Tags data from CSV needs to be imported\n";
}
if ($hasTagsColumn && $productsWithTags > 0) {
    echo "✅ Tags system appears to be working\n";
}

echo "\nNext steps:\n";
echo "1. Add tags column if missing\n";
echo "2. Import tags from CSV tax:product_tag column\n";
echo "3. Update product search to include tags\n";
?>
