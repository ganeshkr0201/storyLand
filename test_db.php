<?php
require_once 'config.php';

try {
    $conn = getDBConnection();
    echo "Database connection successful!<br>";
    
    // Check if users table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "Users table exists!<br>";
        
        // Show table structure
        $stmt = $conn->query("DESCRIBE users");
        echo "Users table structure:<br>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Field: " . $row['Field'] . ", Type: " . $row['Type'] . "<br>";
        }
    } else {
        echo "Users table does not exist!<br>";
    }
    
    $conn = null;
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 