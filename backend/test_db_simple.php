<?php

// Simple PDO test without Laravel bootstrapping
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Testing MySQL connection...\n";

$host = '127.0.0.1';
$port = 3306;
$dbname = 'rdrims_test';
$username = 'root';
$password = '';

try {
    // Try to connect without selecting a database first
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password, [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ MySQL connection successful\n";
    
    // Create test database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "✅ Database '$dbname' created/exists\n";
    
    // Select the database
    $pdo->exec("USE $dbname");
    echo "✅ Database '$dbname' selected\n";
    
    // Test a simple query
    $result = $pdo->query("SELECT 1 as test");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    echo "✅ Test query result: " . $row['test'] . "\n";
    
    echo "\n✅ All MySQL checks passed!\n";
    exit(0);
    
} catch (PDOException $e) {
    echo "❌ MySQL Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    exit(1);
}
