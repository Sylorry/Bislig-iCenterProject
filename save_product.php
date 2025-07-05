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
    $required_fields = ['category_id', 'product_id', 'brand', 'model', 'storage', 'purchase_price', 'selling_price', 'status', 'stock_quantity'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Check if product_id already exists
    $check_query = "SELECT product_id FROM products WHERE product_id = ?";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute([$data['product_id']]);
    
    if ($check_stmt->fetch()) {
        throw new Exception('Product ID already exists');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert product into products table
    $insert_query = "
        INSERT INTO products (
            category_id, product_id, product, brand, model, 
            storage, purchase_price, selling_price, status, stock_quantity
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ";
    
    $insert_stmt = $pdo->prepare($insert_query);
    $insert_stmt->execute([
        $data['category_id'],
        $data['product_id'],
        $data['category_name'],
        $data['brand'],
        $data['model'],
        $data['storage'],
        $data['purchase_price'],
        $data['selling_price'],
        $data['status'],
        $data['stock_quantity']
    ]);
    
    // Log stock movement for stock in
    $stock_movement_query = "
        INSERT INTO stock_movements (
            product_id, movement_type, quantity, previous_stock, new_stock, 
            created_by, notes
        ) VALUES (?, 'IN', ?, 0, ?, ?, ?)
    ";
    
    $stock_movement_stmt = $pdo->prepare($stock_movement_query);
    $stock_movement_stmt->execute([
        $data['product_id'],
        $data['stock_quantity'],
        $data['stock_quantity'],
        'Admin User', // You can get this from session if user is logged in
        'Initial stock - Product added to inventory via add_products.php'
    ]);
    
    // Handle image uploads
    if (!empty($data['images'])) {
        $upload_dir = 'product_images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        foreach ($data['images'] as $image_key => $image_data) {
            if ($image_data && $image_data !== 'null') {
                // Extract base64 data
                $base64_data = $image_data;
                if (strpos($base64_data, 'data:image/') === 0) {
                    $base64_data = substr($base64_data, strpos($base64_data, ',') + 1);
                }
                
                $image_data_decoded = base64_decode($base64_data);
                $image_number = substr($image_key, -1); // Get the number from image1, image2, etc.
                $filename = $data['product_id'] . '_' . $image_number . '_' . time() . '.png';
                $filepath = $upload_dir . $filename;
                
                if (file_put_contents($filepath, $image_data_decoded)) {
                    // Update the image path in database
                    $column_to_update = 'image' . $image_number;
                    $update_image_query = "UPDATE products SET {$column_to_update} = ? WHERE product_id = ?";
                    $update_image_stmt = $pdo->prepare($update_image_query);
                    $update_image_stmt->execute([$filepath, $data['product_id']]);
                }
            }
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Product added successfully and stock movement logged!',
        'product_id' => $data['product_id'],
        'stock_added' => $data['stock_quantity']
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error in save_product.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>