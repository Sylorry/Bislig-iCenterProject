<?php
echo "<h1>Database Connection Test</h1>";

// Test 1: Check if PDO is available
if (extension_loaded('pdo_mysql')) {
    echo "<p>✅ PDO MySQL extension is loaded</p>";
} else {
    echo "<p>❌ PDO MySQL extension is NOT loaded</p>";
    exit;
}

// Test 2: Try to connect to database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=admin", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Database connection successful</p>";
    
    // Test 3: Check if reservations table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'reservations'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Reservations table exists</p>";
        
        // Test 4: Check table structure
        $stmt = $pdo->query("DESCRIBE reservations");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p><strong>Table columns:</strong> " . implode(', ', $columns) . "</p>";
        
        // Test 5: Check if proof_of_payment column exists
        if (in_array('proof_of_payment', $columns)) {
            echo "<p>✅ proof_of_payment column exists</p>";
            
            // Test 6: Check for data
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations WHERE proof_of_payment IS NOT NULL AND proof_of_payment != ''");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p><strong>Reservations with proof of payment:</strong> " . $result['count'] . "</p>";
            
            if ($result['count'] > 0) {
                // Show sample data
                $stmt = $pdo->query("SELECT reservation_id, name, proof_of_payment FROM reservations WHERE proof_of_payment IS NOT NULL AND proof_of_payment != '' LIMIT 1");
                $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<h3>Sample Reservation:</h3>";
                echo "<p><strong>ID:</strong> " . $reservation['reservation_id'] . "</p>";
                echo "<p><strong>Name:</strong> " . $reservation['name'] . "</p>";
                echo "<p><strong>Proof File:</strong> " . $reservation['proof_of_payment'] . "</p>";
                
                // Check if file exists
                $filePath = "uploads/proof_of_payment/" . $reservation['proof_of_payment'];
                if (file_exists($filePath)) {
                    echo "<p>✅ File exists: $filePath</p>";
                    echo "<img src='$filePath' style='max-width: 200px; border: 1px solid #ccc;' alt='Proof of Payment'>";
                } else {
                    echo "<p>❌ File missing: $filePath</p>";
                }
            }
        } else {
            echo "<p>❌ proof_of_payment column does not exist</p>";
        }
        
    } else {
        echo "<p>❌ Reservations table does not exist</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}
?> 