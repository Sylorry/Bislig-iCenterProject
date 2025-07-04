<?php
// Script to fix the product_id field issue in reservations table
require_once 'db.php';

echo "<h1>Fix Product ID Field Issue</h1>";

try {
    $conn = getDBConnection();
    
    // Check current table structure
    echo "<h2>1. Checking Current Table Structure</h2>";
    $stmt = $conn->query("DESCRIBE reservations");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $fieldNames = array_column($columns, 'Field');
    
    // Check if product_id field exists
    if (in_array('product_id', $fieldNames)) {
        echo "<p>⚠️ <strong>product_id</strong> field found in reservations table</p>";
        
        // Get details about the product_id field
        $productIdColumn = array_filter($columns, function($col) { return $col['Field'] === 'product_id'; });
        $productIdColumn = array_values($productIdColumn)[0];
        
        echo "<p>Field details:</p>";
        echo "<ul>";
        echo "<li>Type: " . $productIdColumn['Type'] . "</li>";
        echo "<li>Null: " . $productIdColumn['Null'] . "</li>";
        echo "<li>Default: " . ($productIdColumn['Default'] ?? 'NULL') . "</li>";
        echo "<li>Extra: " . $productIdColumn['Extra'] . "</li>";
        echo "</ul>";
        
        // Check if this field is being used
        $stmt = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE product_id IS NOT NULL AND product_id != ''");
        $usedCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo "<p>Records using product_id field: $usedCount</p>";
        
        if ($usedCount == 0) {
            echo "<p>✅ The product_id field is not being used in any records.</p>";
            echo "<p>This field appears to be legacy and can be safely removed.</p>";
            
            // Ask for confirmation
            if (isset($_POST['remove_field'])) {
                echo "<h3>Removing product_id field...</h3>";
                
                try {
                    $conn->exec("ALTER TABLE reservations DROP COLUMN product_id");
                    echo "<p style='color: green;'>✅ Successfully removed product_id field from reservations table.</p>";
                    echo "<p>The table should now work correctly with the new multi-product structure.</p>";
                } catch (Exception $e) {
                    echo "<p style='color: red;'>❌ Error removing field: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<form method='post'>";
                echo "<button type='submit' name='remove_field' style='background: #dc2626; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer;'>";
                echo "🗑️ Remove product_id Field";
                echo "</button>";
                echo "</form>";
            }
        } else {
            echo "<p>⚠️ The product_id field is being used in $usedCount records.</p>";
            echo "<p>We need to migrate this data to the new structure before removing the field.</p>";
            
            // Check if the new structure fields exist
            if (in_array('product_id_1', $fieldNames)) {
                echo "<p>✅ product_id_1 field exists - we can migrate the data.</p>";
                
                if (isset($_POST['migrate_data'])) {
                    echo "<h3>Migrating data from product_id to product_id_1...</h3>";
                    
                    try {
                        // Update records to move product_id to product_id_1
                        $stmt = $conn->prepare("UPDATE reservations SET product_id_1 = product_id WHERE product_id IS NOT NULL AND product_id != '' AND (product_id_1 IS NULL OR product_id_1 = '')");
                        $result = $stmt->execute();
                        
                        if ($result) {
                            $affectedRows = $stmt->rowCount();
                            echo "<p style='color: green;'>✅ Successfully migrated $affectedRows records.</p>";
                            
                            // Now remove the old field
                            $conn->exec("ALTER TABLE reservations DROP COLUMN product_id");
                            echo "<p style='color: green;'>✅ Successfully removed product_id field.</p>";
                        } else {
                            echo "<p style='color: red;'>❌ Error migrating data.</p>";
                        }
                    } catch (Exception $e) {
                        echo "<p style='color: red;'>❌ Error during migration: " . $e->getMessage() . "</p>";
                    }
                } else {
                    echo "<form method='post'>";
                    echo "<button type='submit' name='migrate_data' style='background: #059669; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer;'>";
                    echo "🔄 Migrate Data and Remove Field";
                    echo "</button>";
                    echo "</form>";
                }
            } else {
                echo "<p>❌ product_id_1 field does not exist. Cannot migrate data.</p>";
                echo "<p>Please ensure the new multi-product structure is properly set up first.</p>";
            }
        }
    } else {
        echo "<p>✅ <strong>product_id</strong> field does not exist - no action needed.</p>";
    }
    
    // Check for the new multi-product structure
    echo "<h2>2. Checking New Multi-Product Structure</h2>";
    
    $multiProductFields = [];
    for ($i = 1; $i <= 5; $i++) {
        $fields = ["product_id_$i", "product_name_$i", "product_brand_$i", "product_model_$i", "product_price_$i"];
        foreach ($fields as $field) {
            if (in_array($field, $fieldNames)) {
                $multiProductFields[] = $field;
            } else {
                echo "<p>❌ <strong>$field</strong> field missing</p>";
            }
        }
    }
    
    if (count($multiProductFields) == 20) { // 5 products × 4 fields each
        echo "<p>✅ All multi-product fields exist</p>";
    } else {
        echo "<p>⚠️ Some multi-product fields are missing. Found " . count($multiProductFields) . " out of 20 expected fields.</p>";
    }
    
    // Alternative fix: Make product_id field nullable
    if (in_array('product_id', $fieldNames) && !isset($_POST['remove_field']) && !isset($_POST['migrate_data'])) {
        echo "<h2>3. Alternative Fix: Make product_id Field Nullable</h2>";
        echo "<p>If you prefer to keep the field but make it nullable:</p>";
        
        if (isset($_POST['make_nullable'])) {
            echo "<h3>Making product_id field nullable...</h3>";
            
            try {
                // Get the current field type
                $productIdColumn = array_filter($columns, function($col) { return $col['Field'] === 'product_id'; });
                $productIdColumn = array_values($productIdColumn)[0];
                $fieldType = $productIdColumn['Type'];
                
                $conn->exec("ALTER TABLE reservations MODIFY COLUMN product_id $fieldType NULL");
                echo "<p style='color: green;'>✅ Successfully made product_id field nullable.</p>";
                echo "<p>The field can now accept NULL values.</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error making field nullable: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<form method='post'>";
            echo "<button type='submit' name='make_nullable' style='background: #3b82f6; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer;'>";
            echo "🔧 Make product_id Field Nullable";
            echo "</button>";
            echo "</form>";
        }
    }
    
    // Test the fix
    echo "<h2>4. Testing the Fix</h2>";
    
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
        } else {
            echo "<p style='color: red;'>❌ Test insert failed.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Test insert error: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Summary</h2>";
echo "<p>This script helps fix the 'Field product_id doesn't have a default value' error by:</p>";
echo "<ul>";
echo "<li>Identifying if the product_id field exists and is being used</li>";
echo "<li>Migrating data from the old single product_id field to the new multi-product structure</li>";
echo "<li>Removing the problematic field after data migration</li>";
echo "<li>Making the field nullable as an alternative solution</li>";
echo "<li>Testing that new reservations can be created successfully</li>";
echo "</ul>";

echo "<h2>Next Steps</h2>";
echo "<p>After running this fix:</p>";
echo "<ol>";
echo "<li>Test creating a new reservation through the web interface</li>";
echo "<li>Verify that the reservation is saved correctly in the database</li>";
echo "<li>Check that all product information is properly stored</li>";
echo "</ol>";
?> 