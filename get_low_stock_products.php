<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT product_id, product, brand, model, storage, stock_quantity FROM products WHERE stock_quantity <= 5 AND stock_quantity > 0 AND (archived IS NULL OR archived = 0) ORDER BY stock_quantity ASC, product_id DESC");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([]);
} 