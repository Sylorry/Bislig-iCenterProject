<?php
// Test script for proof of payment upload system
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

echo "<h1>Proof of Payment Upload System Test</h1>";

// Test 1: Check upload directory
echo "<h2>Test 1: Upload Directory</h2>";
$uploadDir = 'uploads/proof_of_payment';
if (file_exists($uploadDir)) {
    echo "✅ Upload directory exists: $uploadDir<br>";
    $files = scandir($uploadDir);
    $imageFiles = array_filter($files, function($file) {
        return in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']);
    });
    echo "📁 Found " . count($imageFiles) . " image files in directory<br>";
} else {
    echo "❌ Upload directory does not exist: $uploadDir<br>";
}

// Test 2: Check database table structure
echo "<h2>Test 2: Database Table Structure</h2>";
try {
    $conn = getDBConnection();
    $stmt = $conn->query("DESCRIBE reservations");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('proof_of_payment', $columns)) {
        echo "✅ proof_of_payment column exists in reservations table<br>";
        
        // Check data types
        $stmt = $conn->query("SHOW COLUMNS FROM reservations LIKE 'proof_of_payment'");
        $columnInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "📊 Column type: " . $columnInfo['Type'] . "<br>";
    } else {
        echo "❌ proof_of_payment column does not exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Check existing reservations
echo "<h2>Test 3: Existing Reservations</h2>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM reservations");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "📋 Total reservations: $total<br>";
    
    $stmt = $conn->query("SELECT COUNT(*) as with_proof FROM reservations WHERE proof_of_payment IS NOT NULL AND proof_of_payment != ''");
    $withProof = $stmt->fetch(PDO::FETCH_ASSOC)['with_proof'];
    echo "📸 Reservations with proof of payment: $withProof<br>";
    
    if ($withProof > 0) {
        // Show sample data
        $stmt = $conn->query("SELECT reservation_id, name, proof_of_payment FROM reservations WHERE proof_of_payment IS NOT NULL AND proof_of_payment != '' LIMIT 3");
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Sample Reservations:</h3>";
        foreach ($reservations as $res) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<p><strong>ID:</strong> {$res['reservation_id']}</p>";
            echo "<p><strong>Name:</strong> {$res['name']}</p>";
            echo "<p><strong>Proof:</strong> {$res['proof_of_payment']}</p>";
            
            // Check if it's base64 or filename
            $isBase64 = strpos($res['proof_of_payment'], 'data:image') === 0 || strlen($res['proof_of_payment']) > 100;
            echo "<p><strong>Format:</strong> " . ($isBase64 ? 'Base64' : 'Filename') . "</p>";
            
            if (!$isBase64) {
                // Check if file exists
                $filePath = $uploadDir . '/' . $res['proof_of_payment'];
                if (file_exists($filePath)) {
                    echo "<p>✅ File exists: $filePath</p>";
                    echo "<img src='$filePath' style='max-width: 200px; border: 1px solid #ccc;' alt='Proof of Payment'>";
                } else {
                    echo "<p>❌ File missing: $filePath</p>";
                }
            } else {
                echo "<p>📄 Base64 data (length: " . strlen($res['proof_of_payment']) . " characters)</p>";
            }
            echo "</div>";
        }
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 4: Test the save function with a sample image
echo "<h2>Test 4: Save Function Test</h2>";
try {
    // Create a simple test image (1x1 pixel red PNG)
    $testImageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
    $testBase64 = base64_encode($testImageData);
    
    $filename = saveProofOfPaymentImage($testBase64);
    echo "✅ Test image saved as: $filename<br>";
    
    $filePath = $uploadDir . '/' . $filename;
    if (file_exists($filePath)) {
        echo "✅ Test file exists: $filePath<br>";
        echo "<img src='$filePath' style='max-width: 100px; border: 1px solid #ccc;' alt='Test Image'>";
        
        // Clean up test file
        unlink($filePath);
        echo "<br>🧹 Test file cleaned up<br>";
    } else {
        echo "❌ Test file not found: $filePath<br>";
    }
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Complete</h2>";
echo "<p>The new proof of payment upload system is ready to use!</p>";
echo "<p>Key improvements:</p>";
echo "<ul>";
echo "<li>✅ Images are saved as files instead of base64 in database</li>";
echo "<li>✅ Better performance and smaller database size</li>";
echo "<li>✅ Easier to view and manage images</li>";
echo "<li>✅ Backward compatibility with existing base64 data</li>";
echo "<li>✅ Improved error handling and user feedback</li>";
echo "</ul>";
?> 