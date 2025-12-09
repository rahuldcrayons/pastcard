<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

echo "=== WORDPRESS SQL → LARAVEL IMPORT ===\n\n";

echo "This script will:\n";
echo "  - Read products and categories directly from the WordPress database (connection: wordpress).\n";
echo "  - CLEAR existing Laravel products & related stock rows.\n";
echo "  - Sync/import product categories from WooCommerce product_cat taxonomy.\n";
echo "  - Import products with prices, stock, categories, tags.\n";
echo "  - Map WordPress attachments to Laravel uploads using local wp-content/uploads.\n\n";

// Ensure wordpress connection works
try {
    $wp = DB::connection('wordpress');
    $productCount = $wp->table('posts')->where('post_type', 'product')->count();
    echo "WordPress products found: {$productCount}\n";
} catch (Exception $e) {
    echo "❌ Error connecting to WordPress DB using 'wordpress' connection: " . $e->getMessage() . "\n";
    echo "Check WP_DB_* values in .env and that the pastcart_new SQL has been imported.\n";
    exit(1);
}

if ($productCount === 0) {
    echo "⚠️ No products with post_type=product found in WordPress DB. Nothing to import.\n";
    exit(0);
}

echo "\n⚠️ WARNING: This will TRUNCATE Laravel products and related stock tables.\n";
echo "Type 'IMPORT' (in all caps) to continue: ";
$handle = fopen('php://stdin', 'r');
$line = fgets($handle);
$confirm = trim($line);
fclose($handle);

if ($confirm !== 'IMPORT') {
    echo "Aborted. No changes made.\n";
    exit(0);
}

// 1. Clear product-related tables in Laravel DB
DB::statement('SET FOREIGN_KEY_CHECKS=0');

$tablesToClear = [
    'products',
    'product_stocks',
    'product_translations',
    'product_taxes',
];

