<?php
require_once 'db.php';
require_once 'functions.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    throw new Exception('Invalid product ID');
}

$productId = $_GET['product_id'];
$conn = getDBConnection();

// Add debug logging
error_log("Fetching product details for ID: " . $productId);

// Get the product directly - simplified query
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = :product_id");
$stmt->bindParam(':product_id', $productId, PDO::PARAM_STR);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        error_log("Product not found for ID: " . $productId);
        throw new Exception('Product not found');
    }

    // Add debug logging
    error_log("Product found: " . json_encode($product));
    
    // Output the product as JSON
    echo json_encode($product);
    
} catch (Exception $e) {
    error_log("Error in get_product_details.php: " . $e->getMessage());
    // Return error as JSON
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>