<?php

$defaultInput  = __DIR__ . DIRECTORY_SEPARATOR . 'pastcart_new.sql';
$defaultOutput = __DIR__ . DIRECTORY_SEPARATOR . 'pastcart_new_clean.sql';

// Allow overriding input/output via CLI arguments while keeping defaults
if (PHP_SAPI === 'cli' && isset($argv[1]) && isset($argv[2])) {
    $input  = $argv[1];
    $output = $argv[2];
} else {
    $input  = $defaultInput;
    $output = $defaultOutput;
}

if (!file_exists($input)) {
    echo "Source file not found: {$input}\n";
    exit(1);
}

echo "Sanitizing {$input} -> {$output}\n";

$in  = fopen($input, 'r');
if (!$in) {
    echo "Unable to open input file.\n";
    exit(1);
}

$out = fopen($output, 'w');
if (!$out) {
    fclose($in);
    echo "Unable to open output file for writing.\n";
    exit(1);
}

$lineNumber    = 0;
$skippedHeader = false;

while (($line = fgets($in)) !== false) {
    $lineNumber++;

    // Skip the first MariaDB-specific sandbox line or any line starting with /*M!
    if (!$skippedHeader) {
        $trim = ltrim($line);
        if (strpos($trim, '/*M!') === 0) {
            // Skip this line entirely
            $skippedHeader = true;
            continue;
        }
        // If first line is not that special comment, just mark header as seen and write it
        $skippedHeader = true;
    }

    // Also defensively skip any other MariaDB-only meta-comment lines starting with /*M!
    $trim = ltrim($line);
    if (strpos($trim, '/*M!') === 0) {
        continue;
    }

    fwrite($out, $line);

    if ($lineNumber % 500000 === 0) {
        echo "Processed {$lineNumber} lines...\n";
    }
}

fclose($in);
fclose($out);

echo "Done. Processed {$lineNumber} lines. Clean file: {$output}\n";
