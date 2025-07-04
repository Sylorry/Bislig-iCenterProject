<?php
// Migration script to convert base64 proof of payment data to file-based storage
require_once 'db.php';

// Function to save uploaded image file (copied from reservations.php)
function saveProofOfPaymentImage($base64Data, $uploadDir = 'uploads/proof_of_payment') {
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Remove data URL prefix if present
    if (strpos($base64Data, 'data:image') === 0) {
        $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);
    }

    // Decode base64 string
    $imageData = base64_decode($base64Data);
    if ($imageData === false) {
        throw new Exception('Invalid image data');
    }

    // Generate unique filename with timestamp
    $timestamp = time();
    $filename = 'proof_' . uniqid() . '_' . $timestamp . '.jpg';
    $filepath = $uploadDir . '/' . $filename;

    // Save the file
    if (file_put_contents($filepath, $imageData) === false) {
        throw new Exception('Failed to save image file');
    }

    // Return just the filename for database storage
    return $filename;
}

echo "<h1>Proof of Payment Migration Script</h1>";
echo "<p>This script will convert existing base64 proof of payment data to file-based storage.</p>";

// Check if migration has already been run
$migrationFlagFile = 'uploads/proof_of_payment/.migration_complete';
if (file_exists($migrationFlagFile)) {
    echo "<div style='background: #d1fae5; border: 1px solid #34d399; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>✅ Migration Already Completed</h3>";
    echo "<p>The migration has already been run. If you need to re-run it, delete the file: <code>$migrationFlagFile</code></p>";
    echo "</div>";
    exit;
}

try {
    $conn = getDBConnection();
    
    // Find reservations with base64 proof of payment data
    $stmt = $conn->query("SELECT reservation_id, name, proof_of_payment FROM reservations WHERE proof_of_payment IS NOT NULL AND proof_of_payment != '' AND (proof_of_payment LIKE 'data:image%' OR LENGTH(proof_of_payment) > 100)");
    $base64Reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalCount = count($base64Reservations);
    echo "<h2>Found $totalCount reservations with base64 proof of payment data</h2>";
    
    if ($totalCount === 0) {
        echo "<div style='background: #d1fae5; border: 1px solid #34d399; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>✅ No Migration Needed</h3>";
        echo "<p>No reservations found with base64 proof of payment data. The system is already using file-based storage.</p>";
        echo "</div>";
        
        // Create migration flag
        file_put_contents($migrationFlagFile, date('Y-m-d H:i:s') . ' - No migration needed');
        exit;
    }
    
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>⚠️ Migration Required</h3>";
    echo "<p>Found $totalCount reservations that need to be migrated from base64 to file-based storage.</p>";
    echo "<p>This will improve performance and make images easier to view.</p>";
    echo "</div>";
    
    // Show sample of what will be migrated
    echo "<h3>Sample Reservations to Migrate:</h3>";
    $sampleCount = min(3, $totalCount);
    for ($i = 0; $i < $sampleCount; $i++) {
        $res = $base64Reservations[$i];
        echo "<div style='border: 1px solid #e5e7eb; padding: 10px; margin: 10px 0; border-radius: 8px;'>";
        echo "<p><strong>ID:</strong> {$res['reservation_id']}</p>";
        echo "<p><strong>Name:</strong> {$res['name']}</p>";
        echo "<p><strong>Current Data Length:</strong> " . strlen($res['proof_of_payment']) . " characters</p>";
        echo "</div>";
    }
    
    if ($totalCount > 3) {
        echo "<p>... and " . ($totalCount - 3) . " more reservations</p>";
    }
    
    // Migration form
    echo "<form method='post' style='margin: 20px 0;'>";
    echo "<input type='hidden' name='confirm_migration' value='1'>";
    echo "<button type='submit' style='background: #dc2626; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px;'>";
    echo "🚀 Start Migration";
    echo "</button>";
    echo "</form>";
    
    // Handle migration
    if (isset($_POST['confirm_migration'])) {
        echo "<h2>🔄 Starting Migration...</h2>";
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        foreach ($base64Reservations as $reservation) {
            try {
                // Save the base64 data as a file
                $filename = saveProofOfPaymentImage($reservation['proof_of_payment']);
                
                // Update the database record
                $updateStmt = $conn->prepare("UPDATE reservations SET proof_of_payment = ? WHERE reservation_id = ?");
                $updateStmt->execute([$filename, $reservation['reservation_id']]);
                
                $successCount++;
                echo "<div style='color: #059669; margin: 5px 0;'>✅ Migrated reservation ID {$reservation['reservation_id']} ({$reservation['name']}) -> $filename</div>";
                
            } catch (Exception $e) {
                $errorCount++;
                $errors[] = "Reservation ID {$reservation['reservation_id']}: " . $e->getMessage();
                echo "<div style='color: #dc2626; margin: 5px 0;'>❌ Failed to migrate reservation ID {$reservation['reservation_id']}: " . $e->getMessage() . "</div>";
            }
        }
        
        echo "<h3>Migration Results:</h3>";
        echo "<div style='background: #d1fae5; border: 1px solid #34d399; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
        echo "<p><strong>✅ Successfully migrated:</strong> $successCount reservations</p>";
        echo "<p><strong>❌ Failed to migrate:</strong> $errorCount reservations</p>";
        echo "</div>";
        
        if ($errorCount > 0) {
            echo "<h3>Errors:</h3>";
            echo "<div style='background: #fee2e2; border: 1px solid #f87171; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
            foreach ($errors as $error) {
                echo "<p style='margin: 5px 0;'>• $error</p>";
            }
            echo "</div>";
        }
        
        if ($successCount > 0) {
            // Create migration flag
            $migrationInfo = date('Y-m-d H:i:s') . " - Migrated $successCount reservations";
            if ($errorCount > 0) {
                $migrationInfo .= ", $errorCount errors";
            }
            file_put_contents($migrationFlagFile, $migrationInfo);
            
            echo "<div style='background: #d1fae5; border: 1px solid #34d399; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
            echo "<h3>✅ Migration Completed</h3>";
            echo "<p>The migration has been completed successfully!</p>";
            echo "<p>All future reservations will use the new file-based storage system.</p>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fee2e2; border: 1px solid #f87171; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>❌ Migration Error</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<h2>Migration Benefits:</h2>";
echo "<ul>";
echo "<li>✅ <strong>Better Performance:</strong> Database queries are faster without large base64 data</li>";
echo "<li>✅ <strong>Smaller Database:</strong> Reduced database size and backup times</li>";
echo "<li>✅ <strong>Easier Viewing:</strong> Images load faster and are easier to manage</li>";
echo "<li>✅ <strong>Better Error Handling:</strong> Clear feedback when images are missing</li>";
echo "<li>✅ <strong>Backward Compatibility:</strong> Old base64 data is automatically converted</li>";
echo "</ul>";

echo "<p><strong>Note:</strong> After migration, you can safely delete this script.</p>";
?> 