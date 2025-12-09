<?php
echo "Updating .env file for local development...\n";

$envContent = file_get_contents('.env');

// Update database settings for local XAMPP
$envContent = str_replace('DB_HOST=sdb-61.hosting.stackcp.net', 'DB_HOST=127.0.0.1', $envContent);
$envContent = str_replace('DB_DATABASE=pastcart-35303733c60-35303237fdcd', 'DB_DATABASE=pastcart', $envContent);
$envContent = str_replace('DB_USERNAME=pastcart-35303733c60-35303237fdcd', 'DB_USERNAME=root', $envContent);
$envContent = str_replace('DB_PASSWORD=aNkkdRZY8mk5rHwz', 'DB_PASSWORD=', $envContent);

// Update app settings for local development
$envContent = str_replace('APP_URL="https://pastcart.in"', 'APP_URL="http://localhost:8000"', $envContent);

file_put_contents('.env', $envContent);

echo ".env file updated successfully!\n";
?>
