<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    // Get JSON data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    // Validate required fields
    if (empty($data['product_id'])) {
        throw new Exception('Product ID is required');
    }
    if (empty($data['quantity']) || $data['quantity'] <= 0) {
        throw new Exception('Valid quantity is required');
    }
    // Start transaction
    $pdo->beginTransaction();
    // Get current product information
    $product_query = "SELECT product_id, product, brand, model, stock_quantity FROM products WHERE product_id = ?";
    $product_stmt = $pdo->prepare($product_query);
    $product_stmt->execute([$data['product_id']]);
    $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        throw new Exception('Product not found');
    }
    $current_stock = $product['stock_quantity'];
    $quantity_to_return = $data['quantity'];
    if ($quantity_to_return > $current_stock) {
        throw new Exception('Return quantity cannot exceed current stock');
    }
    $new_stock = $current_stock - $quantity_to_return;
    // Update product stock quantity
    $update_query = "UPDATE products SET stock_quantity = ? WHERE product_id = ?";
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->execute([$new_stock, $data['product_id']]);
    // Log return in returns table (create if not exists)
    $returns_query = "
        INSERT INTO returns (
            product_id, quantity, previous_stock, new_stock,
            created_by, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())
    ";
    $notes = $data['notes'] ?: 'Returned item - stock reduced';
    $returns_stmt = $pdo->prepare($returns_query);
    $returns_stmt->execute([
        $data['product_id'],
        $quantity_to_return,
        $current_stock,
        $new_stock,
        'Admin User', // You can get this from session if user is logged in
        $notes
    ]);
    // Commit transaction
    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => "Successfully processed return for {$product['product']} - {$product['brand']} {$product['model']}. Returned {$quantity_to_return} units. New stock: {$new_stock}",
        'product_id' => $data['product_id'],
        'quantity_returned' => $quantity_to_return,
        'previous_stock' => $current_stock,
        'new_stock' => $new_stock
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error in process_return.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 