foreach ($tablesToClear as $table) {
    if (Schema::hasTable($table)) {
        DB::table($table)->truncate();
        echo "✅ Cleared table: {$table}\n";
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n📂 STEP 1: IMPORTING / SYNCING CATEGORIES FROM WORDPRESS\n";
echo str_repeat('-', 60) . "\n";

$termToCategoryId = importWordpressCategories($wp);

echo "\n📦 STEP 2: IMPORTING PRODUCTS FROM WORDPRESS\n";
echo str_repeat('-', 60) . "\n";

$totalImported = 0;
$totalErrors   = 0;
$offset        = 0;
$limit         = 200;

// Caches for attachments and uploads
$attachmentToUploadId = [];
$filePathToUploadId   = [];

while (true) {
    $products = $wp->table('posts')
        ->where('post_type', 'product')
        ->whereIn('post_status', ['publish', 'pending', 'draft'])
        ->orderBy('ID')
        ->offset($offset)
        ->limit($limit)
        ->get();

    if ($products->isEmpty()) {
        break;
    }

    $offset += $limit;

    $productIds = $products->pluck('ID')->all();

    // Load meta and taxonomy for this batch
    $metaByPost      = loadProductMeta($wp, $productIds);
    $taxonomyByPost  = loadProductTaxonomy($wp, $productIds);

    // Collect attachment IDs used by this batch
    $attachmentIds = collectAttachmentIdsFromMeta($metaByPost);

    if (!empty($attachmentIds)) {
        mapAttachmentsToUploads(
            $wp,
            $attachmentIds,
            $attachmentToUploadId,
            $filePathToUploadId
        );
    }

    foreach ($products as $wpProduct) {
        try {
            $meta = $metaByPost[$wpProduct->ID] ?? [];
            $tax  = $taxonomyByPost[$wpProduct->ID] ?? ['category_term_ids' => [], 'tag_names' => []];

            $product = new App\Models\Product();

            // Basic info
            $name = $wpProduct->post_title ?: ('Product ' . $wpProduct->ID);
            $slug = $wpProduct->post_name ?: Str::slug($name);

            $product->name = $name;
            $product->slug = Str::limit($slug, 180, '');

            // SKU
            $sku = $meta['_sku'] ?? null;
            if (!$sku) {
                $sku = 'WP-' . $wpProduct->ID;
            }
            $product->sku = Str::limit($sku, 190, '');

            // Published
            $product->published = isWordpressPublished($wpProduct->post_status) ? 1 : 0;

            // Featured
            $featuredVal = strtolower(trim($meta['_featured'] ?? 'no'));
            $product->featured = in_array($featuredVal, ['1', 'yes', 'true'], true) ? 1 : 0;

            // Description
            $description = $wpProduct->post_content ?: ($wpProduct->post_excerpt ?: '');
            $product->description      = $description;
            $product->meta_description = Str::limit(strip_tags($description), 160, '');

            // Pricing
            $regularPrice = firstNumeric([
                $meta['_regular_price'] ?? null,
                $meta['_price'] ?? null,
            ]);
            $salePrice = firstNumeric([
                $meta['_sale_price'] ?? null,
            ]);

            $product->unit_price = $regularPrice ?? 0;
            if (!is_null($salePrice) && $salePrice > 0 && $salePrice < $product->unit_price) {
                $product->purchase_price = $salePrice;
                $product->discount       = $product->unit_price - $salePrice;
                $product->discount_type  = 'amount';
            } else {
                $product->purchase_price = $product->unit_price;
                $product->discount       = 0;
            }

            // Stock
            $stockQty    = firstNumeric([$meta['_stock'] ?? null]);
            $stockStatus = strtolower(trim($meta['_stock_status'] ?? 'instock'));

            if (!is_null($stockQty)) {
                $product->current_stock = max(0, (int) $stockQty);
            } else {
                $product->current_stock = in_array($stockStatus, ['instock', 'onbackorder'], true) ? 100 : 0;
            }

            // Category mapping
            $categoryId = chooseCategoryIdForProduct(
                $name,
                $tax['category_term_ids'],
                $termToCategoryId
            );
            $product->category_id = $categoryId ?: 1; // Fallback category ID 1

            // Tags as comma-separated string
            $product->tags = empty($tax['tag_names']) ? null : implode(',', $tax['tag_names']);

            // Tax
            $taxStatus = strtolower(trim($meta['_tax_status'] ?? 'taxable'));
            $product->tax      = $taxStatus === 'taxable' ? 1 : 0;
            $product->tax_type = 'percent';

            // Digital / virtual
            $downloadable = strtolower(trim($meta['_downloadable'] ?? 'no'));
            $virtual      = strtolower(trim($meta['_virtual'] ?? 'no'));
            $product->digital = (in_array($downloadable, ['yes', '1', 'true'], true)
                              || in_array($virtual, ['yes', '1', 'true'], true)) ? 1 : 0;

            // Weight
            $weight = firstNumeric([$meta['_weight'] ?? null]);
            $product->weight = $weight ?? 0;

            // Required fields
            $product->user_id           = 1; // admin
            $product->added_by          = 'admin';
            $product->num_of_sale       = 0;
            $product->rating            = 0;
            $product->barcode           = $product->sku;
            $product->refundable        = 1;
            $product->shipping_type     = 'flat_rate';
            $product->shipping_cost     = 0;
            $product->est_shipping_days = 7;
            $product->min_qty           = 1;
            $product->cash_on_delivery  = 1;
            $product->meta_title        = Str::limit($product->name, 190, '');

            // Images: thumbnail + gallery
            $thumbnailUploadId = null;
            if (!empty($meta['_thumbnail_id'])) {
                $thumbId = (int) $meta['_thumbnail_id'];
                if (isset($attachmentToUploadId[$thumbId])) {
                    $thumbnailUploadId = $attachmentToUploadId[$thumbId];
                }
            }

            $galleryUploadIds = [];
            if (!empty($meta['_product_image_gallery'])) {
                $ids = array_filter(array_map('trim', explode(',', $meta['_product_image_gallery'])));
                foreach ($ids as $aid) {
                    $aidInt = (int) $aid;
                    if (isset($attachmentToUploadId[$aidInt])) {
                        $galleryUploadIds[] = $attachmentToUploadId[$aidInt];
                    }
                }
            }

            if ($thumbnailUploadId) {
                $product->thumbnail_img = $thumbnailUploadId;
            } elseif (!empty($galleryUploadIds)) {
                $product->thumbnail_img = $galleryUploadIds[0];
            }

            if (!empty($galleryUploadIds)) {
                // store as comma-separated list of upload IDs
                $product->photos = implode(',', $galleryUploadIds);
            }

            $product->save();

            // Create stock row
            if ($product->current_stock > 0 && Schema::hasTable('product_stocks')) {
                DB::table('product_stocks')->insert([
                    'product_id' => $product->id,
                    'variant'    => '',
                    'sku'        => $product->sku,
                    'price'      => $product->unit_price,
                    'qty'        => $product->current_stock,
                    'image'      => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $totalImported++;
        } catch (Exception $e) {
            $totalErrors++;
            if ($totalErrors <= 20) {
                echo "❌ Error on product ID {$wpProduct->ID}: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "Processed up to offset {$offset}. Imported so far: {$totalImported}, Errors: {$totalErrors}\n";
}

echo "\n✅ IMPORT FINISHED\n";
echo str_repeat('=', 40) . "\n";
echo "Imported products: {$totalImported}\n";
echo "Errors: {$totalErrors}\n";

// ====== HELPER FUNCTIONS ======

/**
 * Import or sync WooCommerce product categories into Laravel categories table.
 * Returns an array term_id => category_id.
 */
function importWordpressCategories($wp)
{
    $categories = $wp->table('terms')
        ->join('term_taxonomy', 'terms.term_id', '=', 'term_taxonomy.term_id')
        ->where('term_taxonomy.taxonomy', 'product_cat')
        ->select('terms.term_id', 'terms.name', 'terms.slug', 'term_taxonomy.parent', 'term_taxonomy.description')
        ->orderBy('term_taxonomy.parent')
        ->orderBy('terms.name')
        ->get();

    if ($categories->isEmpty()) {
        echo "No product_cat terms found in WordPress. Skipping category import.\n";
        return [];
    }

    // Build lookup by term_id
    $byTermId = [];
    foreach ($categories as $cat) {
        $byTermId[$cat->term_id] = $cat;
    }

    $termToCategoryId = [];

    // First pass: parents (parent = 0)
    foreach ($categories as $cat) {
        if ((int) $cat->parent !== 0) {
            continue;
        }

        $existing = App\Models\Category::where('slug', $cat->slug)
            ->where('parent_id', 0)
            ->first();

        if (!$existing) {
            $existing = new App\Models\Category();
            $existing->name             = $cat->name;
            $existing->slug             = Str::slug($cat->slug ?: $cat->name);
            $existing->parent_id        = 0;
            $existing->level            = 0;
            $existing->order_level      = 1000;
            $existing->commision_rate   = 0;
            $existing->featured         = 0;
            $existing->icon             = null;
            $existing->banner           = null;
            $existing->meta_title       = $cat->name;
            $existing->meta_description = $cat->description ?: $cat->name;
            $existing->save();

            echo "Parent category created: {$existing->name}\n";
        }

        $termToCategoryId[$cat->term_id] = $existing->id;
    }

    // Subsequent passes: children, until all mapped or no progress
    $remaining = true;
    while ($remaining) {
        $remaining = false;

        foreach ($categories as $cat) {
            if (isset($termToCategoryId[$cat->term_id])) {
                continue; // already mapped
            }

            $parentTermId = (int) $cat->parent;
            if ($parentTermId !== 0 && !isset($termToCategoryId[$parentTermId])) {
                // Parent not yet created
                continue;
            }

            $parentCategoryId = $parentTermId === 0 ? 0 : $termToCategoryId[$parentTermId];

            $existing = App\Models\Category::where('slug', $cat->slug)
                ->where('parent_id', $parentCategoryId)
                ->first();

            if (!$existing) {
                $level = 0;
                if ($parentCategoryId > 0) {
                    $parentCat = App\Models\Category::find($parentCategoryId);
                    $level     = $parentCat ? $parentCat->level + 1 : 1;
                }

                $existing = new App\Models\Category();
                $existing->name             = $cat->name;
                $existing->slug             = Str::slug($cat->slug ?: $cat->name);
                $existing->parent_id        = $parentCategoryId;
                $existing->level            = $level;
                $existing->order_level      = max(0, 500 - $level * 10);
                $existing->commision_rate   = 0;
                $existing->featured         = 0;
                $existing->icon             = null;
                $existing->banner           = null;
                $existing->meta_title       = $cat->name;
                $existing->meta_description = $cat->description ?: $cat->name;
                $existing->save();

                echo "Child category created: {$existing->name} (parent term {$parentTermId})\n";
            }

            $termToCategoryId[$cat->term_id] = $existing->id;
            $remaining                       = true;
        }
    }

    echo "Total WordPress product_cat terms: " . count($byTermId) . "\n";
    echo "Mapped to Laravel categories: " . count($termToCategoryId) . "\n";

    return $termToCategoryId;
}

/**
 * Load product meta for given WordPress product IDs.
 * Returns [post_id => [meta_key => meta_value]].
 */
function loadProductMeta($wp, array $productIds)
{
    if (empty($productIds)) {
        return [];
    }

    $metaKeys = [
        '_sku',
        '_regular_price',
        '_sale_price',
        '_price',
        '_stock',
        '_stock_status',
        '_manage_stock',
        '_weight',
        '_thumbnail_id',
        '_product_image_gallery',
        '_featured',
        '_tax_status',
        '_tax_class',
        '_downloadable',
        '_virtual',
    ];

    $rows = $wp->table('postmeta')
        ->whereIn('post_id', $productIds)
        ->whereIn('meta_key', $metaKeys)
        ->get();

    $result = [];
    foreach ($rows as $row) {
        $result[$row->post_id][$row->meta_key] = $row->meta_value;
    }

    return $result;
}

/**
 * Load taxonomy info (categories, tags) for given WordPress product IDs.
 * Returns [product_id => ['category_term_ids' => [...], 'tag_names' => [...]]].
 */
function loadProductTaxonomy($wp, array $productIds)
{
    if (empty($productIds)) {
        return [];
    }

    $rows = $wp->table('term_relationships')
        ->join('term_taxonomy', 'term_relationships.term_taxonomy_id', '=', 'term_taxonomy.term_taxonomy_id')
        ->join('terms', 'term_taxonomy.term_id', '=', 'terms.term_id')
        ->whereIn('term_relationships.object_id', $productIds)
        ->whereIn('term_taxonomy.taxonomy', ['product_cat', 'product_tag'])
        ->select(
            'term_relationships.object_id as object_id',
            'term_taxonomy.taxonomy as taxonomy',
            'terms.term_id as term_id',
            'terms.name as name'
        )
        ->get();

    $result = [];
    foreach ($productIds as $pid) {
        $result[$pid] = [
            'category_term_ids' => [],
            'tag_names'         => [],
        ];
    }

    foreach ($rows as $row) {
        $pid = $row->object_id;
        if (!isset($result[$pid])) {
            $result[$pid] = [
                'category_term_ids' => [],
                'tag_names'         => [],
            ];
        }

        if ($row->taxonomy === 'product_cat') {
            $result[$pid]['category_term_ids'][] = (int) $row->term_id;
        } elseif ($row->taxonomy === 'product_tag') {
            $result[$pid]['tag_names'][] = $row->name;
        }
    }

    // Make category term IDs unique
    foreach ($result as &$val) {
        $val['category_term_ids'] = array_values(array_unique($val['category_term_ids']));
        $val['tag_names']         = array_values(array_unique($val['tag_names']));
    }

    return $result;
}

/**
 * Collect all attachment IDs referenced in product meta (thumbnail + gallery).
 */
function collectAttachmentIdsFromMeta(array $metaByPost)
{
    $ids = [];

    foreach ($metaByPost as $meta) {
        if (!empty($meta['_thumbnail_id'])) {
            $ids[] = (int) $meta['_thumbnail_id'];
        }
        if (!empty($meta['_product_image_gallery'])) {
            $parts = array_filter(array_map('trim', explode(',', $meta['_product_image_gallery'])));
            foreach ($parts as $p) {
                if ($p !== '') {
                    $ids[] = (int) $p;
                }
            }
        }
    }

    return array_values(array_unique($ids));
}

/**
 * Map WordPress attachment IDs to Laravel uploads rows, using local wp-content/uploads.
 * Updates $attachmentToUploadId and $filePathToUploadId caches.
 */
function mapAttachmentsToUploads($wp, array $attachmentIds, array &$attachmentToUploadId, array &$filePathToUploadId)
{
    $attachmentIds = array_values(array_unique($attachmentIds));
    if (empty($attachmentIds)) {
        return;
    }

    // Filter out IDs we already mapped
    $newIds = [];
    foreach ($attachmentIds as $id) {
        if (!isset($attachmentToUploadId[$id])) {
            $newIds[] = $id;
        }
    }

    if (empty($newIds)) {
        return;
    }

    $attachments = $wp->table('posts')
        ->whereIn('ID', $newIds)
        ->where('post_type', 'attachment')
        ->select('ID', 'guid')
        ->get();

    if ($attachments->isEmpty()) {
        return;
    }

    // Load _wp_attached_file meta
    $metaRows = $wp->table('postmeta')
        ->whereIn('post_id', $newIds)
        ->where('meta_key', '_wp_attached_file')
        ->get();

    $attachedFile = [];
    foreach ($metaRows as $row) {
        $attachedFile[$row->post_id] = $row->meta_value;
    }

    foreach ($attachments as $att) {
        $relPath = null;

        if (!empty($attachedFile[$att->ID])) {
            // Example: 2024/10/image.jpg
            $relPath = 'wp-content/uploads/' . ltrim($attachedFile[$att->ID], '/');
        } elseif (!empty($att->guid)) {
            if (preg_match('~wp-content/uploads/(.*)$~i', $att->guid, $m)) {
                $relPath = 'wp-content/uploads/' . $m[1];
            }
        }

        if (!$relPath) {
            continue;
        }

        $relPath = str_replace('\\', '/', $relPath);
        $relPath = preg_replace('~/+~', '/', $relPath);

        $fullPath = public_path($relPath);
        if (!File::exists($fullPath)) {
            // File not found locally; skip
            continue;
        }

        // Reuse uploads entry if same file path already inserted
        if (isset($filePathToUploadId[$relPath])) {
            $uploadId = $filePathToUploadId[$relPath];
        } else {
            $ext = pathinfo($relPath, PATHINFO_EXTENSION) ?: 'jpg';
            $size = @filesize($fullPath) ?: 0;

            $uploadId = DB::table('uploads')->insertGetId([
                'file_original_name' => basename($relPath),
                'file_name'          => $relPath,
                'user_id'            => 1,
                'file_size'          => $size,
                'extension'          => $ext,
                'type'               => 'image',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            $filePathToUploadId[$relPath] = $uploadId;
        }

        $attachmentToUploadId[$att->ID] = $uploadId;
    }
}

/**
 * Determine if a WordPress post_status should be treated as published.
 */
function isWordpressPublished($status)
{
    $status = strtolower(trim($status));
    return in_array($status, ['publish', 'published', 'wc-completed', 'wc-processing'], true);
}

/**
 * Choose the best category ID for a product, given its term IDs and a term->category map.
 */
function chooseCategoryIdForProduct($productName, array $termIds, array $termToCategoryId)
{
    $bestId   = null;
    $bestLevel = -1;

    foreach ($termIds as $termId) {
        if (!isset($termToCategoryId[$termId])) {
            continue;
        }
        $catId = $termToCategoryId[$termId];
        $cat   = App\Models\Category::find($catId);
        if (!$cat) {
            continue;
        }
        $level = (int) ($cat->level ?? 0);
        if ($level > $bestLevel) {
            $bestLevel = $level;
            $bestId    = $cat->id;
        }
    }

    if ($bestId) {
        return $bestId;
    }

    // Fallback: simple name-based heuristic
    $nameLower = strtolower($productName);

    if (strpos($nameLower, 'comic') !== false) {
        $cat = App\Models\Category::where('name', 'LIKE', '%COMIC%')->orderBy('level', 'desc')->first();
    } elseif (strpos($nameLower, 'magazine') !== false) {
        $cat = App\Models\Category::where('name', 'LIKE', '%MAGAZINE%')->orderBy('level', 'desc')->first();
    } elseif (strpos($nameLower, 'novel') !== false || strpos($nameLower, 'book') !== false) {
        $cat = App\Models\Category::where('name', 'LIKE', '%NOVEL%')->orderBy('level', 'desc')->first();
    } elseif (strpos($nameLower, 'stamp') !== false || strpos($nameLower, 'philately') !== false) {
        $cat = App\Models\Category::where('name', 'LIKE', '%PHILATELY%')->orderBy('level', 'desc')->first();
    } else {
        $cat = App\Models\Category::where('name', 'LIKE', '%Other%')->orderBy('level', 'desc')->first();
    }

    return $cat ? $cat->id : null;
}

/**
 * Return the first non-null numeric value from an array of candidates.
 */
function firstNumeric(array $values)
{
    foreach ($values as $v) {
        if ($v === null) {
            continue;
        }
        $clean = preg_replace('/[^0-9.\-]/', '', (string) $v);
        if ($clean === '' || !is_numeric($clean)) {
            continue;
        }
        return (float) $clean;
    }
    return null;
}
