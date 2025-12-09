<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== IMPORT PROGRESS CHECK ===\n\n";

// Get current stats
$total = App\Models\Product::count();
$published = App\Models\Product::where('published', 1)->count();
$withStock = App\Models\Product::where('current_stock', '>', 0)->count();
$withImages = App\Models\Product::whereNotNull('thumbnail_img')->count();

echo "📊 CURRENT STATS:\n";
echo "Total products imported: " . number_format($total) . "\n";
echo "Published: " . number_format($published) . "\n";
echo "With stock: " . number_format($withStock) . "\n";
echo "With images: " . number_format($withImages) . "\n\n";

// Category distribution
echo "📂 CATEGORY DISTRIBUTION:\n";
$parentProducts = App\Models\Product::whereHas('category', fn($q) => $q->where('parent_id', 0))->count();
$childProducts = App\Models\Product::whereHas('category', fn($q) => $q->where('parent_id', '>', 0))->count();

echo "Products in parent categories: " . number_format($parentProducts) . "\n";
echo "Products in child categories: " . number_format($childProducts) . "\n";
$percentage = $total > 0 ? round(($childProducts / $total) * 100, 2) : 0;
echo "Percentage in child categories: {$percentage}%\n\n";

// Top categories
echo "🏆 TOP CATEGORIES WITH PRODUCTS:\n";
$topCats = DB::table('products')
    ->join('categories', 'products.category_id', '=', 'categories.id')
    ->select('categories.name', 'categories.parent_id', DB::raw('count(products.id) as cnt'))
    ->groupBy('categories.id', 'categories.name', 'categories.parent_id')
    ->orderBy('cnt', 'desc')
    ->limit(15)
    ->get();

foreach ($topCats as $cat) {
    $type = $cat->parent_id == 0 ? ' (Parent)' : ' (Child)';
    echo str_pad($cat->name . $type, 40) . " : " . number_format($cat->cnt) . " products\n";
}

// Sample products
echo "\n📦 SAMPLE IMPORTED PRODUCTS:\n";
$samples = App\Models\Product::with('category')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

foreach ($samples as $product) {
    $catName = $product->category ? $product->category->name : 'No category';
    echo "- {$product->name} → {$catName} (₹{$product->unit_price})\n";
}

echo "\n";

// Progress indicator
if ($total < 75000) {
    $progress = round(($total / 76000) * 100, 2);
    echo "⏳ Import progress: ~{$progress}% complete\n";
    echo "   Estimated remaining: " . number_format(76000 - $total) . " products\n";
} else {
    echo "✅ Import appears to be complete or nearly complete!\n";
}

echo "\n🎯 Status check complete!\n";
?>
