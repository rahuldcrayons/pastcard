<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== FIXING ROUTING ISSUES ===\n\n";

// 1. Comment out conversation routes in product detail views
$filesToFix = [
    'resources/views/frontend/product_details.blade.php',
    'resources/views/frontend/digital_product_details.blade.php'
];

foreach ($filesToFix as $file) {
    $filePath = base_path($file);
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        
        // Comment out the conversation modal form
        if (strpos($content, "route('conversations.store')") !== false) {
            $content = preg_replace(
                '/(<form[^>]*action="{{ route\(\'conversations\.store\'\) }}"[^>]*>.*?<\/form>)/s',
                '{{-- Commented out conversation form (feature not enabled)
$1 --}}',
                $content
            );
            
            file_put_contents($filePath, $content);
            echo "✅ Commented out conversation form in: $file\n";
        }
    }
}

// 2. Add missing conversation routes to web.php
$webRoutesFile = base_path('routes/web.php');
$webRoutesContent = file_get_contents($webRoutesFile);

// Check if conversation routes already exist
if (strpos($webRoutesContent, 'conversations.') === false) {
    $conversationRoutes = "\n\n// Conversation routes (protected by auth middleware)\n";
    $conversationRoutes .= "Route::middleware('auth')->group(function () {\n";
    $conversationRoutes .= "    Route::get('/conversations', 'ConversationController@index')->name('conversations.index');\n";
    $conversationRoutes .= "    Route::post('/conversations', 'ConversationController@store')->name('conversations.store');\n";
    $conversationRoutes .= "    Route::get('/conversations/{id}', 'ConversationController@show')->name('conversations.show');\n";
    $conversationRoutes .= "    Route::post('/conversations/{id}/refresh', 'ConversationController@refresh')->name('conversations.refresh');\n";
    $conversationRoutes .= "    Route::post('/conversations/{id}/reply', 'ConversationController@reply')->name('conversations.message_store');\n";
    $conversationRoutes .= "});\n";
    
    // Add routes before the final closing
    $webRoutesContent = str_replace(
        "});\n\n",
        "});\n" . $conversationRoutes . "\n",
        $webRoutesContent
    );
    
    file_put_contents($webRoutesFile, $webRoutesContent);
    echo "✅ Added conversation routes to web.php\n";
}

echo "\n🔍 Checking for other undefined routes...\n\n";

// 3. Check for other potentially missing routes
$routesToCheck = [
    'conversations.admin_show',
    'affiliate.user.index',
    'club_point.index'
];

$routeList = collect(\Route::getRoutes())->map(function ($route) {
    return $route->getName();
})->filter()->toArray();

foreach ($routesToCheck as $routeName) {
    if (!in_array($routeName, $routeList)) {
        echo "⚠️  Route missing: $routeName\n";
    }
}

echo "\n✅ Routing fixes complete!\n";
echo "🎯 Run 'php artisan route:clear' to clear route cache.\n";
?>
