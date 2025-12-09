<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== FINDING NAME COLUMN ===\n\n";

$csvFile = 'product.csv';
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle, 0, ',', '"', '\\');

echo "ALL COLUMNS IN CSV:\n";
echo str_repeat("-", 60) . "\n";

foreach ($header as $i => $col) {
    echo "Column {$i}: {$col}\n";
}

echo "\n\nCHECKING FIRST ROW DATA:\n";
echo str_repeat("-", 60) . "\n";

$firstRow = fgetcsv($handle, 0, ',', '"', '\\');

// Check each column for potential product names
$possibleNameColumns = [];

foreach ($header as $i => $col) {
    if (isset($firstRow[$i]) && !empty(trim($firstRow[$i]))) {
        $value = trim($firstRow[$i]);
        
        // Check if this could be a product name
        if (strlen($value) > 5 && strlen($value) < 500) {
            // Check if it's not a number, date, or URL
            if (!is_numeric($value) && 
                !filter_var($value, FILTER_VALIDATE_URL) &&
                !preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                
                echo "\nColumn {$i} ({$col}): \n";
                echo "  Value: " . substr($value, 0, 100) . "\n";
                
                // Check if it looks like a product name
                if (!in_array(strtolower($value), ['yes', 'no', 'true', 'false', 'publish', 'draft', 'instock', 'outofstock'])) {
                    $possibleNameColumns[$col] = $value;
                }
            }
        }
    }
}

echo "\n\nPOSSIBLE NAME COLUMNS:\n";
echo str_repeat("=", 60) . "\n";

if (empty($possibleNameColumns)) {
    echo "❌ No suitable name columns found!\n\n";
    
    // Show first row data for debugging
    echo "FIRST ROW COMPLETE DATA:\n";
    foreach ($header as $i => $col) {
        if (isset($firstRow[$i]) && !empty($firstRow[$i])) {
            echo "{$col}: " . substr($firstRow[$i], 0, 50) . "\n";
        }
    }
} else {
    foreach ($possibleNameColumns as $col => $value) {
        echo "✅ {$col}: {$value}\n";
    }
}

// Check a few more rows to confirm pattern
echo "\n\nCHECKING MORE ROWS:\n";
echo str_repeat("-", 60) . "\n";

for ($j = 0; $j < 5; $j++) {
    $row = fgetcsv($handle, 0, ',', '"', '\\');
    if (!$row) break;
    
    echo "\nRow " . ($j + 2) . ":\n";
    
    // Check the first non-empty, non-numeric column
    $foundName = false;
    foreach ($header as $i => $col) {
        if (isset($row[$i]) && !empty(trim($row[$i]))) {
            $value = trim($row[$i]);
            
            // Skip if numeric, URL, or common status values
            if (!is_numeric($value) && 
                !filter_var($value, FILTER_VALIDATE_URL) &&
                strlen($value) > 5 && strlen($value) < 500 &&
                !in_array(strtolower($value), ['yes', 'no', 'publish', 'draft', 'instock', 'outofstock', 'simple', 'variable'])) {
                
                echo "  {$col}: " . substr($value, 0, 80) . "\n";
                $foundName = true;
                break;
            }
        }
    }
    
    if (!$foundName) {
        // Show SKU and price at least
        $sku = isset($row[12]) ? $row[12] : '[no sku]';
        $price = isset($row[18]) ? $row[18] : '[no price]';
        echo "  SKU: {$sku}, Price: {$price}\n";
    }
}

fclose($handle);

echo "\n✅ Analysis complete!\n";
?>
