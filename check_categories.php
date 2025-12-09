<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "Current Categories in Database:\n";
echo "ID,Name,Slug,Parent_ID,Level\n";

$categories = App\Models\Category::select('id', 'name', 'slug', 'parent_id', 'level')->orderBy('id')->get();
foreach($categories as $cat) {
    echo $cat->id . ',"' . $cat->name . '",' . $cat->slug . ',' . ($cat->parent_id ?: '0') . ',' . $cat->level . "\n";
}

echo "\nTotal categories in database: " . count($categories) . "\n";
