<?php
/**
 * Database connection configuration
 * 
 * INSTRUCTIONS:
 * 1. Update the values with your database credentials
 * 2. For production, change password and consider using environment variables
 */

// Database connection parameters
$host = 'localhost';     // Database host (usually localhost)
$dbname = 'admin';       // Database name
$username = 'root';      // Database username
$password = '';          // Database password (often empty for local development)

// PDO connection options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,         // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,    // Return associative arrays by default
    PDO::ATTR_EMULATE_PREPARES => false,                // Use real prepared statements
    PDO::ATTR_PERSISTENT => false,                      // Don't use persistent connections
];

// Create a PDO instance (connect to the database)
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
    
    // Set session variables to ensure consistent behavior
    $conn->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
    $conn->exec("SET SESSION autocommit = 1");
    
    // Debug logging
    error_log("db.php: Database connection established successfully");
    
} catch (PDOException $e) {
    // For production environments, consider logging the error instead of displaying it
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}

// Function to get database connection (for backward compatibility)
function getConnection() {
    global $conn;
    return $conn;
}

// Function to get database connection (alternative name)
function getDBConnection() {
    global $conn;
    return $conn;
}
?>
