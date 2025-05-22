<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'connect_db.php'; // Include database connection

try {
    // Check if position_id column exists
    $stmt = $connect->query("SHOW COLUMNS FROM offices LIKE 'position_id'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        // Add the position_id column if it doesn't exist
        $connect->exec("ALTER TABLE offices ADD COLUMN position_id VARCHAR(20) DEFAULT NULL");
        echo "Success: position_id column has been added to the offices table.<br>";
    } else {
        echo "The position_id column already exists in the offices table.<br>";
    }
    
    // Display current table structure
    $stmt = $connect->query("DESCRIBE offices");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Offices Table Structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Show sample data
    $stmt = $connect->query("SELECT id, name, position_id FROM offices LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($rows) > 0) {
        echo "<h3>Sample Data:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Position ID</th></tr>";
        
        foreach ($rows as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['position_id']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    error_log("Error in add_position_column.php: " . $e->getMessage());
}
?> 