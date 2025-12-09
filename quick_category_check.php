<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== QUICK CATEGORY CHECK ===\n";
echo "Total categories: " . App\Models\Category::count() . "\n";
echo "Main categories (parent_id = 0): " . App\Models\Category::where('parent_id', 0)->count() . "\n";
echo "Subcategories (parent_id > 0): " . App\Models\Category::where('parent_id', '>', 0)->count() . "\n";

echo "\nMain categories:\n";
$mainCats = App\Models\Category::where('parent_id', 0)->orderBy('name')->get(['id', 'name']);
foreach($mainCats as $cat) {
    echo "- {$cat->name} (ID: {$cat->id})\n";
}
?>
