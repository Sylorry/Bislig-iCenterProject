<?php
// Test file to check proof of payment image display
require_once 'db.php';

try {
    $conn = getDBConnection();
    
    // Check if proof_of_payment column exists
    $stmt = $conn->query("DESCRIBE reservations");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Database Check</h2>";
    echo "<p>Available columns: " . implode(', ', $columns) . "</p>";
    
    if (in_array('proof_of_payment', $columns)) {
        echo "<p>✅ proof_of_payment column exists!</p>";
        
        // Get a sample reservation with proof of payment
        $stmt = $conn->query("SELECT reservation_id, name, proof_of_payment FROM reservations WHERE proof_of_payment IS NOT NULL AND proof_of_payment != '' LIMIT 3");
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Sample Reservations with Proof of Payment</h2>";
        foreach ($reservations as $res) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<p><strong>ID:</strong> {$res['reservation_id']}</p>";
            echo "<p><strong>Name:</strong> {$res['name']}</p>";
            echo "<p><strong>Proof File:</strong> {$res['proof_of_payment']}</p>";
            
            // Check if file exists
            $filePath = "uploads/proof_of_payment/{$res['proof_of_payment']}";
            $absolutePath = __DIR__ . "/uploads/proof_of_payment/{$res['proof_of_payment']}";
            
            echo "<p><strong>Relative Path:</strong> $filePath</p>";
            echo "<p><strong>Absolute Path:</strong> $absolutePath</p>";
            
            if (file_exists($filePath)) {
                echo "<p>✅ File exists (relative path)</p>";
                echo "<img src='$filePath' style='max-width: 200px; border: 1px solid #ccc;' alt='Proof of Payment'>";
            } elseif (file_exists($absolutePath)) {
                echo "<p>✅ File exists (absolute path)</p>";
                echo "<img src='$filePath' style='max-width: 200px; border: 1px solid #ccc;' alt='Proof of Payment'>";
            } else {
                echo "<p>❌ File does not exist</p>";
            }
            
            echo "</div>";
        }
        
        if (empty($reservations)) {
            echo "<p>No reservations found with proof of payment.</p>";
        }
        
    } else {
        echo "<p>❌ proof_of_payment column does not exist!</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

<h2>Directory Check</h2>
<?php
$uploadDir = "uploads/proof_of_payment";
$absoluteUploadDir = __DIR__ . "/uploads/proof_of_payment";

echo "<p><strong>Relative Upload Dir:</strong> $uploadDir</p>";
echo "<p><strong>Absolute Upload Dir:</strong> $absoluteUploadDir</p>";
echo "<p><strong>Relative Dir Exists:</strong> " . (is_dir($uploadDir) ? 'Yes' : 'No') . "</p>";
echo "<p><strong>Absolute Dir Exists:</strong> " . (is_dir($absoluteUploadDir) ? 'Yes' : 'No') . "</p>";

if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    echo "<p><strong>Files in directory:</strong></p>";
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
}
?> 