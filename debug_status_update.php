<?php
// Debug script to test status update functionality
require_once 'db.php';

echo "<h1>Status Update Debug</h1>";

// Get a sample reservation to test with
$stmt = $conn->query("SELECT reservation_id, name, status FROM reservations ORDER BY reservation_date DESC LIMIT 1");
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    echo "<p>No reservations found in database.</p>";
    exit;
}

echo "<h2>Testing with Reservation:</h2>";
echo "<p><strong>ID:</strong> " . $reservation['reservation_id'] . "</p>";
echo "<p><strong>Name:</strong> " . $reservation['name'] . "</p>";
echo "<p><strong>Current Status:</strong> " . $reservation['status'] . "</p>";

// Test the update functionality
$reservation_id = $reservation['reservation_id'];
$current_status = $reservation['status'];
$new_status = ($current_status === 'completed') ? 'pending' : 'completed';

echo "<h2>Testing Update:</h2>";
echo "<p>Attempting to change status from '$current_status' to '$new_status'</p>";

// Simulate the POST request
$_POST['reservation_id'] = $reservation_id;
$_POST['status'] = $new_status;

// Capture output
ob_start();

// Include the update script
include 'update_status.php';

$output = ob_get_clean();

echo "<h3>Update Result:</h3>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Check the result
if (strpos($output, 'success') !== false) {
    echo "<p style='color: green;'>✅ Update successful!</p>";
    
    // Verify the change
    $stmt = $conn->prepare("SELECT status FROM reservations WHERE reservation_id = ?");
    $stmt->execute([$reservation_id]);
    $updated_status = $stmt->fetchColumn();
    
    echo "<p><strong>New Status:</strong> " . $updated_status . "</p>";
    
    if (strtolower(trim($updated_status)) === strtolower(trim($new_status))) {
        echo "<p style='color: green;'>✅ Status correctly updated in database!</p>";
    } else {
        echo "<p style='color: red;'>❌ Status mismatch! Expected: $new_status, Got: $updated_status</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Update failed!</p>";
}

// Show recent error logs
echo "<h2>Recent Error Logs:</h2>";
$log_file = ini_get('error_log');
if ($log_file && file_exists($log_file)) {
    $logs = file_get_contents($log_file);
    $lines = explode("\n", $logs);
    $recent_lines = array_slice($lines, -20); // Last 20 lines
    
    echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 300px; overflow-y: auto;'>";
    foreach ($recent_lines as $line) {
        if (strpos($line, 'update_status.php') !== false) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>Error log not found or not accessible.</p>";
}
?> 