<?php
ob_start();

// Custom error handler for fatal errors
function handleFatalError() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Fatal error: ' . $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        exit;
    }
}
register_shutdown_function('handleFatalError');

// Simple test endpoint to check JSON response
if (isset($_GET['test']) && $_GET['test'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'JSON test successful']);
    exit;
}

// Simple test endpoint for reservation
if (isset($_GET['test']) && $_GET['test'] === 'reservation') {
    header('Content-Type: application/json');
    try {
        // Test basic functionality
        $testData = [
            'product_ids' => ['IPHONE_XR_128GB'], // Use string product ID
            'name' => 'Test User',
            'contact_number' => '09123456789',
            'address' => 'Test Address',
            'email' => 'test@example.com',
            'reservation_fee' => '500',
            'proof_of_payment' => ''
        ];
        
        echo json_encode([
            'success' => true,
            'message' => 'Reservation test successful',
            'data' => $testData
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Test error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Test endpoint to check products in database
if (isset($_GET['test']) && $_GET['test'] === 'products') {
    header('Content-Type: application/json');
    try {
        require_once 'db.php';
        $conn = getDBConnection();
        
        $stmt = $conn->query("SELECT product_id, product, brand, model, selling_price FROM products LIMIT 10");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Products found',
            'products' => $products,
            'count' => count($products)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    exit;
}

// API endpoint for handling reservations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear any previous output
    ob_clean();
    header('Content-Type: application/json');
    
    // Enable error logging
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    try {
        // Log the incoming request
        error_log("=== RESERVATION REQUEST STARTED ===");
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
        
        // Check if required files exist
        if (!file_exists('db.php')) {
            throw new Exception('Database configuration file not found');
        }
        if (!file_exists('functions.php')) {
            throw new Exception('Functions file not found');
        }
        
        require_once 'db.php';
        require_once 'functions.php';
        
        $json = file_get_contents('php://input');
        error_log("Raw input received: " . substr($json, 0, 500) . "...");
        
        if (empty($json)) {
            throw new Exception('No JSON data received');
        }
        
        $data = json_decode($json, true);
        
        if (!$data) {
            error_log("JSON decode failed: " . json_last_error_msg());
            throw new Exception('Invalid JSON data received: ' . json_last_error_msg());
        }
        
        error_log("Decoded data: " . print_r($data, true));

        // Sanitize and validate input data
        $sanitized_data = array();
        $sanitized_data['product_ids'] = $data['product_ids']; // Keep as strings, don't convert to int
        
        // Filter out invalid product IDs (empty or null)
        $sanitized_data['product_ids'] = array_filter($sanitized_data['product_ids'], function($id) {
            return !empty($id) && $id !== null;
        });
        
        // Re-index the array
        $sanitized_data['product_ids'] = array_values($sanitized_data['product_ids']);
        
        $sanitized_data['name'] = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $sanitized_data['contact_number'] = filter_var($data['contact_number'], FILTER_SANITIZE_STRING);
        $sanitized_data['address'] = filter_var($data['address'], FILTER_SANITIZE_STRING);
        $sanitized_data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $sanitized_data['proof_of_payment'] = isset($data['proof_of_payment']) ? $data['proof_of_payment'] : ''; // Handle separately due to base64

        // Validate product selection count (1-5 products)
        if (count($sanitized_data['product_ids']) < 1 || count($sanitized_data['product_ids']) > 5) {
            throw new Exception("Please select between 1 and 5 products for reservation");
        }

        // Additional validation
        if (!filter_var($sanitized_data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        if (!preg_match('/^[0-9]{11}$/', $sanitized_data['contact_number'])) {
            throw new Exception("Invalid contact number format");
        }

        $conn = getDBConnection();
        if (!$conn) {
            throw new Exception('Database connection failed');
        }

        // Check if all products exist
        $placeholders = str_repeat('?,', count($sanitized_data['product_ids']) - 1) . '?';
        error_log("Product IDs being checked: " . print_r($sanitized_data['product_ids'], true));
        error_log("SQL placeholders: " . $placeholders);
        
        $checkProduct = $conn->prepare("SELECT product_id, selling_price FROM products WHERE product_id IN ($placeholders)");
        if (!$checkProduct) {
            throw new Exception("Error preparing product check");
        }
        
        $checkProduct->execute($sanitized_data['product_ids']);
        $result = $checkProduct->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Found products in database: " . print_r($result, true));
        error_log("Expected count: " . count($sanitized_data['product_ids']) . ", Found count: " . count($result));
        
        if (count($result) !== count($sanitized_data['product_ids'])) {
            // Find which products are missing
            $found_ids = array_column($result, 'product_id');
            $missing_ids = array_diff($sanitized_data['product_ids'], $found_ids);
            error_log("Missing product IDs: " . print_r($missing_ids, true));
            throw new Exception("One or more selected products are invalid. Missing IDs: " . implode(', ', $missing_ids));
        }

        // Check if any product requires payment (above 1000)
        $paymentRequired = false;
        $productsAbove1000 = [];
        foreach ($result as $row) {
            if ($row['selling_price'] > 1000) {
                $paymentRequired = true;
                $productsAbove1000[] = $row;
            }
        }
        $checkProduct->closeCursor();

        // Validate required fields (after determining if payment is required)
        $required_fields = ['product_ids', 'name', 'contact_number', 'address', 'email'];
        if ($paymentRequired) {
            $required_fields[] = 'proof_of_payment';
        }
        
        foreach ($required_fields as $field) {
            if (empty($sanitized_data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Validate reservation fee if payment is required
        if ($paymentRequired) {
            if (!isset($data['reservation_fee']) || empty($data['reservation_fee'])) {
                throw new Exception("Reservation fee is required for products above ₱1,000");
            }
            
            $reservationFee = floatval($data['reservation_fee']);
            $countAbove1000 = count($productsAbove1000);
            $minPayment = $countAbove1000 * 500; // ₱500 minimum per item
            $totalAmount = $countAbove1000 * 1000; // ₱1,000 total per item
            
            if ($reservationFee < $minPayment) {
                throw new Exception("Minimum reservation fee is ₱" . number_format($minPayment, 2) . " (₱500 per product × {$countAbove1000} products)");
            }
            
            if ($reservationFee > $totalAmount) {
                throw new Exception("Reservation fee cannot exceed ₱" . number_format($totalAmount, 2) . " (₱1,000 per product × {$countAbove1000} products)");
            }
        }

        // Check for existing pending reservations
        $checkReservation = $conn->prepare("
            SELECT reservation_id 
            FROM reservations 
            WHERE email = ? 
            AND status = 'pending' 
            AND reservation_date >= CURDATE() - INTERVAL 7 DAY
        ");
        
        if (!$checkReservation) {
            throw new Exception("Error preparing reservation check");
        }
        
        $checkReservation->execute([$sanitized_data['email']]);
        $reservationResult = $checkReservation->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($reservationResult) > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'You already have a pending reservation. Please wait for it to be processed or contact support.',
                'show_modal' => 'existing_reservation'
            ]);
            exit;
        }
        $checkReservation->closeCursor();

        // Get product details for all selected products
        $placeholders = str_repeat('?,', count($sanitized_data['product_ids']) - 1) . '?';
        $getProductDetails = $conn->prepare("
            SELECT product_id, product, brand, model, selling_price, image1 
            FROM products 
            WHERE product_id IN ($placeholders)
        ");
        
        if (!$getProductDetails) {
            throw new Exception("Error preparing product details query");
        }
        
        $getProductDetails->execute($sanitized_data['product_ids']);
        $productDetails = $getProductDetails->fetchAll(PDO::FETCH_ASSOC);
        $getProductDetails->closeCursor();

        // Calculate reservation fee based on products above ₱1,000
        $reservationFee = 0;
        $productsAbove1000 = 0;
        foreach ($productDetails as $product) {
            if ($product['selling_price'] > 1000) {
                $productsAbove1000++;
            }
        }
        $reservationFee = $productsAbove1000 * 500; // ₱500 per product above ₱1,000

        // Prepare product data for insertion
        $productData = [
            'product_id_1' => null, 'product_id_2' => null, 'product_id_3' => null, 'product_id_4' => null, 'product_id_5' => null,
            'product_name_1' => null, 'product_name_2' => null, 'product_name_3' => null, 'product_name_4' => null, 'product_name_5' => null,
            'product_brand_1' => null, 'product_brand_2' => null, 'product_brand_3' => null, 'product_brand_4' => null, 'product_brand_5' => null,
            'product_model_1' => null, 'product_model_2' => null, 'product_model_3' => null, 'product_model_4' => null, 'product_model_5' => null,
            'product_price_1' => null, 'product_price_2' => null, 'product_price_3' => null, 'product_price_4' => null, 'product_price_5' => null
        ];

        // Fill in product data for selected products
        for ($i = 0; $i < count($productDetails) && $i < 5; $i++) {
            $product = $productDetails[$i];
            $index = $i + 1;
            
            $productData["product_id_$index"] = $product['product_id'];
            $productData["product_name_$index"] = $product['product'];
            $productData["product_brand_$index"] = $product['brand'];
            $productData["product_model_$index"] = $product['model'];
            $productData["product_price_$index"] = $product['selling_price'];
        }

        // Insert single reservation with all products
        $stmt = $conn->prepare("
            INSERT INTO reservations (
                name,
                contact_number,
                address,
                email,
                reservation_date,
                reservation_time,
                status,
                proof_of_payment,
                reservation_fee,
                remaining_reservation_fee,
                product_count,
                product_id_1, product_id_2, product_id_3, product_id_4, product_id_5,
                product_name_1, product_name_2, product_name_3, product_name_4, product_name_5,
                product_brand_1, product_brand_2, product_brand_3, product_brand_4, product_brand_5,
                product_model_1, product_model_2, product_model_3, product_model_4, product_model_5,
                product_price_1, product_price_2, product_price_3, product_price_4, product_price_5
            ) VALUES (
                ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?
            )
        ");
        
        if (!$stmt) {
            throw new Exception("Database prepare error");
        }
        
        // Format date and time
        $reservation_date = date('Y-m-d');
        $reservation_time = date('H:i:s');
        
        // Calculate remaining reservation fee
        $totalProductValue = 0;
        foreach ($productDetails as $product) {
            $totalProductValue += $product['selling_price'];
        }
        $remainingReservationFee = $totalProductValue - $reservationFee;
        
        $params = [
            $sanitized_data['name'],
            $sanitized_data['contact_number'],
            $sanitized_data['address'],
            $sanitized_data['email'],
            $reservation_date,
            $reservation_time,
            $sanitized_data['proof_of_payment'],
            $reservationFee,
            $remainingReservationFee,
            count($sanitized_data['product_ids']),
            // Product IDs
            $productData['product_id_1'], $productData['product_id_2'], $productData['product_id_3'], $productData['product_id_4'], $productData['product_id_5'],
            // Product names
            $productData['product_name_1'], $productData['product_name_2'], $productData['product_name_3'], $productData['product_name_4'], $productData['product_name_5'],
            // Product brands
            $productData['product_brand_1'], $productData['product_brand_2'], $productData['product_brand_3'], $productData['product_brand_4'], $productData['product_brand_5'],
            // Product models
            $productData['product_model_1'], $productData['product_model_2'], $productData['product_model_3'], $productData['product_model_4'], $productData['product_model_5'],
            // Product prices
            $productData['product_price_1'], $productData['product_price_2'], $productData['product_price_3'], $productData['product_price_4'], $productData['product_price_5']
        ];
        
        if (!$stmt->execute($params)) {
            throw new Exception("Database execute error");
        }
        
        $reservation_id = $conn->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Reservation created successfully',
            'reservation_id' => $reservation_id,
            'product_count' => count($sanitized_data['product_ids']),
            'reservation_fee' => $reservationFee,
            'remaining_fee' => $remainingReservationFee
        ]);
        
        $stmt->closeCursor();
        $conn = null; // Close PDO connection
        error_log("=== RESERVATION SUCCESSFUL ===");
        exit;
        
    } catch (Exception $e) {
        // Log the error
        error_log("=== RESERVATION ERROR ===");
        error_log("Error message: " . $e->getMessage());
        error_log("Error file: " . $e->getFile() . ":" . $e->getLine());
        error_log("Error trace: " . $e->getTraceAsString());
        error_log("Data: " . json_encode($data ?? []));
        error_log("Time: " . date('Y-m-d H:i:s'));
        error_log("IP: " . $_SERVER['REMOTE_ADDR']);
        error_log("User Agent: " . $_SERVER['HTTP_USER_AGENT']);
        error_log("----------------------------------------");

        // Clear any output buffer
        ob_clean();
        
        // Ensure proper JSON response
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        
        $json_response = json_encode($response);
        if ($json_response === false) {
            error_log("JSON encode error: " . json_last_error_msg());
            $json_response = json_encode([
                'success' => false,
                'message' => 'Server error occurred'
            ]);
        }
        
        echo $json_response;
        exit;
    }
}

// Ensure no further output for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exit;
}

require_once 'functions.php';
require_once 'db.php';

// Get existing reservations for display
$conn = getConnection();
$query = "SELECT * FROM reservations ORDER BY reservation_date DESC, reservation_time DESC";
$stmt = $conn->query($query);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations - BISLIG iCENTER</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Roboto&family=Roboto+Slab&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="kiosk.css">
    <style>
        .reservations-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Navigation Menu Styles */
        .menu-wrapper ul {
            display: flex;
            gap: 24px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .menu-wrapper ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .menu-wrapper ul li a:hover {
            color: #007dd1;
            background: rgba(255, 255, 255, 0.1);
        }

        .menu-wrapper ul li a i {
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .menu-wrapper ul li a:hover i {
            color: #007dd1;
        }

        .nav-reservations {
            position: relative;
        } 

        .nav-reservations .notification-count {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: #dc3545; /* Red color for notification */
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
            line-height: 14px;
            border: 1px solid #fff; /* Optional: white border */
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        .reservation-form {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            padding: 48px;
            margin-bottom: 40px;
            border: 2px solid #000;
            transition: all 0.3s ease;
            box-sizing: border-box;
            padding-left: 32px;
            padding-right: 32px;
        }

        .reservation-form:hover {
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .reservation-form h2 {
            color: #000;
            font-size: 32px;
            margin-bottom: 40px;
            text-align: center;
            font-family: 'Roboto Slab', serif;
            position: relative;
            padding-bottom: 16px;
        }

        .reservation-form h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #007dd1, #005fa3);
            border-radius: 2px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr)); /* Prevents overflow */
            gap: 48px; /* or 80px if you want, but 48px is more balanced */
            align-items: center;
        }
        @media (max-width: 1200px) {
            .form-row {
                gap: 32px;
            }
        }
        @media (max-width: 900px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        .form-group {
            max-width: 100%;
            margin-bottom: 40px; /* Increased from 32px for more space */
        }

        .form-group.full-width {
            grid-column: 1 / -1;
            margin-top: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 14px; /* Slightly increased for clarity */
            font-weight: 600;
            color: #333;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            max-width: 100%;
            box-sizing: border-box;
            width: 100%;
            padding: 18px 22px; /* Slightly increased padding */
            border: 2px solid #000;
            border-radius: 16px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            margin-bottom: 14px; /* Increased from 8px for more space between stacked fields */
        }

        .form-group input[readonly] {
            background: #f0f0f0;
            cursor: not-allowed;
        }

        .form-group input[type="number"] {
            font-family: 'Roboto', sans-serif;
        }

        .form-group input[type="number"]::-webkit-inner-spin-button,
        .form-group input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .form-group input[type="number"] {
            -moz-appearance: textfield;
        }

        .form-group input:hover,
        .form-group textarea:hover,
        .form-group select:hover {
            border-color: #000;
            background: #fff;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #007dd1;
            outline: 2px solid #007dd1;
            outline-offset: 2px;
            background: #fff;
            box-shadow: none;
        }

        .form-group input.error,
        .form-group textarea.error,
        .form-group select.error {
            border-color: #dc3545 !important;
            background-color: #fff5f5;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.25);
        }

        .form-group input.error:focus,
        .form-group textarea.error:focus,
        .form-group select.error:focus {
            border-color: #dc3545 !important;
            outline: 2px solid #dc3545 !important;
            background-color: #fff5f5;
        }

        .form-group textarea {
            min-height: 140px;
            resize: vertical;
            line-height: 1.6;
        }

        .form-group input[type="file"] {
            padding: 16px;
            background: #f8f9fa;
            border: 2px dashed #000;
            cursor: pointer;
            border-radius: 16px;
            transition: all 0.3s ease;
            margin-top: 8px;
        }

        .form-group input[type="file"]:hover {
            border-color: #000;
            background: #f0f7ff;
        }

        .form-group input[type="file"]::file-selector-button {
            padding: 12px 24px;
            border-radius: 12px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            margin-right: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-group input[type="file"]::file-selector-button:hover {
            background: #333;
        }

        .submit-btn {
            background: #000;
            color: #fff;
            border: none;
            border-radius: 16px;
            padding: 20px 40px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 32px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .submit-btn:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        #selectedProductDisplay {
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            padding: 32px;
            margin-bottom: 40px;
            position: relative;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        #selectedProductDisplay button {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            color: #666;
            font-size: 28px;
            cursor: pointer;
            padding: 8px;
            transition: all 0.3s ease;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #selectedProductDisplay button:hover {
            background: rgba(0, 0, 0, 0.1);
            color: #d32f2f;
        }

        #selectedProductDisplay button:active {
            transform: scale(0.95);
        }

        #selectedProductDisplay .product-display-content {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        #selectedProductDisplay img {
            width: 320px;
            height: 320px;
            object-fit: contain;
            border-radius: 12px;
            background: white;
            padding: 24px;
            transition: transform 0.3s ease;
        }

        #selectedProductDisplay img:hover {
            transform: scale(1.02);
        }

        #selectedProductDisplay .product-info {
            flex: 1;
        }

        #selectedProductDisplay h3 {
            margin: 0 0 16px 0;
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }

        #selectedProductDisplay p {
            margin: 0 0 12px 0;
            color: #666;
            font-size: 18px;
            line-height: 1.4;
        }

        #selectedProductDisplay .product-price {
            color: #000;
            font-weight: bold;
            font-size: 24px;
            margin-top: 16px;
        }

        .product-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 32px;
            margin-top: 20px;
            max-height: 600px;
            overflow-y: auto;
            padding: 24px;
            border: 2px solid #000;
            border-radius: 20px;
            background: #f8f9fa;
        }

        .product-option {
            background: #fff;
            border: 2px solid #000;
            border-radius: 16px;
            padding: 32px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-height: 450px;
        }

        .product-option:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            border-color: #000;
        }

        .product-option.selected {
            border-color: #000;
            background: #f0f7ff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .product-option img {
            width: 200px;
            height: 200px;
            object-fit: contain;
            margin-bottom: 24px;
            border-radius: 12px;
            background: #fff;
            padding: 16px;
            transition: transform 0.3s ease;
        }

        .product-option:hover img {
            transform: scale(1.05);
        }

        .product-info {
            width: 100%;
        }

        .product-info h4 {
            margin: 0 0 16px 0;
            font-size: 22px;
            color: #333;
            font-weight: 600;
        }

        .product-brand {
            color: #666;
            font-size: 18px;
            margin: 0 0 8px 0;
        }

        .product-model {
            color: #888;
            font-size: 16px;
            margin: 0 0 16px 0;
        }

        .product-price {
            color: #000;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        #imagePreview {
            margin-top: 20px;
            text-align: center;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 16px;
            border: 2px solid #e0e0e0;
        }

        #imagePreview img {
            max-width: 240px;
            max-height: 240px;
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 16px;
        }

        #imagePreview button {
            margin-top: 16px;
            padding: 12px 24px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        #imagePreview button:hover {
            background: #ff2222;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 68, 68, 0.2);
        }

        @media (max-width: 768px) {
            .reservation-form {
                padding: 32px 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .product-selector {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 24px;
                padding: 16px;
            }

            .product-option {
                padding: 24px;
                min-height: 400px;
            }

            .product-option img {
                width: 160px;
                height: 160px;
                margin-bottom: 20px;
            }

            .product-info h4 {
                font-size: 20px;
                margin-bottom: 12px;
            }

            .product-brand {
                font-size: 16px;
            }

            .product-model {
                font-size: 14px;
            }

            .product-price {
                font-size: 20px;
            }

            #selectedProductDisplay {
                padding: 24px;
            }

            #selectedProductDisplay .product-display-content {
                flex-direction: column;
                gap: 24px;
                text-align: center;
            }

            #selectedProductDisplay img {
                width: 280px;
                height: 280px;
                padding: 20px;
            }

            #selectedProductDisplay h3 {
                font-size: 24px;
            }

            #selectedProductDisplay p {
                font-size: 16px;
            }

            #selectedProductDisplay .product-price {
                font-size: 20px;
            }

            .form-group {
                margin-bottom: 28px; /* More space on mobile too */
            }

            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 16px 14px;
                font-size: 15px;
                margin-bottom: 12px; /* More space on mobile */
            }

            .submit-btn {
                padding: 16px 32px;
                font-size: 16px;
            }
        }

        .payment-display {
            position: relative;
        }

        .fixed-payment {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            border: 1px solid #ddd;
        }

        .payment-message {
            margin-top: 8px;
            font-size: 14px;
            color: #666;
            font-style: italic;
        }

        .payment-message.error {
            color: #c62828;
        }

        .balance-display {
            position: relative;
        }

        .balance-message {
            margin-top: 8px;
            font-size: 14px;
            color: #666;
            font-style: italic;
        }

        .balance-message.positive {
            color: #2e7d32;
        }

        .balance-message.zero {
            color: #1976d2;
        }

        .balance-message.negative {
            color: #c62828;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            overflow-y: auto;
            padding: 20px;
        }

        .modal.show {
            opacity: 1;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 0;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            transform: translateY(-20px);
            transition: transform 0.3s ease-out;
            position: relative;
        }

        .modal.show .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #eee;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h2 {
            margin: 0;
            color: #000;
            font-size: 24px;
            text-align: center;
            flex: 1;
        }

        .close {
            position: relative;
            right: 0;
            top: 0;
            font-size: 28px;
            font-weight: bold;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: transparent;
            border: none;
            padding: 0;
        }

        .close:hover {
            color: #000;
            background-color: rgba(0, 0, 0, 0.1);
        }

        .close:active {
            transform: scale(0.95);
        }

        .modal-body {
            padding: 32px 24px;
            text-align: center;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 24px;
            border-top: 1px solid #eee;
            text-align: center;
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        .modal-btn {
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            min-width: 120px;
        }

        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .modal-btn:active {
            transform: translateY(0);
        }

        .success-icon {
            font-size: 64px;
            color: #4CAF50;
            margin-bottom: 24px;
            animation: scaleIn 0.5s ease-out;
        }

        .warning-icon {
            font-size: 64px;
            color: #f57c00;
            margin-bottom: 24px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Mobile Responsive Modal */
        @media (max-width: 768px) {
            .modal-content {
                margin: 10% auto;
                width: 95%;
            }

            .modal-header h2 {
                font-size: 20px;
            }

            .modal-body {
                padding: 24px 16px;
            }

            .modal-footer {
                padding: 16px;
                flex-direction: column;
            }

            .modal-btn {
                width: 100%;
                margin: 8px 0;
            }
        }

        /* Enhanced Modal Styles */
        .product-removal-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
            padding: 20px;
        }

        .product-image-container {
            width: 200px;
            height: 200px;
            border-radius: 12px;
            overflow: hidden;
            background: #f8f9fa;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .removal-product-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .product-details {
            text-align: center;
            width: 100%;
        }

        .product-details h3 {
            margin: 0 0 12px 0;
            color: #333;
            font-size: 24px;
            font-weight: 600;
        }

        .product-details p {
            margin: 8px 0;
            color: #666;
            font-size: 16px;
        }

        .product-details p:last-child {
            color: #000;
            font-weight: 600;
            font-size: 20px;
            margin-top: 16px;
        }

        .removal-message {
            text-align: center;
            padding: 20px;
            background: #fff3e0;
            border-radius: 12px;
            width: 100%;
            margin-top: 16px;
        }

        .removal-message p {
            margin: 12px 0 0 0;
            color: #333;
            font-size: 16px;
        }

        .warning-icon {
            font-size: 48px;
            color: #f57c00;
            margin-bottom: 16px;
        }

        .modal-btn.cancel-btn {
            background: #f5f5f5;
            color: #333;
        }

        .modal-btn.confirm-btn {
            background: #dc3545;
            color: white;
        }

        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .modal-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .modal-content {
                margin: 10% auto;
                width: 95%;
            }

            .product-image-container {
                width: 160px;
                height: 160px;
            }

            .product-details h3 {
                font-size: 20px;
            }

            .product-details p {
                font-size: 14px;
            }

            .removal-message p {
                font-size: 14px;
            }
        }

        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border: 2px solid #000;
            margin-bottom: 24px;
        }

        .form-padding {
            padding-left: 20px;
            padding-right: 20px;
        }

        .category-heading {
            display: block;
            background: #007dd1;
            color: #fff !important;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            margin-top: 24px;
            margin-bottom: 0;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .category-heading:hover, .category-heading.active {
            background: #005fa3;
            color: #fff;
        }

        /* Enhanced Form Styles */
        .form-section {
            transition: all 0.3s ease;
        }

        .form-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
        }

        .form-section h3 {
            position: relative;
        }

        .form-section h3::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #007dd1, #005fa3);
            border-radius: 2px;
        }

        /* Input field enhancements */
        .form-group input,
        .form-group textarea {
            transition: all 0.3s ease;
        }

        .form-group input:hover,
        .form-group textarea:hover {
            border-color: #007dd1;
            box-shadow: 0 4px 12px rgba(0, 125, 209, 0.1);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #007dd1;
            box-shadow: none;
            outline: 2px solid #007dd1;
            outline-offset: 2px;
            transform: translateY(-1px);
        }

        /* File input styling */
        .form-group input[type="file"] {
            position: relative;
            overflow: hidden;
        }

        .form-group input[type="file"]:hover {
            border-color: #007dd1;
            background: #f0f7ff;
        }

        .form-group input[type="file"]::file-selector-button {
            background: linear-gradient(135deg, #007dd1, #005fa3);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-group input[type="file"]::file-selector-button:hover {
            background: linear-gradient(135deg, #005fa3, #004080);
            transform: translateY(-1px);
        }

        /* Checkbox styling */
        .agreement-container {
            transition: all 0.3s ease;
        }

        .agreement-container:hover {
            background: #f0f7ff;
            border-color: #007dd1;
        }

        .agreement-container input[type="checkbox"] {
            accent-color: #007dd1;
        }

        .agreement-container input[type="checkbox"]:checked {
            transform: scale(1.1);
        }

        /* Submit button enhancements */
        .submit-btn {
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0, 125, 209, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        /* Field message styling */
        .field-message {
            transition: color 0.3s ease;
        }

        /* Image preview enhancements */
        #imagePreview {
            transition: all 0.3s ease;
        }

        #imagePreview img {
            transition: transform 0.3s ease;
        }

        #imagePreview img:hover {
            transform: scale(1.05);
        }

        #imagePreview button {
            transition: all 0.3s ease;
        }

        #imagePreview button:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        /* Responsive form adjustments */
        @media (max-width: 768px) {
            .form-section {
                padding: 24px 20px;
            }

            .form-section h3 {
                font-size: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .submit-btn {
                padding: 16px 32px;
                font-size: 16px;
            }
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 32px;
            gap: 16px;
        }
        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .step.active {
            background: linear-gradient(135deg, #007dd1, #005fa3);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 125, 209, 0.3);
            border: none;
        }
        .step:not(.active) {
            background: #f8f9fa;
            color: #666;
            border: 2px solid #e0e0e0;
        }
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
        }

        @media (max-width: 768px) {
            .step {
                font-size: 14px;
                padding: 10px 10px;
            }
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Selection Limit Modal Specific Styles */
        #selectionLimitModal .modal-content {
            max-width: 500px;
        }

        #selectionLimitModal .modal-body {
            padding: 32px 24px;
        }

        #selectionLimitModal .exclamation-triangle {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        #selectionLimitModal .info-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #007dd1;
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(0, 125, 209, 0.1);
        }

        #selectionLimitModal .info-box p {
            margin: 0;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #selectionLimitModal .info-box i {
            color: #007dd1;
            margin-right: 8px;
            font-size: 16px;
        }

        #selectionLimitModal .current-count {
            color: #007dd1;
            font-weight: 700;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="menu-bar">
            <img src="images/icenter.png" alt="Logo" class="logo" />
            <div class="menu-wrapper">
                <ul>
                    <li><a href="kiosk.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="kiosk.php#container3"><i class="fas fa-mobile-alt"></i> Our Products</a></li>
                    <li class="nav-reservations">
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <div class="reservations-container">
        <div class="reservation-form">
            <h2>Make a Reservation</h2>
            
            <div class="reservation-notice" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 24px; border-radius: 16px; margin-bottom: 32px; border: 2px solid #007dd1; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                <h3 style="color: #007dd1; margin-bottom: 16px; font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-info-circle" style="color: #007dd1;"></i>
                    How to Reserve Products
                </h3>
                <ol style="margin: 0; padding-left: 20px; color: #333; line-height: 1.6;">
                    <li style="margin-bottom: 12px;">Select 1 to 5 products from the available options below</li>
                    <li style="margin-bottom: 12px;">Fill in your personal details accurately</li>
                    <li style="margin-bottom: 12px;">For products above ₱1,000: Pay reservation fee of ₱1,000 per item</li>
                    <li style="margin-bottom: 12px;">For products ₱1,000 or below: No reservation fee required</li>
                    <li style="margin-bottom: 12px;">Upload proof of payment (only if reservation fee is required)</li>
                    <li style="margin-bottom: 12px;">Agree to the terms and conditions</li>
                    <li>Submit your reservation and wait for confirmation</li>
                </ol>
            </div>

            <!-- Step Indicator -->
            <div class="step-indicator" style="display: flex; justify-content: center; margin-bottom: 32px; gap: 16px;">
                <div class="step active" id="step1-indicator">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Step 1: Select Products</span>
                </div>
                <div class="step" id="step2-indicator">
                    <i class="fas fa-list"></i>
                    <span>Step 2: Confirm Selection</span>
                </div>
                <div class="step" id="step3-indicator">
                    <i class="fas fa-user-edit"></i>
                    <span>Step 3: Personal Details</span>
                </div>
            </div>
            
            <form id="reservationForm" enctype="multipart/form-data">
                <!-- Step 1: Product Selection -->
                <div class="form-step active" id="step1">
                    <div class="form-group full-width" style="margin-bottom: 40px;">
                        <label style="font-size: 20px; font-weight: 700; color: #333; margin-bottom: 20px; display: block;">Select Products for Reservation</label>
                        
                        <!-- Selection Counter -->
                        <div id="selectionCounter" style="background: linear-gradient(135deg, #007dd1, #005fa3); color: white; padding: 16px 24px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0, 125, 209, 0.3);">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <i class="fas fa-shopping-cart" style="font-size: 20px;"></i>
                                <span style="font-size: 18px; font-weight: 600;">Selected Products: <span id="selectedCount">0</span> / 5</span>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;">
                                <span id="selectionStatus">No products selected</span>
                            </div>
                        </div>
                        
                        <div class="product-selector" style="background: #ffffff; border: 2px solid #e0e0e0; border-radius: 16px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                            <?php
                            // First, get all products with their category information (not just available ones)
                            $productsQuery = "SELECT p.*, c.category_name 
                                             FROM products p 
                                             LEFT JOIN categories c ON p.category_id = c.category_id 
                                             ORDER BY p.product, c.category_name";
                            
                            try {
                            $productsStmt = $conn->query($productsQuery);
                            $allProducts = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
                                error_log("Products query executed successfully");
                            } catch (Exception $e) {
                                error_log("Error executing products query: " . $e->getMessage());
                                $allProducts = [];
                            }
                            
                            // Debug: Log all products to see their structure
                            error_log("All products from database: " . print_r($allProducts, true));
                            
                            // Debug: Check the first product's structure
                            if (!empty($allProducts)) {
                                error_log("First product keys: " . print_r(array_keys($allProducts[0]), true));
                                error_log("First product data: " . print_r($allProducts[0], true));
                            } else {
                                error_log("No products found in database");
                                // Check if the query is working
                                $testQuery = "SELECT COUNT(*) as count FROM products";
                                $testStmt = $conn->query($testQuery);
                                $testResult = $testStmt->fetch(PDO::FETCH_ASSOC);
                                error_log("Total products in database: " . $testResult['count']);
                                
                                // Check table structure
                                try {
                                    $columnsQuery = "SHOW COLUMNS FROM products";
                                    $columnsStmt = $conn->query($columnsQuery);
                                    $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
                                    error_log("Products table columns: " . print_r($columns, true));
                                } catch (Exception $e) {
                                    error_log("Error getting table structure: " . $e->getMessage());
                                }
                            }

                            // Group products by product name
                            $productsByProduct = [];
                            foreach ($allProducts as $product) {
                                $productName = $product['product'];
                                if (!isset($productsByProduct[$productName])) {
                                    $productsByProduct[$productName] = [];
                                }
                                $productsByProduct[$productName][] = $product;
                            }

                            $selectedProductIds = isset($_GET['product_ids']) ? explode(',', $_GET['product_ids']) : [];
                            $productIndex = 0;
                            $autoOpenFound = false;
                            
                            if (empty($productsByProduct)) {
                                echo '<div style="color: #666; padding: 40px; text-align: center; background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 12px; font-size: 16px;">';
                                echo '<i class="fas fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 16px; display: block;"></i>';
                                echo 'No products found in the database.';
                                echo '</div>';
                            } else {
                                foreach ($productsByProduct as $productName => $products) {
                                    $productId = 'product_' . $productIndex;
                                    $isIphone = (strtoupper($productName) === 'IPHONE');
                                    $autoOpen = '';
                                    if ($isIphone && !$autoOpenFound) {
                                        $autoOpen = ' auto-open';
                                        $autoOpenFound = true;
                                    }
                                    
                                    echo '<div class="product-category-section" style="margin-bottom: 30px;">';
                                    echo '<h3 class="category-heading' . $autoOpen . '" data-target="' . $productId . '" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px 24px; border-radius: 12px; margin: 0 0 20px 0; font-size: 18px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">';
                                    echo '<span>' . htmlspecialchars($productName) . '</span>';
                                    echo '<span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 14px;">' . count($products) . ' variants</span>';
                                    echo '</h3>';
                                    
                                    echo '<div class="category-products' . $autoOpen . '" id="' . $productId . '" style="display:none;">';
                                    echo '<div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">';
                                    
                                    foreach ($products as $product) {
                                        // Debug: Log product data
                                        error_log("Processing product: " . print_r($product, true));
                                        
                                        $isSelected = in_array($product['product_id'], $selectedProductIds);
                                        $statusClass = ($product['status'] === 'available') ? 'available' : 'unavailable';
                                        $statusColor = ($product['status'] === 'available') ? '#28a745' : '#dc3545';
                                        
                                        // Skip products with invalid product_id
                                        if (empty($product['product_id'])) {
                                            error_log("Skipping product with empty product_id: " . $product['product']);
                                            continue;
                                        }
                                        
                                        echo '<div class="product-card ' . $statusClass . '" style="background: white; border: 2px solid ' . ($isSelected ? '#007dd1' : '#e9ecef') . '; border-radius: 16px; padding: 20px; transition: all 0.3s ease; cursor: pointer; position: relative; box-shadow: 0 4px 12px rgba(0,0,0,0.08);" onclick="toggleProductSelection(this)">';
                                        
                                        // Status badge
                                        echo '<div style="position: absolute; top: 12px; right: 12px; background: ' . $statusColor . '; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase;">' . htmlspecialchars($product['status'] ?: 'Not set') . '</div>';
                                        
                                        // Checkbox
                                        echo '<div style="position: absolute; top: 12px; left: 12px;">';
                                        echo '<input type="checkbox" class="product-checkbox" value="' . htmlspecialchars($product['product_id']) . '" data-selling-price="' . htmlspecialchars($product['selling_price']) . '" ' . ($isSelected ? 'checked' : '') . ' style="width: 20px; height: 20px; cursor: pointer;" onclick="event.stopPropagation();">';
                                        echo '</div>';
                                        
                                        // Product image
                                        echo '<div style="text-align: center; margin: 20px 0;">';
                                        if (!empty($product['image1'])) {
                                            echo '<img src="' . htmlspecialchars($product['image1']) . '" alt="' . htmlspecialchars($product['product']) . '" style="width: 120px; height: 120px; object-fit: contain; border-radius: 12px; background: #f8f9fa; padding: 12px; border: 1px solid #dee2e6;">';
                                        } else {
                                            echo '<div style="width: 120px; height: 120px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 14px; margin: 0 auto;">No Image</div>';
                                        }
                                        echo '</div>';
                                        
                                        // Product details
                                        echo '<div style="text-align: center;">';
                                        echo '<h4 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 600; color: #333;">' . htmlspecialchars($product['product']) . '</h4>';
                                        
                                        if (!empty($product['brand'])) {
                                            echo '<p style="margin: 0 0 4px 0; color: #666; font-size: 14px;"><strong>Brand:</strong> ' . htmlspecialchars($product['brand']) . '</p>';
                                        }
                                        
                                        if (!empty($product['model'])) {
                                            echo '<p style="margin: 0 0 4px 0; color: #666; font-size: 14px;"><strong>Model:</strong> ' . htmlspecialchars($product['model']) . '</p>';
                                        }
                                        
                                        if (!empty($product['category_name'])) {
                                            echo '<p style="margin: 0 0 8px 0; color: #666; font-size: 14px;"><strong>Category:</strong> ' . htmlspecialchars($product['category_name']) . '</p>';
                                        }
                                        
                                        if (!empty($product['selling_price'])) {
                                            echo '<div style="background: linear-gradient(135deg, #007dd1, #005fa3); color: white; padding: 12px; border-radius: 8px; font-weight: bold; font-size: 18px; margin-top: 12px;">₱' . number_format($product['selling_price'], 2) . '</div>';
                                        }
                                        echo '</div>';
                                        
                                        echo '</div>';
                                    }
                                    
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                    $productIndex++;
                                }
                            }
                            ?>
                        </div>
                        <input type="hidden" id="product_ids" name="product_ids" value="<?php echo htmlspecialchars(implode(',', $selectedProductIds)); ?>" required>
                    </div>

                    <div style="text-align: center; margin-top: 40px;">
                        <button type="button" class="next-btn" onclick="nextStep()" style="background: linear-gradient(135deg, #007dd1 0%, #005fa3 100%); color: #fff; border: none; border-radius: 16px; padding: 16px 32px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 8px 24px rgba(0, 125, 209, 0.3);">
                            <i class="fas fa-arrow-right" style="margin-right: 8px;"></i>
                            Next: Confirm Selection
                        </button>
                    </div>
                </div>

                <!-- Step 2: Confirmation List -->
                <div class="form-step" id="step2">
                    <div class="form-section" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e0e0e0; border-radius: 20px; padding: 32px; margin-bottom: 32px; box-shadow: 0 8px 24px rgba(0,0,0,0.08);">
                        <h3 style="color: #333; margin-bottom: 24px; font-size: 22px; font-weight: 700; text-align: center; border-bottom: 3px solid #007dd1; padding-bottom: 12px;">Confirm Your Selected Products</h3>
                        <div id="confirmationList" style="margin-bottom: 24px;"></div>
                        <div style="text-align: center;">
                            <button type="button" class="prev-btn" onclick="prevStep()" style="background: #6c757d; color: #fff; border: none; border-radius: 16px; padding: 16px 32px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3); margin-right: 16px;">
                                <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                                Back to Products
                            </button>
                            <button type="button" class="next-btn" onclick="confirmSelectionAndContinue()" style="background: linear-gradient(135deg, #007dd1 0%, #005fa3 100%); color: #fff; border: none; border-radius: 16px; padding: 16px 32px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 8px 24px rgba(0, 125, 209, 0.3);">
                                <i class="fas fa-check" style="margin-right: 8px;"></i>
                                Confirm & Continue
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Personal Information, Payment, etc. -->
                <div class="form-step" id="step3">
                    <div class="form-section" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e0e0e0; border-radius: 20px; padding: 32px; margin-bottom: 32px; box-shadow: 0 8px 24px rgba(0,0,0,0.08);">
                        <h3 style="color: #333; margin-bottom: 24px; font-size: 22px; font-weight: 700; text-align: center; border-bottom: 3px solid #007dd1; padding-bottom: 12px;">Personal Information</h3>
                        
                        <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 48px; margin-bottom: 24px;">
                            <div class="form-group">
                                <label for="name" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 16px;">
                                    <i class="fas fa-user" style="color: #007dd1; margin-right: 8px;"></i>Full Name
                                </label>
                                <input type="text" id="name" name="name" required pattern="[A-Za-z\s]+" minlength="2" maxlength="100" 
                                    oninput="this.value = this.value.toUpperCase()" 
                                    placeholder="Enter your full name"
                                    style="width: 100%; padding: 16px 20px; border: 2px solid #e0e0e0; border-radius: 12px; font-size: 16px; transition: all 0.3s ease; background: #fff;">
                                <div class="field-message" style="margin-top: 6px; font-size: 14px; color: #666;">Must be a valid name (letters and spaces only, 2-100 characters)</div>
                            </div>
                            <div class="form-group">
                                <label for="contact_number" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 16px;">
                                    <i class="fas fa-phone" style="color: #007dd1; margin-right: 8px;"></i>Contact Number
                                </label>
                                <input type="tel" id="contact_number" name="contact_number" required 
                                    pattern="[0-9]{11}" minlength="11" maxlength="11" 
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)"
                                    placeholder="Enter 11-digit number"
                                    style="width: 100%; padding: 16px 20px; border: 2px solid #e0e0e0; border-radius: 12px; font-size: 16px; transition: all 0.3s ease; background: #fff;">
                                <div class="field-message" style="margin-top: 6px; font-size: 14px; color: #666;">Must be a valid 11-digit phone number (e.g., 09123456789)</div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 24px;">
                            <label for="address" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 16px;">
                                <i class="fas fa-map-marker-alt" style="color: #007dd1; margin-right: 8px;"></i>Complete Address
                            </label>
                            <textarea id="address" name="address" required minlength="10" maxlength="200" 
                                oninput="this.value = this.value.toUpperCase()"
                                placeholder="Enter your complete address"
                                style="width: 100%; padding: 16px 20px; border: 2px solid #e0e0e0; border-radius: 12px; font-size: 16px; transition: all 0.3s ease; background: #fff; min-height: 120px; resize: vertical;"></textarea>
                            <div class="field-message" style="margin-top: 6px; font-size: 14px; color: #666;">Must be a valid address (10-200 characters)</div>
                        </div>

                        <div class="form-group" style="margin-bottom: 24px;">
                            <label for="email" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 16px;">
                                <i class="fas fa-envelope" style="color: #007dd1; margin-right: 8px;"></i>Email Address
                            </label>
                            <input type="email" id="email" name="email" required 
                                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                placeholder="Enter your email address"
                                style="width: 100%; padding: 16px 20px; border: 2px solid #e0e0e0; border-radius: 12px; font-size: 16px; transition: all 0.3s ease; background: #fff;">
                            <div class="field-message" style="margin-top: 6px; font-size: 14px; color: #666;">Must be a valid email address (e.g., name@domain.com)</div>
                        </div>
                    </div>

                    <div class="form-section" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e0e0e0; border-radius: 20px; padding: 32px; margin-bottom: 32px; box-shadow: 0 8px 24px rgba(0,0,0,0.08);">
                        <h3 style="color: #333; margin-bottom: 24px; font-size: 22px; font-weight: 700; text-align: center; border-bottom: 3px solid #007dd1; padding-bottom: 12px;">Payment Information</h3>
                        
                        <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 48px;">
                            <div class="form-group">
                                <label for="down_payment">Reservation Fee (₱500-₱1,000 per item above ₱1,000):</label>
                                <input type="number" id="down_payment" name="down_payment" step="0.01" min="0" readonly>
                                <div id="paymentMessage" class="payment-message"></div>
                                </div>
                            <div class="form-group">
                                <label for="balance">Remaining Balance:</label>
                                <input type="number" id="balance" name="balance" step="0.01" readonly>
                                <div id="balanceMessage" class="balance-message"></div>
                            </div>
                            <div class="form-group">
                                <label for="proof_of_payment">Proof of Payment (Required for items above ₱1,000):</label>
                                <input type="file" id="proof_of_payment" name="proof_of_payment" accept="image/*" onchange="previewImage(this)">
                                <small>Upload a screenshot or photo of your payment receipt (JPG, JPEG, PNG only, max 5MB)</small>
                                <div id="imagePreview" style="display: none; margin-top: 10px;">
                                    <img id="preview" src="#" alt="Preview" style="max-width: 200px; max-height: 200px;">
                                    <button type="button" onclick="removeImage()" style="margin-left: 10px;">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e0e0e0; border-radius: 20px; padding: 32px; margin-bottom: 32px; box-shadow: 0 8px 24px rgba(0,0,0,0.08);">
                        <h3 style="color: #333; margin-bottom: 24px; font-size: 22px; font-weight: 700; text-align: center; border-bottom: 3px solid #007dd1; padding-bottom: 12px;">Terms and Agreement</h3>
                        
                        <div class="form-group" style="margin-top: 32px;">
                            <div class="checkbox-container" style="display: flex; align-items: flex-start; gap: 12px; padding: 20px; background: #f8f9fa; border-radius: 12px; border: 2px solid #e0e0e0;">
                                <input type="checkbox" id="user_agreement" name="user_agreement" required style="margin-top: 4px; transform: scale(1.2);">
                                <label for="user_agreement" style="font-size: 16px; line-height: 1.6; color: #333; cursor: pointer; margin: 0;">
                                    I agree to the <a href="#" onclick="showTermsModal(); return false;" style="color: #007dd1; text-decoration: underline; font-weight: 600;">Terms and Conditions</a> for product reservations. 
                                    I understand that for products above ₱1,000, I must pay a minimum reservation fee of ₱500 per item (up to ₱1,000 per item), and the remaining balance must be paid upon collection within 7 days.
                                </label>
                            </div>
                            <div id="agreementError" style="color: #dc3545; font-size: 14px; margin-top: 8px; display: none;"></div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 40px; gap: 32px; flex-wrap: wrap;">
                        <button type="button" class="prev-btn" onclick="prevStep()" style="background: #6c757d; color: #fff; border: none; border-radius: 16px; padding: 16px 32px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3); min-width: 220px;">
                            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                            Back to Confirmation
                        </button>
                        <button type="submit" class="submit-btn" id="submitBtn" style="background: linear-gradient(135deg, #007dd1 0%, #005fa3 100%); color: #fff; border: none; border-radius: 16px; padding: 20px 48px; font-size: 18px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 8px 24px rgba(0, 125, 209, 0.3); min-width: 220px;">
                            <i class="fas fa-paper-plane" style="margin-right: 12px;"></i>
                            Submit Reservation
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Category toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-open IPHONE or first category
            var autoOpenHeading = document.querySelector('.category-heading.auto-open');
            var autoOpenProducts = document.querySelector('.category-products.auto-open');
            if (autoOpenHeading && autoOpenProducts) {
                autoOpenProducts.style.display = 'block';
                autoOpenHeading.classList.add('active');
            } else {
                // fallback: open first
                var firstHeading = document.querySelector('.category-heading');
                var firstProducts = document.querySelector('.category-products');
                if (firstHeading && firstProducts) {
                    firstProducts.style.display = 'block';
                    firstHeading.classList.add('active');
                }
            }
            
            // Add click event listeners to category headings
            document.querySelectorAll('.category-heading').forEach(function(heading) {
                heading.addEventListener('click', function() {
                    var targetId = this.getAttribute('data-target');
                    
                    // Hide all product lists
                    document.querySelectorAll('.category-products').forEach(function(div) {
                        div.style.display = 'none';
                    });
                    
                    // Remove active class from all headings
                    document.querySelectorAll('.category-heading').forEach(function(h) {
                        h.classList.remove('active');
                    });
                    
                    // Show the selected one
                    var targetDiv = document.getElementById(targetId);
                    if (targetDiv) {
                        targetDiv.style.display = 'block';
                        this.classList.add('active');
                    }
                });
            });

            // Add event listeners to product checkboxes
            document.querySelectorAll('.product-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    console.log('Checkbox changed:', {
                        value: this.value,
                        checked: this.checked,
                        price: this.getAttribute('data-selling-price')
                    });
                    
                    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
                    const currentSelectedCount = selectedCheckboxes.length;
                    
                    // If trying to select and already at max, prevent selection and show modal
                    if (this.checked && currentSelectedCount > 5) {
                        this.checked = false;
                        showSelectionLimitModal();
                        return;
                    }
                    
                    updateSelectedProductIds();
                });
            });
        });

        function updateSelectedProductIds() {
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
            
            // Debug logging
            console.log('updateSelectedProductIds called');
            console.log('Selected checkboxes:', selectedCheckboxes.length);
            console.log('Selected IDs:', selectedIds);
            
            document.getElementById('product_ids').value = selectedIds.join(',');
            
            // Debug the hidden field value
            console.log('Hidden field value:', document.getElementById('product_ids').value);
            
            // Update selection counter
            const selectedCount = selectedIds.length;
            const selectedCountElement = document.getElementById('selectedCount');
            const selectionStatusElement = document.getElementById('selectionStatus');
            const selectionCounter = document.getElementById('selectionCounter');
            
            selectedCountElement.textContent = selectedCount;
            
            // Update status and styling
            if (selectedCount === 0) {
                selectionStatusElement.textContent = 'No products selected';
                selectionCounter.style.background = 'linear-gradient(135deg, #007dd1, #005fa3)';
            } else if (selectedCount === 1) {
                selectionStatusElement.textContent = '1 product selected';
                selectionCounter.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
            } else if (selectedCount >= 2 && selectedCount <= 4) {
                selectionStatusElement.textContent = `${selectedCount} products selected`;
                selectionCounter.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
            } else if (selectedCount === 5) {
                selectionStatusElement.textContent = 'Maximum reached (5 products)';
                selectionCounter.style.background = 'linear-gradient(135deg, #ffc107, #fd7e14)';
            }
            
            // Update payment calculation when products change
            if (selectedCount > 0) {
                validateAndCalculatePayment();
            } else {
                // Reset payment fields when no products selected
                document.getElementById('down_payment').value = '500';
                document.getElementById('balance').value = '';
                document.getElementById('paymentMessage').textContent = '';
                document.getElementById('balanceMessage').textContent = '';
            }
        }

        // Function to toggle product selection when clicking on the product item
        function toggleProductSelection(productCard) {
            const checkbox = productCard.querySelector('.product-checkbox');
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            const currentSelectedCount = selectedCheckboxes.length;
            
            // If trying to select and already at max, prevent selection and show modal
            if (!checkbox.checked && currentSelectedCount >= 5) {
                showSelectionLimitModal();
                return;
            }
            
            checkbox.checked = !checkbox.checked;
            updateSelectedProductIds();
            
            // Update visual feedback for card-based layout
            if (checkbox.checked) {
                productCard.style.borderColor = '#007dd1';
                productCard.style.boxShadow = '0 8px 24px rgba(0, 125, 209, 0.3)';
                productCard.style.transform = 'translateY(-2px)';
            } else {
                productCard.style.borderColor = '#e9ecef';
                productCard.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)';
                productCard.style.transform = 'translateY(0)';
            }
        }

        // On form submit, validate at least one product selected
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            const selectedIds = document.getElementById('product_ids').value;
            if (!selectedIds) {
                e.preventDefault();
                showNoProductSelectedModal();
                return;
            }
            const selectedCount = selectedIds.split(',').filter(id => id.trim() !== '').length;
            if (selectedCount < 1 || selectedCount > 5) {
                e.preventDefault();
                alert('Please select between 1 and 5 products for reservation.');
                return;
            }
        });

        // Multi-step form navigation
        function showStep(step) {
            document.querySelectorAll('.form-step').forEach(function(div, idx) {
                div.classList.remove('active');
            });
            document.getElementById('step' + step).classList.add('active');
            // Update step indicator
            document.getElementById('step1-indicator').classList.toggle('active', step === 1);
            document.getElementById('step2-indicator').classList.toggle('active', step === 2);
            document.getElementById('step3-indicator').classList.toggle('active', step === 3);
        }
        function nextStep() {
            // Validate at least one product selected before moving to next step
            const selectedIds = document.getElementById('product_ids').value;
            if (!selectedIds) {
                showNoProductSelectedModal();
                return;
            }
            const selectedCount = selectedIds.split(',').filter(id => id.trim() !== '').length;
            if (selectedCount < 1 || selectedCount > 5) {
                alert('Please select between 1 and 5 products for reservation.');
                return;
            }
            // Show confirmation step
            populateConfirmationList();
            showStep(2);
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
        function prevStep() {
            // If on confirmation step, go back to product selection
            if (document.getElementById('step2').classList.contains('active')) {
                showStep(1);
            } else {
                // If on personal info step, go back to confirmation
                showStep(2);
            }
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
        function confirmSelectionAndContinue() {
            showStep(3);
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
        // Populate confirmation list with selected products
        function populateConfirmationList() {
            const selectedIds = document.getElementById('product_ids').value.split(',').filter(id => id.trim() !== '');
            const allProductCards = document.querySelectorAll('.product-card');
            let html = '';
            if (selectedIds.length === 0) {
                html = '<div style="color: #c62828; font-weight: bold;">No products selected.</div>';
            } else {
                html = '<ul style="list-style: none; padding: 0;">';
                allProductCards.forEach(card => {
                    const checkbox = card.querySelector('.product-checkbox');
                    if (checkbox && selectedIds.includes(checkbox.value)) {
                        const img = card.querySelector('img');
                        const name = card.querySelector('h4') ? card.querySelector('h4').textContent : '';
                        const brand = card.querySelector('p strong') ? card.querySelector('p strong').parentNode.textContent : '';
                        const priceDiv = card.querySelector('div[style*="background: linear-gradient"]');
                        const price = priceDiv ? priceDiv.textContent : '';
                        html += `<li style='display: flex; align-items: center; gap: 24px; margin-bottom: 18px; background: #f8f9fa; border-radius: 12px; padding: 16px 24px; border: 1.5px solid #e0e0e0;'>`;
                        if (img) html += `<img src='${img.src}' alt='' style='width: 80px; height: 80px; object-fit: contain; border-radius: 8px; background: #fff; border: 1px solid #dee2e6; margin-right: 16px;'>`;
                        html += `<div><div style='font-weight: 600; font-size: 18px; color: #333;'>${name}</div>`;
                        if (brand) html += `<div style='color: #666; font-size: 14px;'>${brand}</div>`;
                        if (price) html += `<div style='color: #007dd1; font-weight: bold; font-size: 16px; margin-top: 4px;'>${price}</div>`;
                        html += `</div></li>`;
                    }
                });
                html += '</ul>';
            }
            document.getElementById('confirmationList').innerHTML = html;
        }
        // On page load, show step 1
        document.addEventListener('DOMContentLoaded', function() {
            showStep(1);
        });
    </script>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reservation Successful!</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <i class="fas fa-check-circle success-icon"></i>
                <p>Your reservation has been submitted successfully.</p>
                <p id="successMessage">We will contact you shortly to confirm your reservation.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn" onclick="window.location.href='kiosk.php'">Return to Home</button>
            </div>
        </div>
    </div>

    <!-- Product Removal Confirmation Modal -->
    <div id="productRemovalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Remove Product</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="product-removal-content">
                    <div class="product-image-container">
                        <img id="removalProductImage" src="" alt="Selected Product" class="removal-product-image">
                    </div>
                    <div class="product-details">
                        <h3 id="removalProductName"></h3>
                        <p id="removalProductBrand"></p>
                        <p id="removalProductModel"></p>
                        <p id="removalProductPrice"></p>
                    </div>
                    <div class="removal-message">
                        <i class="fas fa-exclamation-circle warning-icon"></i>
                        <p>Are you sure you want to remove this product from your selection?</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn cancel-btn" onclick="hideRemoveConfirmModal()">Cancel</button>
                <button class="modal-btn confirm-btn" onclick="confirmProductRemoval()">Remove Product</button>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Terms and Conditions</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="terms-content">
                    <h3>Terms and Conditions for Product Reservations</h3>
                    <div class="terms-content">
                        <h4>Reservation Fee Structure:</h4>
                        <ul>
                            <li><strong>Products priced ₱1,000 and below:</strong> No reservation fee required</li>
                            <li><strong>Products priced above ₱1,000:</strong> 
                                <ul>
                                    <li>Minimum reservation fee: ₱500 per item</li>
                                    <li>Maximum reservation fee: ₱1,000 per item</li>
                                    <li>Proof of payment is required</li>
                                </ul>
                            </li>
                        </ul>
                        
                        <h4>Payment Terms:</h4>
                        <ul>
                            <li>Reservation fee must be paid within 24 hours of making the reservation</li>
                            <li>Remaining balance must be paid upon product collection</li>
                            <li>Reservation is valid for 7 days from the date of reservation</li>
                            <li>Failure to pay the remaining balance within 7 days will result in cancellation of the reservation</li>
                        </ul>
                        
                        <h4>Reservation Process:</h4>
                        <ul>
                            <li>Submit your reservation with complete and accurate information</li>
                            <li>Pay the required reservation fee (₱500-₱1,000 per item above ₱1,000)</li>
                            <li>Upload proof of payment for verification</li>
                            <li>We will contact you within 24 hours to confirm your reservation</li>
                            <li>Collect your product within 7 days and pay the remaining balance</li>
                        </ul>
                        
                        <h4>Cancellation Policy:</h4>
                        <ul>
                            <li>Reservations can be cancelled within 24 hours without penalty</li>
                            <li>After 24 hours, a 10% cancellation fee will be deducted from the refund</li>
                            <li>No refunds for cancellations after 7 days</li>
                        </ul>
                        
                        <h4>Important Notes:</h4>
                        <ul>
                            <li>Products are reserved on a first-come, first-served basis</li>
                            <li>We reserve the right to cancel reservations if products become unavailable</li>
                            <li>All prices are subject to change without prior notice</li>
                            <li>Valid government-issued ID is required upon product collection</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn" onclick="closeTermsModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Selection Limit Modal -->
    <div id="selectionLimitModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Selection Limit Reached</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-exclamation-triangle exclamation-triangle" style="font-size: 64px; color: #ffc107; margin-bottom: 24px;"></i>
                    <h3 style="color: #333; margin-bottom: 16px; font-size: 24px;">Maximum Products Reached</h3>
                    <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 16px;">
                        You have reached the maximum limit of <strong>5 products</strong> for a single reservation.
                    </p>
                    <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
                        To add more products, please remove one of your current selections first.
                    </p>
                    <div class="info-box">
                        <p>
                            <i class="fas fa-info-circle"></i>
                            Current Selection: <span id="currentSelectionCount" class="current-count">5</span> products
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn" onclick="hideSelectionLimitModal()" style="background: #007dd1; color: white;">Got It</button>
            </div>
        </div>
    </div>

    <!-- No Product Selected Modal -->
    <div id="noProductSelectedModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Product Selection Required</h2>
                <span class="close" title="Close">&times;</span>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-exclamation-circle warning-icon" style="font-size: 64px; color: #dc3545; margin-bottom: 24px;"></i>
                    <h3 style="color: #333; margin-bottom: 16px; font-size: 24px;">Please select at least one product to reserve.</h3>
                    <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 16px;">
                        You must select at least one product before proceeding with your reservation.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn" onclick="hideNoProductSelectedModal()" style="background: #007dd1; color: white;">OK</button>
            </div>
        </div>
    </div>

    <!-- Form Validation Error Modal -->
    <div id="formValidationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle" style="color: #dc3545; margin-right: 10px;"></i>Form Validation Error</h3>
                <span class="close" onclick="hideFormValidationModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Please correct the following errors before submitting:</p>
                <ul id="errorList" style="margin-top: 15px; padding-left: 20px; color: #dc3545;"></ul>
            </div>
            <div class="modal-footer">
                <button onclick="hideFormValidationModal()" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </div>

    <!-- Existing Reservation Modal -->
    <div id="existingReservationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle" style="color: #007dd1; margin-right: 10px;"></i>Existing Reservation Found</h3>
                <span class="close" onclick="hideExistingReservationModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-clock" style="font-size: 48px; color: #007dd1; margin-bottom: 20px;"></i>
                    <h4 style="color: #333; margin-bottom: 15px;">Reservation Already Exists</h4>
                    <p style="color: #666; line-height: 1.6; margin-bottom: 20px;">
                        You already have a pending reservation in our system. Please wait for it to be processed or contact our support team for assistance.
                    </p>
                    <div style="background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 12px; padding: 15px; margin: 20px 0;">
                        <h5 style="color: #333; margin-bottom: 10px;"><i class="fas fa-phone" style="color: #007dd1; margin-right: 8px;"></i>Contact Support</h5>
                        <p style="color: #666; margin: 0;">Phone: <strong>0912-345-6789</strong></p>
                        <p style="color: #666; margin: 5px 0 0 0;">Email: <strong>support@bisligicenter.com</strong></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="hideExistingReservationModal()" class="btn btn-primary">Close</button>
            </div>
        </div>
    </div>

    <script>
        const MIN_PAYMENT = 500;
        const FULL_PAYMENT = 1000;

        let pendingProductRemoval = null;

        // Global variables for modal handling
        let isModalOpen = false;
        let modalTimeout = null;

        // Function to show the confirmation modal
        function showRemoveConfirmModal() {
            const selectedProduct = JSON.parse(sessionStorage.getItem('selectedProduct') || '{}');
            if (selectedProduct.selected) {
                // Update modal content with product details
                document.getElementById('removalProductImage').src = selectedProduct.image;
                document.getElementById('removalProductName').textContent = selectedProduct.name;
                document.getElementById('removalProductBrand').textContent = selectedProduct.brand;
                document.getElementById('removalProductModel').textContent = selectedProduct.model;
                document.getElementById('removalProductPrice').textContent = selectedProduct.price;
                
                showModal('productRemovalModal');
            }
        }

        // Function to hide the confirmation modal
        function hideRemoveConfirmModal() {
            const modal = document.getElementById('removeConfirmModal');
            modal.style.display = 'none';
            isModalOpen = false;
        }

        // Function to perform the actual removal
        function performProductRemoval() {
            const selectedProductDisplay = document.getElementById('selectedProductDisplay');
            selectedProductDisplay.style.display = 'none';
            document.getElementById('product_id').value = '';
            document.querySelectorAll('.product-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            sessionStorage.removeItem('selectedProduct');
            
            // Reset payment fields
            document.getElementById('down_payment').value = '500';
            document.getElementById('balance').value = '';
            document.getElementById('paymentMessage').textContent = '';
            document.getElementById('balanceMessage').textContent = '';
            
            hideRemoveConfirmModal();
        }

        // Function to remove selected product
        function removeSelectedProduct() {
            showRemoveConfirmModal();
        }

        // Initialize modal event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('removeConfirmModal');
            const closeBtn = modal.querySelector('.close');
            const cancelBtn = modal.querySelector('.cancel-btn');
            const confirmBtn = modal.querySelector('.confirm-btn');

            // Close button handler
            closeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                hideRemoveConfirmModal();
            });

            // Cancel button handler
            cancelBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                hideRemoveConfirmModal();
            });

            // Confirm button handler
            confirmBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                performProductRemoval();
            });

            // Click outside modal handler
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    hideRemoveConfirmModal();
                }
            });

            // Prevent modal from closing when clicking inside the modal content
            modal.querySelector('.modal-content').addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });

        // Function to validate and calculate payment
        function validateAndCalculatePayment() {
            const paymentInput = document.getElementById('down_payment');
            const payment = parseFloat(paymentInput.value) || 0;
            const paymentMessage = document.getElementById('paymentMessage');
            
            // Get selected product checkboxes
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            let countAbove1000 = 0;
            let totalPrice = 0;
            selectedCheckboxes.forEach(cb => {
                const price = parseFloat(cb.getAttribute('data-selling-price')) || 0;
                if (price > 1000) {
                    countAbove1000++;
                    totalPrice += price;
                }
            });

            if (countAbove1000 === 0) {
                paymentMessage.textContent = 'No reservation fee required (only applies to items above ₱1,000).';
                paymentMessage.className = 'payment-message';
                paymentInput.value = '';
                paymentInput.readOnly = true;
                document.getElementById('balance').value = '';
                document.getElementById('balanceMessage').textContent = '';
                return;
            }

            // Calculate payment range
            const minPayment = countAbove1000 * 500; // ₱500 minimum per item
            const maxPayment = countAbove1000 * 1000; // ₱1,000 maximum per item
            
            paymentInput.readOnly = false;
            paymentInput.min = minPayment;
            paymentInput.max = maxPayment;

            if (payment < minPayment) {
                paymentMessage.textContent = `Minimum reservation fee is ₱${minPayment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} (₱500 per product × ${countAbove1000} products)`;
                paymentMessage.className = 'payment-message error';
                paymentInput.value = minPayment;
                calculateBalance();
                return;
            }

            if (payment > maxPayment) {
                paymentMessage.textContent = `Maximum reservation fee is ₱${maxPayment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} (₱1,000 per product × ${countAbove1000} products)`;
                paymentMessage.className = 'payment-message error';
                paymentInput.value = maxPayment;
                calculateBalance();
                return;
            }

            paymentMessage.textContent = `Reservation Fee: ₱${payment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} for ${countAbove1000} product${countAbove1000 > 1 ? 's' : ''} (₱500-₱1,000 per item above ₱1,000)`;
            paymentMessage.className = 'payment-message';
            calculateBalance();
        }

        function calculateBalance() {
            const payment = parseFloat(document.getElementById('down_payment').value) || 0;
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            let totalPrice = 0;
            let countAbove1000 = 0;
            selectedCheckboxes.forEach(cb => {
                const price = parseFloat(cb.getAttribute('data-selling-price')) || 0;
                if (price > 1000) {
                    totalPrice += price;
                    countAbove1000++;
                }
            });

            if (countAbove1000 === 0) {
                document.getElementById('balance').value = '';
                document.getElementById('balanceMessage').textContent = '';
                return;
            }

            const remainingBalance = totalPrice - payment;
            document.getElementById('balance').value = remainingBalance.toFixed(2);

            const balanceMessage = document.getElementById('balanceMessage');
            if (remainingBalance > 0) {
                balanceMessage.textContent = `Remaining balance: ₱${remainingBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} (Total: ₱${totalPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})})`;
                balanceMessage.className = 'balance-message positive';
            } else if (remainingBalance === 0) {
                balanceMessage.textContent = 'Full payment completed!';
                balanceMessage.className = 'balance-message zero';
            } else {
                balanceMessage.textContent = 'Payment exceeds full amount';
                balanceMessage.className = 'balance-message negative';
            }
        }

        // Calculate balance when page loads if a product is selected
        document.addEventListener('DOMContentLoaded', function() {
            const selectedProduct = document.querySelector('.product-option.selected');
            if (selectedProduct) {
                validateAndCalculatePayment();
            }
        });

        // Image preview functions
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewDiv = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewDiv.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeImage() {
            const input = document.getElementById('proof_of_payment');
            const preview = document.getElementById('preview');
            const previewDiv = document.getElementById('imagePreview');
            
            input.value = '';
            preview.src = '#';
            previewDiv.style.display = 'none';
        }

        // Function to show terms modal
        function showTermsModal() {
            const modal = document.getElementById('termsModal');
            const closeBtn = modal.querySelector('.close');
            
            modal.style.display = 'block';
            
            // Close modal when clicking the X
            closeBtn.onclick = function() {
                closeTermsModal();
            }
            
            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target == modal) {
                    closeTermsModal();
                }
            }
        }

        // Function to close terms modal
        function closeTermsModal() {
            const modal = document.getElementById('termsModal');
            modal.style.display = 'none';
        }

        // Add real-time validation feedback
        document.querySelectorAll('input, textarea').forEach(field => {
            field.addEventListener('input', function() {
                validateField(this);
                // Clear error styling when user starts typing
                if (this.classList.contains('error')) {
                    this.classList.remove('error');
                    this.style.borderColor = '#e0e0e0';
                }
            });
        });

        function validateField(field) {
            const message = field.nextElementSibling;
            if (field.validity.valid) {
                message.style.color = '#2e7d32';
            } else {
                message.style.color = '#d32f2f';
            }
        }

        // Update form submission to include field validation
        document.getElementById('reservationForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Get the selected product IDs
            const selectedIds = document.getElementById('product_ids').value;
            if (!selectedIds) {
                showNoProductSelectedModal();
                return;
            }
            
            const selectedCount = selectedIds.split(',').filter(id => id.trim() !== '').length;
            if (selectedCount < 1 || selectedCount > 5) {
                alert('Please select between 1 and 5 products for reservation');
                return;
            }

            // Check if any selected product is above 1000
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            let hasAbove1000 = false;
            selectedCheckboxes.forEach(cb => {
                const price = parseFloat(cb.getAttribute('data-selling-price')) || 0;
                if (price > 1000) hasAbove1000 = true;
            });

            // Validate all fields
            let isValid = true;
            let errorMessages = [];
            
            // Check required fields
            const requiredFields = [
                { id: 'name', label: 'Full Name' },
                { id: 'contact_number', label: 'Contact Number' },
                { id: 'address', label: 'Complete Address' },
                { id: 'email', label: 'Email Address' }
            ];
            
            requiredFields.forEach(field => {
                const element = document.getElementById(field.id);
                if (!element.value.trim()) {
                    isValid = false;
                    errorMessages.push(`${field.label} is required`);
                    element.style.borderColor = '#dc3545';
                    element.classList.add('error');
                } else if (!element.validity.valid) {
                    isValid = false;
                    errorMessages.push(`${field.label} format is invalid`);
                    element.style.borderColor = '#dc3545';
                    element.classList.add('error');
                } else {
                    element.style.borderColor = '#e0e0e0';
                    element.classList.remove('error');
                }
            });
            
            // Check if payment is required but not provided
            if (hasAbove1000) {
                const paymentInput = document.getElementById('down_payment');
                const fileInput = document.getElementById('proof_of_payment');
                
                if (!paymentInput.value || parseFloat(paymentInput.value) === 0) {
                    isValid = false;
                    errorMessages.push('Reservation fee is required for products above ₱1,000');
                    paymentInput.style.borderColor = '#dc3545';
                    paymentInput.classList.add('error');
                } else {
                    paymentInput.style.borderColor = '#e0e0e0';
                    paymentInput.classList.remove('error');
                }
                
                if (!fileInput.files || fileInput.files.length === 0) {
                    isValid = false;
                    errorMessages.push('Proof of payment is required for products above ₱1,000');
                    fileInput.style.borderColor = '#dc3545';
                    fileInput.classList.add('error');
                } else {
                    fileInput.style.borderColor = '#e0e0e0';
                    fileInput.classList.remove('error');
                }
            }
            
            if (!isValid) {
                // Populate error list
                const errorList = document.getElementById('errorList');
                errorList.innerHTML = '';
                errorMessages.forEach(error => {
                    const li = document.createElement('li');
                    li.textContent = error;
                    errorList.appendChild(li);
                });
                
                showFormValidationModal();
                return;
            }
            
            const agreementCheckbox = document.getElementById('user_agreement');
            const agreementError = document.getElementById('agreementError');
            
            if (!agreementCheckbox.checked) {
                agreementError.textContent = 'You must agree to the Terms and Conditions to proceed';
                agreementError.style.display = 'block';
                return;
            }
            
            agreementError.style.display = 'none';
            
            try {
                const formData = new FormData(e.target);
                const payment = parseFloat(formData.get('down_payment')) || 0;
                
                if (hasAbove1000) {
                    // Calculate required payment based on selected products above 1000
                    let countAbove1000 = 0;
                    selectedCheckboxes.forEach(cb => {
                        const price = parseFloat(cb.getAttribute('data-selling-price')) || 0;
                        if (price > 1000) countAbove1000++;
                    });
                    const minPaymentPerProduct = 500; // ₱500 minimum per item
                    const maxPaymentPerProduct = 1000; // ₱1,000 maximum per item
                    const totalMinPayment = countAbove1000 * minPaymentPerProduct;
                    const totalMaxPayment = countAbove1000 * maxPaymentPerProduct;
                
                if (payment < totalMinPayment) {
                        throw new Error(`Minimum reservation fee is ₱${totalMinPayment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} (₱${minPaymentPerProduct.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} per product × ${countAbove1000} products)`);
                }
                
                    if (payment > totalMaxPayment) {
                        throw new Error(`Maximum reservation fee is ₱${totalMaxPayment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} (₱${maxPaymentPerProduct.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} per product × ${countAbove1000} products)`);
                    }
                }
                // Prepare reservation data
                const reservationData = {
                    product_ids: selectedIds.split(',').filter(id => id.trim() !== ''), // Keep as strings
                    name: formData.get('name'),
                    contact_number: formData.get('contact_number'),
                    address: formData.get('address'),
                    email: formData.get('email'),
                    reservation_fee: hasAbove1000 ? payment.toFixed(2) : '',
                    product_count: selectedCount
                };
                
                // Debug logging
                console.log('Raw selectedIds:', selectedIds);
                console.log('Parsed product_ids:', reservationData.product_ids);
                console.log('Selected checkboxes:', selectedCheckboxes.length);
                selectedCheckboxes.forEach((cb, index) => {
                    console.log(`Checkbox ${index}:`, {
                        value: cb.value,
                        checked: cb.checked,
                        price: cb.getAttribute('data-selling-price')
                    });
                });

                // Log the data being sent (for debugging)
                console.log('Sending reservation data:', reservationData);
                console.log('Payment required:', hasAbove1000);

                if (hasAbove1000) {
                const file = formData.get('proof_of_payment');
                    console.log('File info:', file ? {name: file.name, size: file.size, type: file.type} : 'No file');
                    
                if (!file || file.size === 0) {
                    throw new Error('Please upload proof of payment');
                }
                if (file.size > 5 * 1024 * 1024) {
                    throw new Error('File size must be less than 5MB');
                }
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    throw new Error('Only JPG, JPEG, and PNG files are allowed');
                }
                const reader = new FileReader();
                reader.onload = async (e) => {
                    try {
                        reservationData.proof_of_payment = e.target.result;
                            console.log('Sending request with proof of payment...');
                        
                        const response = await fetch('reservations.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(reservationData)
                        });
                            
                            console.log('Response status:', response.status);
                            console.log('Response headers:', response.headers);
                        
                        let data;
                        const contentType = response.headers.get('content-type');
                            console.log('Content-Type:', contentType);
                            
                        if (!contentType || !contentType.includes('application/json')) {
                            console.error('Invalid content type:', contentType);
                            throw new Error('Server returned non-JSON response');
                        }

                            let text;
                        try {
                                text = await response.text();
                                console.log('Raw response text:', text);
                                
                            if (!text) {
                                throw new Error('Empty response from server');
                            }
                            data = JSON.parse(text);
                                console.log('Parsed response data:', data);
                        } catch (parseError) {
                            console.error('JSON parse error:', parseError);
                            console.error('Response text:', text);
                            throw new Error('Invalid JSON response from server');
                        }
                        if (data.success) {
                            document.getElementById('reservationForm').reset();
                            document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
                            updateSelectedProductIds();
                            const successMessage = document.getElementById('successMessage');
                            if (data.product_count > 1) {
                                successMessage.textContent = `We will contact you shortly to confirm your reservation for ${data.product_count} products.`;
                            } else {
                                successMessage.textContent = 'We will contact you shortly to confirm your reservation.';
                            }
                            showModal('successModal');
                            } else {
                                // Check if we need to show a specific modal
                                if (data.show_modal === 'existing_reservation') {
                                    showExistingReservationModal();
                        } else {
                            throw new Error(data.message || 'Failed to make reservation');
                                }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert(error.message || 'An error occurred while processing your reservation. Please try again.');
                    }
                };
                reader.onerror = () => {
                    throw new Error('Error reading the file');
                };
                reader.readAsDataURL(file);
                } else {
                    // No payment/proof required, send request directly
                    reservationData.proof_of_payment = '';
                    console.log('Sending request without payment...');
                    
                    const response = await fetch('reservations.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(reservationData)
                    });
                    
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    
                    let data;
                    const contentType = response.headers.get('content-type');
                    console.log('Content-Type:', contentType);
                    
                    if (!contentType || !contentType.includes('application/json')) {
                        console.error('Invalid content type:', contentType);
                        throw new Error('Server returned non-JSON response');
                    }
                    let text;
                    try {
                        text = await response.text();
                        console.log('Raw response text:', text);
                        
                        if (!text) {
                            throw new Error('Empty response from server');
                        }
                        data = JSON.parse(text);
                        console.log('Parsed response data:', data);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        console.error('Response text:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                    if (data.success) {
                        document.getElementById('reservationForm').reset();
                        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
                        updateSelectedProductIds();
                        const successMessage = document.getElementById('successMessage');
                        if (data.product_count > 1) {
                            successMessage.textContent = `We will contact you shortly to confirm your reservation for ${data.product_count} products.`;
                        } else {
                            successMessage.textContent = 'We will contact you shortly to confirm your reservation.';
                        }
                        showModal('successModal');
                    } else {
                        throw new Error(data.message || 'Failed to make reservation');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'An error occurred while processing your reservation. Please try again.');
            }
        });

        // Load selected product from session storage
        const selectedProduct = JSON.parse(sessionStorage.getItem('selectedProduct') || '{}');
        if (selectedProduct.selected) {
            const selectedProductDisplay = document.getElementById('selectedProductDisplay');
            const productId = selectedProduct.product_id;
            
            document.getElementById('selectedProductImage').src = selectedProduct.image;
            document.getElementById('selectedProductName').textContent = selectedProduct.name;
            document.getElementById('selectedProductBrand').textContent = selectedProduct.brand;
            document.getElementById('selectedProductModel').textContent = selectedProduct.model;
            document.getElementById('selectedProductPrice').textContent = selectedProduct.price;
            
            selectedProductDisplay.style.display = 'block';
            document.getElementById('product_id').value = productId;
            
            document.querySelectorAll('.product-option').forEach(opt => {
                if (opt.dataset.productId === productId) {
                    opt.classList.add('selected');
                }
            });
        }

        // Add uppercase conversion for name and address
        document.getElementById('name').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
            validateField(this);
        });

        document.getElementById('address').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
            validateField(this);
        });

        // Handle paste events for name and address
        document.getElementById('name').addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            this.value = pastedText.toUpperCase();
            validateField(this);
        });

        document.getElementById('address').addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            this.value = pastedText.toUpperCase();
            validateField(this);
        });

        // Add contact number validation
        document.getElementById('contact_number').addEventListener('input', function(e) {
            // Remove any non-digit characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limit to 11 digits
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
            
            // Validate the field
            validateField(this);
            
            // Update message color based on length
            const message = this.nextElementSibling;
            if (this.value.length === 11) {
                message.style.color = '#2e7d32';
            } else {
                message.style.color = '#d32f2f';
            }
        });

        // Prevent paste of non-numeric characters
        document.getElementById('contact_number').addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numericOnly = pastedText.replace(/[^0-9]/g, '').slice(0, 11);
            this.value = numericOnly;
            validateField(this);
        });

        // Prevent keypress of non-numeric characters
        document.getElementById('contact_number').addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });

        // Initialize success modal close handlers
        document.addEventListener('DOMContentLoaded', function() {
            const successModal = document.getElementById('successModal');
            const successCloseBtn = successModal.querySelector('.close');

            // Close modal when clicking the X
            successCloseBtn.addEventListener('click', function() {
                hideModal('successModal');
                window.location.href = 'kiosk.php';
            });
        });

        // Enhanced Modal Functions
        function showModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'block';
            // Trigger reflow
            modal.offsetHeight;
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hideModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }, 300);
        }

        // Initialize all modals
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all modals
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                // Close button handler
                const closeBtn = modal.querySelector('.close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        hideModal(modal.id);
                        // Special handling for success modal
                        if (modal.id === 'successModal') {
                            window.location.href = 'kiosk.php';
                        }
                    });
                }

                // Click outside modal handler
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        hideModal(modal.id);
                    }
                });
            });

            // Add escape key handler for all modals
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const visibleModal = document.querySelector('.modal.show');
                    if (visibleModal) {
                        hideModal(visibleModal.id);
                    }
                }
            });
        });

        // Update modal show/hide functions
        function showTermsModal() {
            showModal('termsModal');
        }

        function closeTermsModal() {
            hideModal('termsModal');
        }

        function showRemoveConfirmModal() {
            showModal('productRemovalModal');
        }

        function hideRemoveConfirmModal() {
            hideModal('productRemovalModal');
        }

        function confirmProductRemoval() {
            const selectedProductDisplay = document.getElementById('selectedProductDisplay');
            selectedProductDisplay.style.display = 'none';
            document.getElementById('product_id').value = '';
            document.querySelectorAll('.product-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            sessionStorage.removeItem('selectedProduct');
            
            // Reset payment fields
            document.getElementById('down_payment').value = '500';
            document.getElementById('balance').value = '';
            document.getElementById('paymentMessage').textContent = '';
            document.getElementById('balanceMessage').textContent = '';
            
            hideRemoveConfirmModal();
        }

        function hideSelectionLimitModal() {
            hideModal('selectionLimitModal');
        }

        function showSelectionLimitModal() {
            // Update the current selection count in the modal
            const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            const currentSelectedCount = selectedCheckboxes.length;
            document.getElementById('currentSelectionCount').textContent = currentSelectedCount;
            
            showModal('selectionLimitModal');
        }

        function showNoProductSelectedModal() {
            showModal('noProductSelectedModal');
        }
        function hideNoProductSelectedModal() {
            hideModal('noProductSelectedModal');
        }

        // Form Validation Error Modal Functions
        function showFormValidationModal() {
            showModal('formValidationModal');
        }

        function hideFormValidationModal() {
            hideModal('formValidationModal');
        }

        // Existing Reservation Modal Functions
        function showExistingReservationModal() {
            showModal('existingReservationModal');
        }

        function hideExistingReservationModal() {
            hideModal('existingReservationModal');
        }
    </script>
</body>
</html>