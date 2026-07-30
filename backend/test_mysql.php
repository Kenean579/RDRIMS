<?php

echo "Testing MySQL Connection...\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=rdrims_test', 'root', '');
    echo "✅ MySQL Connected Successfully\n";
    echo "Database: rdrims_test\n";
    
    // Test a simple query
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Users in database: " . $result['count'] . "\n";
    
} catch(Exception $e) {
    echo "❌ Connection Failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nAll checks passed!\n";
