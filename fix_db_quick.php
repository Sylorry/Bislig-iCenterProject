<?php
// Quick fix for the product_id field issue
require_once 'db.php';

echo "<h1>Quick Database Fix</h1>";

try {
    $conn = getDBConnection();
    
    // Check if product_id field exists and its properties
    $stmt = $conn->query("SHOW COLUMNS FROM reservations LIKE 'product_id'");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        echo "<p>✅ product_id field does not exist - no fix needed.</p>";
    } else {
        $column = $columns[0];
        echo "<p>Found product_id field:</p>";
        echo "<ul>";
        echo "<li>Type: " . $column['Type'] . "</li>";
        echo "<li>Null: " . $column['Null'] . "</li>";
        echo "<li>Default: " . ($column['Default'] ?? 'NULL') . "</li>";
        echo "</ul>";
        
        // Check if it's being used
        $stmt = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE product_id IS NOT NULL AND product_id != ''");
        $usedCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($usedCount == 0) {
            echo "<p>✅ Field is not being used - safe to remove.</p>";
            
            // Remove the field
            $conn->exec("ALTER TABLE reservations DROP COLUMN product_id");
            echo "<p style='color: green;'>✅ Successfully removed product_id field.</p>";
        } else {
            echo "<p>⚠️ Field is being used in $usedCount records.</p>";
            
            // Make it nullable instead
            $fieldType = $column['Type'];
            $conn->exec("ALTER TABLE reservations MODIFY COLUMN product_id $fieldType NULL");
            echo "<p style='color: green;'>✅ Successfully made product_id field nullable.</p>";
        }
    }
    
    // Test the fix
    echo "<h2>Testing the fix...</h2>";
    
    try {
        $testStmt = $conn->prepare("
            INSERT INTO reservations (
                name, contact_number, address, email, 
                reservation_date, reservation_time, status,
                product_count, reservation_fee, remaining_reservation_fee
            ) VALUES (
                'TEST_USER', '09123456789', 'TEST_ADDRESS', 'test@test.com',
                CURDATE(), CURTIME(), 'pending',
                1, 0, 0
            )
        ");
        
        $result = $testStmt->execute();
        
        if ($result) {
            $testId = $conn->lastInsertId();
            echo "<p style='color: green;'>✅ Test insert successful! New reservation ID: $testId</p>";
            
            // Clean up test record
            $conn->exec("DELETE FROM reservations WHERE reservation_id = $testId");
            echo "<p>🧹 Test record cleaned up.</p>";
            echo "<p style='color: green; font-weight: bold;'>🎉 Database fix completed successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Test insert failed.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Test insert error: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 