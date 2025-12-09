<?php
// Debug CSV parsing
$csvFile = 'exported.csv';

echo "=== CSV DEBUG ANALYSIS ===\n\n";

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    // Read first few lines raw
    echo "Raw CSV lines:\n";
    for ($i = 0; $i < 3; $i++) {
        $line = fgets($handle);
        echo "Line " . ($i + 1) . ": " . substr($line, 0, 200) . "...\n";
    }
    
    // Reset file pointer
    rewind($handle);
    
    // Parse header
    echo "\nParsing header with different methods:\n";
    
    // Method 1: fgetcsv
    $header1 = fgetcsv($handle, 10000, ",");
    echo "fgetcsv header count: " . count($header1) . "\n";
    echo "First 5 fields: " . implode(", ", array_slice($header1, 0, 5)) . "\n";
    
    // Reset and try different parsing
    rewind($handle);
    $line = fgets($handle);
    
    // Method 2: str_getcsv
    $header2 = str_getcsv($line, ",");
    echo "str_getcsv header count: " . count($header2) . "\n";
    echo "First 5 fields: " . implode(", ", array_slice($header2, 0, 5)) . "\n";
    
    // Read first data row
    echo "\nFirst data row:\n";
    $dataRow = fgetcsv($handle, 10000, ",");
    echo "Data row count: " . count($dataRow) . "\n";
    echo "First 5 values: " . implode(" | ", array_slice($dataRow, 0, 5)) . "\n";
    
    // Test array_combine
    if (count($header1) === count($dataRow)) {
        $combined = array_combine($header1, $dataRow);
        echo "\narray_combine successful!\n";
        
        // Debug field names
        echo "\nActual field names (with character codes):\n";
        foreach(array_keys($combined) as $index => $key) {
            $hexDump = bin2hex($key);
            echo sprintf("%2d. '%s' (len: %d, hex: %s)\n", $index + 1, $key, strlen($key), $hexDump);
            if ($index >= 5) break; // Show first 5
        }
        
        // Try with trimmed keys
        echo "\nTesting field access:\n";
        echo "post_title exists: " . (array_key_exists('post_title', $combined) ? 'YES' : 'NO') . "\n";
        echo "First field value: " . $dataRow[0] . "\n";
        
    } else {
        echo "\nHeader/Data mismatch: Header=" . count($header1) . ", Data=" . count($dataRow) . "\n";
    }
    
    fclose($handle);
} else {
    echo "Could not open CSV file!\n";
}
?>
