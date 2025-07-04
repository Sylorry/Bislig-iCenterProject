<?php
// Script to check and fix proof_of_payment column
require_once 'db.php';

try {
    $conn = getDBConnection();
    
    // Check if proof_of_payment column exists
    $stmt = $conn->query("DESCRIBE reservations");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Database Column Check</h2>";
    echo "<p>Available columns: " . implode(', ', $columns) . "</p>";
    
    if (in_array('proof_of_payment', $columns)) {
        echo "<p>✅ proof_of_payment column already exists!</p>";
    } else {
        echo "<p>❌ proof_of_payment column does not exist. Adding it now...</p>";
        
        // Add the column
        $conn->exec("ALTER TABLE reservations ADD COLUMN proof_of_payment VARCHAR(255) NULL");
        echo "<p>✅ proof_of_payment column added successfully!</p>";
        
        // Verify it was added
        $stmt = $conn->query("DESCRIBE reservations");
        $newColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('proof_of_payment', $newColumns)) {
            echo "<p>✅ Column verification successful!</p>";
        } else {
            echo "<p>❌ Column verification failed!</p>";
        }
    }
    
    // Check for existing reservations with proof of payment
    $stmt = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE proof_of_payment IS NOT NULL AND proof_of_payment != ''");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Reservations with proof of payment:</strong> " . $result['count'] . "</p>";
    
    if ($result['count'] > 0) {
        // Show sample reservations
        $stmt = $conn->query("SELECT reservation_id, name, proof_of_payment FROM reservations WHERE proof_of_payment IS NOT NULL AND proof_of_payment != '' LIMIT 3");
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Sample Reservations with Proof of Payment:</h3>";
        foreach ($reservations as $res) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<p><strong>ID:</strong> {$res['reservation_id']}</p>";
            echo "<p><strong>Name:</strong> {$res['name']}</p>";
            echo "<p><strong>Proof File:</strong> {$res['proof_of_payment']}</p>";
            
            // Check if file exists
            $filePath = "uploads/proof_of_payment/{$res['proof_of_payment']}";
            if (file_exists($filePath)) {
                echo "<p>✅ File exists: $filePath</p>";
                echo "<img src='$filePath' style='max-width: 200px; border: 1px solid #ccc;' alt='Proof of Payment'>";
            } else {
                echo "<p>❌ File missing: $filePath</p>";
            }
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 