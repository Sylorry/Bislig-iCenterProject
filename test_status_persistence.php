<?php
// Test script to verify status persistence
require_once 'db.php';

echo "<h1>Status Persistence Test</h1>";

try {
    // Get a sample reservation
    $stmt = $conn->query("SELECT reservation_id, name, status FROM reservations ORDER BY reservation_date DESC LIMIT 1");
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reservation) {
        echo "<p>No reservations found.</p>";
        exit;
    }
    
    $test_id = $reservation['reservation_id'];
    $original_status = $reservation['status'];
    
    echo "<h2>Testing with Reservation:</h2>";
    echo "<p><strong>ID:</strong> $test_id</p>";
    echo "<p><strong>Name:</strong> " . $reservation['name'] . "</p>";
    echo "<p><strong>Original Status:</strong> $original_status</p>";
    
    // Test 1: Direct database update
    echo "<h3>Test 1: Direct Database Update</h3>";
    $new_status = ($original_status === 'completed') ? 'pending' : 'completed';
    
    $conn->beginTransaction();
    
    $update_stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
    $result = $update_stmt->execute([$new_status, $test_id]);
    
    if ($result) {
        // Verify the update
        $verify_stmt = $conn->prepare("SELECT status FROM reservations WHERE reservation_id = ?");
        $verify_stmt->execute([$test_id]);
        $updated_status = $verify_stmt->fetchColumn();
        
        if ($updated_status === $new_status) {
            echo "<p style='color: green;'>✅ Direct update successful! Status changed from '$original_status' to '$updated_status'</p>";
            
            // Revert back
            $revert_stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
            $revert_result = $revert_stmt->execute([$original_status, $test_id]);
            
            if ($revert_result) {
                echo "<p style='color: green;'>✅ Reverted back to '$original_status'</p>";
                $conn->commit();
            } else {
                echo "<p style='color: red;'>❌ Failed to revert!</p>";
                $conn->rollBack();
            }
        } else {
            echo "<p style='color: red;'>❌ Update verification failed! Expected: '$new_status', Got: '$updated_status'</p>";
            $conn->rollBack();
        }
    } else {
        echo "<p style='color: red;'>❌ Direct update failed!</p>";
        $conn->rollBack();
    }
    
    // Test 2: Simulate the update_status.php script
    echo "<h3>Test 2: Simulate update_status.php</h3>";
    
    // Simulate POST data
    $_POST['reservation_id'] = $test_id;
    $_POST['status'] = $new_status;
    
    // Capture output from update_status.php
    ob_start();
    include 'update_status.php';
    $output = ob_get_clean();
    
    echo "<p><strong>Script Output:</strong> " . htmlspecialchars($output) . "</p>";
    
    if (strpos($output, 'success') !== false) {
        echo "<p style='color: green;'>✅ update_status.php returned success</p>";
        
        // Check if the status was actually updated
        $check_stmt = $conn->prepare("SELECT status FROM reservations WHERE reservation_id = ?");
        $check_stmt->execute([$test_id]);
        $final_status = $check_stmt->fetchColumn();
        
        echo "<p><strong>Final Status in Database:</strong> $final_status</p>";
        
        if ($final_status === $new_status) {
            echo "<p style='color: green;'>✅ Status correctly updated in database!</p>";
        } else {
            echo "<p style='color: red;'>❌ Status not updated in database! Expected: '$new_status', Got: '$final_status'</p>";
        }
        
        // Revert back to original
        $revert_stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $revert_stmt->execute([$original_status, $test_id]);
        echo "<p style='color: green;'>✅ Reverted back to original status</p>";
        
    } else {
        echo "<p style='color: red;'>❌ update_status.php failed!</p>";
    }
    
    // Test 3: Check for any database constraints or issues
    echo "<h3>Test 3: Database Constraints Check</h3>";
    
    // Check if there are any foreign key constraints
    $stmt = $conn->query("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            TABLE_SCHEMA = 'admin' 
            AND TABLE_NAME = 'reservations'
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($constraints)) {
        echo "<p>✅ No foreign key constraints found on reservations table</p>";
    } else {
        echo "<p>⚠️ Foreign key constraints found:</p>";
        foreach ($constraints as $constraint) {
            echo "<p>- " . $constraint['CONSTRAINT_NAME'] . " references " . $constraint['REFERENCED_TABLE_NAME'] . "." . $constraint['REFERENCED_COLUMN_NAME'] . "</p>";
        }
    }
    
    // Check table engine and character set
    $stmt = $conn->query("SHOW TABLE STATUS WHERE Name = 'reservations'");
    $table_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Table Engine:</strong> " . $table_info['Engine'] . "</p>";
    echo "<p><strong>Character Set:</strong> " . $table_info['Collation'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 