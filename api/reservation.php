<?php
// Prevent any output before headers
ob_start();

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Function to send JSON response
function sendJsonResponse($success, $message, $data = null) {
    ob_clean(); // Clear any output
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        error_log("JSON encode error: " . json_last_error_msg());
        $json = json_encode([
            'success' => false,
            'message' => 'Error encoding response'
        ]);
    }
    echo $json;
    exit;
}

// Function to save base64 image
function saveBase64Image($base64String, $uploadDir) {
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Remove data URL prefix if present
    if (strpos($base64String, 'data:image') === 0) {
        $base64String = preg_replace('#^data:image/\w+;base64,#i', '', $base64String);
    }

    // Decode base64 string
    $imageData = base64_decode($base64String);
    if ($imageData === false) {
        throw new Exception('Invalid image data');
    }

    // Generate unique filename
    $filename = uniqid('proof_') . '.jpg';
    $filepath = $uploadDir . '/' . $filename;

    // Save the file
    if (file_put_contents($filepath, $imageData) === false) {
        throw new Exception('Failed to save image');
    }

    return $filename;
}

try {
    // Check if required files exist
    $dbFile = __DIR__ . '/../db.php';
    $functionsFile = __DIR__ . '/../functions.php';
    
    if (!file_exists($dbFile)) {
        throw new Exception("Database configuration file not found");
    }
    if (!file_exists($functionsFile)) {
        throw new Exception("Functions file not found");
    }

    require_once $dbFile;
    require_once $functionsFile;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(false, 'Invalid request method');
    }

    // Get and validate JSON input
    $json = file_get_contents('php://input');
    if (!$json) {
        sendJsonResponse(false, 'No data received');
    }

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg() . " Input: " . substr($json, 0, 1000));
        sendJsonResponse(false, 'Invalid JSON format: ' . json_last_error_msg());
    }

    // Validate required fields
    $required_fields = [
        'name', 'contact_number', 'address', 'email', 
        'reservation_fee', 'remaining_reservation_fee', 'proof_of_payment',
        'product_name', 'product_brand', 'product_model', 'product_price'
    ];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            sendJsonResponse(false, "Missing required field: $field");
        }
    }

    // Validate payment and balance
    if (!is_numeric($data['reservation_fee']) || $data['reservation_fee'] < 0) {
        sendJsonResponse(false, "Invalid payment amount");
    }

    // Ensure minimum payment of 500
    $reservationFee = floatval($data['reservation_fee']);
    if ($reservationFee < 500) {
        sendJsonResponse(false, "Minimum payment amount is ₱500");
    }

    if (!is_numeric($data['remaining_reservation_fee']) || $data['remaining_reservation_fee'] < 0) {
        sendJsonResponse(false, "Invalid balance amount");
    }

    // Save proof of payment image
    $uploadDir = __DIR__ . '/../uploads/proof_of_payment';
    $imageFilename = saveBase64Image($data['proof_of_payment'], $uploadDir);

    // Database connection
    if (!function_exists('getDBConnection')) {
        throw new Exception("Database connection function not found");
    }

    $conn = getDBConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Insert reservation
    $stmt = $conn->prepare("
        INSERT INTO reservations (
            name,
            contact_number,
            address,
            email,
            reservation_date,
            reservation_time,
            proof_of_payment,
            status,
            reservation_fee,
            remaining_reservation_fee,
            product_name_1,
            product_brand_1,
            product_model_1,
            product_price_1,
            product_count
        ) VALUES (?, ?, ?, ?, CURDATE(), CURTIME(), ?, 'pending', ?, ?, ?, ?, ?, ?, 1)
    ");
    
    if (!$stmt) {
        throw new Exception("Database prepare error: " . implode(" ", $conn->errorInfo()));
    }
    
    if (!$stmt->execute([
        $data['name'],
        $data['contact_number'],
        $data['address'],
        $data['email'],
        $imageFilename,
        $data['reservation_fee'],
        $data['remaining_reservation_fee'],
        $data['product_name'],
        $data['product_brand'],
        $data['product_model'],
        $data['product_price']
    ])) {
        throw new Exception("Database execute error: " . implode(" ", $stmt->errorInfo()));
    }
    
    $reservation_id = $conn->lastInsertId();
    $stmt->closeCursor();
    
    sendJsonResponse(true, 'Reservation created successfully', ['reservation_id' => $reservation_id]);
    
} catch (Exception $e) {
    error_log("Reservation error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    sendJsonResponse(false, $e->getMessage());
} catch (Error $e) {
    error_log("PHP Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    sendJsonResponse(false, "A system error occurred: " . $e->getMessage());
} 