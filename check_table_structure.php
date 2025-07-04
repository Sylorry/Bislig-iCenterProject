<?php
// Script to check the reservations table structure
require_once 'db.php';

try {
    $conn = getDBConnection();
    
    echo "<h1>Reservations Table Structure Check</h1>";
    
    // Get table structure
    $stmt = $conn->query("DESCRIBE reservations");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Current Table Structure:</h2>";
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
    
    // Check for specific fields that might be causing issues
    echo "<h2>Field Analysis:</h2>";
    
    $fieldNames = array_column($columns, 'Field');
    
    // Check for product_id field
    if (in_array('product_id', $fieldNames)) {
        echo "<p>✅ <strong>product_id</strong> field exists</p>";
        $productIdColumn = array_filter($columns, function($col) { return $col['Field'] === 'product_id'; });
        $productIdColumn = array_values($productIdColumn)[0];
        echo "<p>Type: " . $productIdColumn['Type'] . ", Null: " . $productIdColumn['Null'] . ", Default: " . ($productIdColumn['Default'] ?? 'NULL') . "</p>";
    } else {
        echo "<p>❌ <strong>product_id</strong> field does not exist</p>";
    }
    
    // Check for product_id_1 through product_id_5 fields
    for ($i = 1; $i <= 5; $i++) {
        $fieldName = "product_id_$i";
        if (in_array($fieldName, $fieldNames)) {
            echo "<p>✅ <strong>$fieldName</strong> field exists</p>";
        } else {
            echo "<p>❌ <strong>$fieldName</strong> field does not exist</p>";
        }
    }
    
    // Check for other important fields
    $importantFields = ['reservation_id', 'name', 'contact_number', 'address', 'email', 'reservation_date', 'reservation_time', 'status', 'proof_of_payment', 'reservation_fee', 'remaining_reservation_fee', 'product_count'];
    
    echo "<h3>Important Fields Check:</h3>";
    foreach ($importantFields as $field) {
        if (in_array($field, $fieldNames)) {
            echo "<p>✅ <strong>$field</strong> exists</p>";
        } else {
            echo "<p>❌ <strong>$field</strong> missing</p>";
        }
    }
    
    // Show sample data
    echo "<h2>Sample Data (Last 3 Reservations):</h2>";
    $stmt = $conn->query("SELECT * FROM reservations ORDER BY reservation_id DESC LIMIT 3");
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($reservations)) {
        echo "<p>No reservations found in the table.</p>";
    } else {
        foreach ($reservations as $index => $reservation) {
            echo "<h3>Reservation " . ($index + 1) . " (ID: " . $reservation['reservation_id'] . "):</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            
            foreach ($reservation as $field => $value) {
                $displayValue = $value;
                if (strlen($displayValue) > 100) {
                    $displayValue = substr($displayValue, 0, 100) . "... (truncated)";
                }
                echo "<tr>";
                echo "<td><strong>$field</strong></td>";
                echo "<td>" . htmlspecialchars($displayValue) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 