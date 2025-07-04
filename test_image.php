<?php
require_once 'db.php';

try {
    $conn = getDBConnection();
    
    // Get a few reservations with proof of payment
    $stmt = $conn->query("SELECT reservation_id, name, proof_of_payment FROM reservations WHERE proof_of_payment IS NOT NULL AND proof_of_payment != '' LIMIT 5");
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Testing Proof of Payment Images</h2>";
    
    foreach ($reservations as $res) {
        echo "<h3>Reservation ID: {$res['reservation_id']} - {$res['name']}</h3>";
        echo "<p>Proof of payment filename: {$res['proof_of_payment']}</p>";
        
        $imagePath = "uploads/proof_of_payment/{$res['proof_of_payment']}";
        echo "<p>Full path: $imagePath</p>";
        
        if (file_exists($imagePath)) {
            echo "<p style='color: green;'>✅ File exists</p>";
            echo "<p>File size: " . filesize($imagePath) . " bytes</p>";
            echo "<p>File permissions: " . substr(sprintf('%o', fileperms($imagePath)), -4) . "</p>";
            
            // Try to display the image
            echo "<img src='$imagePath' style='max-width: 300px; max-height: 300px; border: 1px solid #ccc;' alt='Proof of Payment'><br><br>";
        } else {
            echo "<p style='color: red;'>❌ File does not exist</p>";
        }
        
        echo "<hr>";
    }
    
    if (empty($reservations)) {
        echo "<p>No reservations found with proof of payment.</p>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 