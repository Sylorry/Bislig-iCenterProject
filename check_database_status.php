<?php
// Script to check database structure and test status updates
require_once 'db.php';

echo "<h1>Database Status Check</h1>";

try {
    // Check table structure
    echo "<h2>Table Structure:</h2>";
    $stmt = $conn->query("DESCRIBE reservations");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for triggers
    echo "<h2>Triggers:</h2>";
    $stmt = $conn->query("SHOW TRIGGERS WHERE `Table` = 'reservations'");
    $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($triggers)) {
        echo "<p>No triggers found on reservations table.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Trigger</th><th>Event</th><th>Table</th><th>Statement</th></tr>";
        foreach ($triggers as $trigger) {
            echo "<tr>";
            echo "<td>" . $trigger['Trigger'] . "</td>";
            echo "<td>" . $trigger['Event'] . "</td>";
            echo "<td>" . $trigger['Table'] . "</td>";
            echo "<td>" . htmlspecialchars($trigger['Statement']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Get a sample reservation for testing
    echo "<h2>Sample Reservation for Testing:</h2>";
    $stmt = $conn->query("SELECT reservation_id, name, status, reservation_date FROM reservations ORDER BY reservation_date DESC LIMIT 1");
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reservation) {
        echo "<p><strong>ID:</strong> " . $reservation['reservation_id'] . "</p>";
        echo "<p><strong>Name:</strong> " . $reservation['name'] . "</p>";
        echo "<p><strong>Current Status:</strong> " . $reservation['status'] . "</p>";
        echo "<p><strong>Date:</strong> " . $reservation['reservation_date'] . "</p>";
        
        // Test direct database update
        $test_id = $reservation['reservation_id'];
        $current_status = $reservation['status'];
        $new_status = ($current_status === 'completed') ? 'pending' : 'completed';
        
        echo "<h3>Testing Direct Database Update:</h3>";
        echo "<p>Changing status from '$current_status' to '$new_status'</p>";
        
        // Start transaction for testing
        $conn->beginTransaction();
        
        try {
            // Update the status
            $update_stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
            $result = $update_stmt->execute([$new_status, $test_id]);
            
            echo "<p>Update result: " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";
            echo "<p>Rows affected: " . $update_stmt->rowCount() . "</p>";
            
            // Verify the update
            $verify_stmt = $conn->prepare("SELECT status FROM reservations WHERE reservation_id = ?");
            $verify_stmt->execute([$test_id]);
            $updated_status = $verify_stmt->fetchColumn();
            
            echo "<p>Updated status: '$updated_status'</p>";
            
            if ($updated_status === $new_status) {
                echo "<p style='color: green;'>✅ Direct update successful!</p>";
                
                // Now revert it back
                $revert_stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
                $revert_result = $revert_stmt->execute([$current_status, $test_id]);
                
                if ($revert_result) {
                    echo "<p style='color: green;'>✅ Reverted back to original status successfully!</p>";
                    $conn->commit();
                } else {
                    echo "<p style='color: red;'>❌ Failed to revert status!</p>";
                    $conn->rollBack();
                }
            } else {
                echo "<p style='color: red;'>❌ Direct update failed! Expected: '$new_status', Got: '$updated_status'</p>";
                $conn->rollBack();
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error during test: " . $e->getMessage() . "</p>";
            $conn->rollBack();
        }
        
    } else {
        echo "<p>No reservations found for testing.</p>";
    }
    
    // Check for any recent error logs
    echo "<h2>Recent Error Logs:</h2>";
    $log_file = ini_get('error_log');
    if ($log_file && file_exists($log_file)) {
        $logs = file_get_contents($log_file);
        $lines = explode("\n", $logs);
        $recent_lines = array_slice($lines, -30); // Last 30 lines
        
        echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 400px; overflow-y: auto;'>";
        foreach ($recent_lines as $line) {
            if (strpos($line, 'update_status.php') !== false || strpos($line, 'reserved.php') !== false) {
                echo htmlspecialchars($line) . "\n";
            }
        }
        echo "</pre>";
    } else {
        echo "<p>Error log not found or not accessible.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 