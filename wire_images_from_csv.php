<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\Product;
use App\Models\Upload;

// Optional CLI arg: max products to update per run
$maxToUpdate = null;
if (PHP_SAPI === 'cli' && !empty($argv[1]) && ctype_digit($argv[1])) {
    $maxToUpdate = (int) $argv[1];
}

$startFromRow = 1;
if (PHP_SAPI === 'cli' && !empty($argv[2]) && ctype_digit($argv[2])) {
    $startFromRow = max(1, (int) $argv[2]);
}

echo "=== WIRE IMAGES FROM product.csv INTO PRODUCTS/UPLOADS ===\n\n";
echo "Max products to update this run: " . ($maxToUpdate ?: 'ALL') . "\n";
echo "Starting from CSV data row: {$startFromRow}\n\n";

$productFile = 'product.csv';
if (!file_exists($productFile)) {
    echo "❌ product.csv not found in project root.\n";
    exit(1);
}

$handle = fopen($productFile, 'r');
if (!$handle) {
    echo "❌ Unable to open product.csv for reading.\n";
    exit(1);
}

$header = fgetcsv($handle);
if ($header === false) {
    echo "❌ product.csv appears to be empty.\n";
    fclose($handle);
    exit(1);
}

$columns = [];
foreach ($header as $i => $col) {
    $columns[trim($col)] = $i;
}

echo "Columns found in product.csv: " . implode(', ', array_keys($columns)) . "\n\n";

function csvVal(array $row, array $columns, array $keys, $default = null) {
    foreach ($keys as $key) {
        if (isset($columns[$key]) && isset($row[$columns[$key]])) {
            $val = trim($row[$columns[$key]]);
            if ($val !== '') return $val;
        }
    }
    return $default;
}

$updatedProducts = 0;
$skippedAlreadyImaged = 0;
$skippedNoMatch = 0;
$skippedNoImages = 0;
$rowNum = 0;

while (($row = fgetcsv($handle)) !== false) {
    $rowNum++;

    if ($maxToUpdate && $updatedProducts >= $maxToUpdate) {
        echo "\nReached maxToUpdate limit of {$maxToUpdate} products. Stopping.\n";
        break;
    }

    if ($rowNum < $startFromRow) {
        continue;
    }

    if ($rowNum % 1000 === 0) {
        echo "Processed {$rowNum} CSV rows... (updated: {$updatedProducts})\n";
    }

    // Name and slug must match how run_migration_auto.php created products
    $name = csvVal($row, $columns, ['name', 'Name', 'post_title', 'post_name'], '');
    if ($name === '') {
        continue;
    }

    $slug = Str::slug($name);

    /** @var Product|null $product */
    $product = Product::where('slug', $slug)->first();

    // Fallback 1: match by exact product name
    if (!$product) {
        $product = Product::where('name', $name)->first();
    }

    // Fallback 2: case-insensitive name match
    if (!$product) {
        $product = Product::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
    }

    if (!$product) {
        $skippedNoMatch++;
        continue;
    }

    // Skip products that already have images wired
    if (!empty($product->thumbnail_img) || !empty($product->photos)) {
        $skippedAlreadyImaged++;
        continue;
    }

    $imagesField = csvVal($row, $columns, ['images', 'Images'], '');
    if ($imagesField === '') {
        $skippedNoImages++;
        continue;
    }

    // Split multiple images (WooCommerce exporter uses | as separator)
    $segments = preg_split('/\s*\|\s*/', $imagesField);
    $uploadIds = [];

    foreach ($segments as $seg) {
        $seg = trim($seg);
        if ($seg === '') continue;

        // Extract the first URL (before alt/title meta like "! alt : ...")
        if (!preg_match('/https?:\/\/[^\s]+/i', $seg, $m)) {
            continue;
        }
        $url = $m[0];

        $path = parse_url($url, PHP_URL_PATH);
        $path = $path ? ltrim($path, '/') : '';

        $relative = null;
        if ($path !== '' && stripos($path, 'wp-content/uploads/') !== false) {
            // Normalize to relative path under public/
            $pos = stripos($path, 'wp-content/uploads/');
            $relative = substr($path, $pos); // e.g. wp-content/uploads/2019/07/file.jpg
        }

        $fileName = null;
        $externalLink = null;
        $fullPath = null;

        if ($relative) {
            $fullPath = base_path('public/' . $relative);
            if (file_exists($fullPath)) {
                $fileName = $relative;
            } else {
                // Local copy missing; fall back to external link
                $externalLink = $url;
            }
        } else {
            $externalLink = $url;
        }

        // Find or create Upload record
        $upload = null;
        if ($fileName) {
            $upload = Upload::where('file_name', $fileName)->first();
        }
        if (!$upload && $externalLink) {
            $upload = Upload::where('external_link', $externalLink)->first();
        }

        if (!$upload) {
            $infoPath = $fileName ?: ($path ?: basename($url));
            $origName = pathinfo($infoPath, PATHINFO_FILENAME) ?: 'image';
            $ext = pathinfo($infoPath, PATHINFO_EXTENSION) ?: null;

            $upload = new Upload();
            $upload->file_original_name = $origName;
            $upload->file_name = $fileName;
            $upload->user_id = 1; // admin
            $upload->extension = $ext;
            $upload->type = 'image';
            $upload->file_size = ($fileName && $fullPath && file_exists($fullPath)) ? (int) filesize($fullPath) : 0;
            // external_link column exists even if not fillable; assign directly
            $upload->external_link = $externalLink;
            $upload->processed = 0;
            $upload->processed_at = null;
            $upload->save();
        }

        if ($upload && !in_array($upload->id, $uploadIds, true)) {
            $uploadIds[] = $upload->id;
        }
    }

    if (empty($uploadIds)) {
        $skippedNoImages++;
        continue;
    }

    $product->thumbnail_img = $uploadIds[0];
    $product->photos = implode(',', $uploadIds);
    $product->save();

    $updatedProducts++;
}

fclose($handle);

echo "\n=== IMAGE WIRING COMPLETE ===\n";
echo "CSV rows processed: {$rowNum}\n";
echo "Products updated with images: {$updatedProducts}\n";
echo "Skipped (already had images): {$skippedAlreadyImaged}\n";
echo "Skipped (no matching product): {$skippedNoMatch}\n";
echo "Skipped (no valid image URLs): {$skippedNoImages}\n";
