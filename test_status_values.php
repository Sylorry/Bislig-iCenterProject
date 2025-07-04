<?php
// Test script to check status values in the database
require_once 'db.php';

try {
    echo "<h1>Status Values Check</h1>";
    
    // Get all unique status values
    $stmt = $conn->query("SELECT DISTINCT status FROM reservations ORDER BY status");
    $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Unique Status Values in Database:</h2>";
    echo "<ul>";
    foreach ($statuses as $status) {
        $count = $conn->query("SELECT COUNT(*) FROM reservations WHERE status = '$status'")->fetchColumn();
        echo "<li><strong>$status</strong> - $count reservations</li>";
    }
    echo "</ul>";
    
    // Show sample reservations with their status
    echo "<h2>Sample Reservations with Status:</h2>";
    $stmt = $conn->query("SELECT reservation_id, name, status, reservation_date FROM reservations ORDER BY reservation_date DESC LIMIT 10");
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Status</th><th>Date</th></tr>";
    foreach ($reservations as $res) {
        echo "<tr>";
        echo "<td>" . $res['reservation_id'] . "</td>";
        echo "<td>" . $res['name'] . "</td>";
        echo "<td>" . $res['status'] . "</td>";
        echo "<td>" . $res['reservation_date'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?> 