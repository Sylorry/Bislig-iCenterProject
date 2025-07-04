<?php
// Script to add the missing archived column to reservations table
require_once 'db.php';

try {
    $conn = getDBConnection();
    
    // Check if archived column exists
    $stmt = $conn->query("DESCRIBE reservations");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Database Column Check</h2>";
    echo "<p>Available columns: " . implode(', ', $columns) . "</p>";
    
    if (in_array('archived', $columns)) {
        echo "<p>✅ archived column already exists!</p>";
    } else {
        echo "<p>❌ archived column does not exist. Adding it now...</p>";
        
        // Add the archived column
        $conn->exec("ALTER TABLE reservations ADD COLUMN archived TINYINT(1) DEFAULT 0");
        echo "<p>✅ archived column added successfully!</p>";
        
        // Verify it was added
        $stmt = $conn->query("DESCRIBE reservations");
        $newColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('archived', $newColumns)) {
            echo "<p>✅ Column verification successful!</p>";
        } else {
            echo "<p>❌ Column verification failed!</p>";
        }
    }
    
    // Check for existing reservations
    $stmt = $conn->query("SELECT COUNT(*) as count FROM reservations");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Total reservations:</strong> " . $result['count'] . "</p>";
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE archived = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Archived reservations:</strong> " . $result['count'] . "</p>";
    
    echo "<p>✅ Database structure updated successfully!</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?> 