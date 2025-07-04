<?php
require_once 'db.php';

try {
    $pdo = getConnection();
    
    // Query to get out of stock products (stock_quantity = 0)
    $query = "SELECT product_id, product, brand, model, storage, stock_quantity 
              FROM products 
              WHERE (archived IS NULL OR archived = 0) AND stock_quantity = 0 
              ORDER BY product_id DESC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($products);
    
} catch (Exception $e) {
    // Return a JSON error message
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 