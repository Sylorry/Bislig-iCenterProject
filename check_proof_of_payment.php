<?php
require_once 'db.php';

try {
    $conn = getDBConnection();
    
    // Check if proof_of_payment column exists
    $stmt = $conn->query("DESCRIBE reservations");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Database columns:\n";
    print_r($columns);
    
    // Check if proof_of_payment exists
    if (in_array('proof_of_payment', $columns)) {
        echo "\n✅ proof_of_payment column exists!\n";
        
        // Check for reservations with proof of payment
        $stmt = $conn->query("SELECT reservation_id, name, proof_of_payment FROM reservations WHERE proof_of_payment IS NOT NULL AND proof_of_payment != '' LIMIT 5");
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nReservations with proof of payment:\n";
        foreach ($reservations as $res) {
            echo "ID: {$res['reservation_id']}, Name: {$res['name']}, Proof: {$res['proof_of_payment']}\n";
            
            // Check if file exists
            $filePath = "uploads/proof_of_payment/{$res['proof_of_payment']}";
            if (file_exists($filePath)) {
                echo "  ✅ File exists: $filePath\n";
            } else {
                echo "  ❌ File missing: $filePath\n";
            }
        }
        
        if (empty($reservations)) {
            echo "No reservations found with proof of payment.\n";
        }
        
    } else {
        echo "\n❌ proof_of_payment column does not exist!\n";
        
        // Try to add it
        echo "Attempting to add proof_of_payment column...\n";
        $conn->exec("ALTER TABLE reservations ADD COLUMN proof_of_payment VARCHAR(255) NULL");
        echo "✅ Column added successfully!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 