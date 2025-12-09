<?php
echo "Setting up database...\n";

try {
    // Database connection settings
    $host = '127.0.0.1';
    $username = 'root';
    $password = '';
    $database = 'pastcart';
    
    // Connect to MySQL without specifying database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    echo "Creating database '$database'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$database' created successfully!\n";
    
    // Select the database
    $pdo->exec("USE $database");
    
    // Import SQL file
    $sqlFile = 'database_backups/pastcart-35303733c60a.sql';
    if (file_exists($sqlFile)) {
        echo "Importing SQL file: $sqlFile\n";
        echo "This may take a few minutes...\n";
        
        $sql = file_get_contents($sqlFile);
        
        // Remove MySQL comments and execute
        $statements = explode(';', $sql);
        $count = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^(\/\*|--|#)/', $statement)) {
                try {
                    $pdo->exec($statement);
                    $count++;
                    if ($count % 100 == 0) {
                        echo "Executed $count statements...\n";
                    }
                } catch (PDOException $e) {
                    // Skip MySQL-specific commands that might fail
                    if (!strpos($e->getMessage(), 'syntax error') && !strpos($e->getMessage(), 'command not supported')) {
                        echo "Warning: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        echo "Database import completed! Executed $count statements.\n";
    } else {
        echo "Error: SQL file not found at $sqlFile\n";
        exit(1);
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Database setup completed successfully!\n";
?>
