<?php
header('Content-Type: application/json');

require_once '../db.php';

$conn = getDBConnection();

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    // Query to get pending reservations with details for the new multi-product structure
    $query = "SELECT 
                r.reservation_id,
                r.name,
                r.reservation_date,
                r.reservation_time,
                r.product_count,
                r.reservation_fee,
                r.remaining_reservation_fee,
                r.status,
                -- Product 1
                r.product_name_1, r.product_brand_1, r.product_model_1, r.product_price_1,
                -- Product 2
                r.product_name_2, r.product_brand_2, r.product_model_2, r.product_price_2,
                -- Product 3
                r.product_name_3, r.product_brand_3, r.product_model_3, r.product_price_3,
                -- Product 4
                r.product_name_4, r.product_brand_4, r.product_model_4, r.product_price_4,
                -- Product 5
                r.product_name_5, r.product_brand_5, r.product_model_5, r.product_price_5
              FROM reservations r
              WHERE r.status = 'pending'
              ORDER BY r.reservation_date DESC, r.reservation_time DESC";
    
    $stmt = $conn->query($query);
    
    if ($stmt) {
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pending_count = count($reservations);
        
        // Format the reservations to include product details
        foreach ($reservations as &$reservation) {
            $products = [];
            
            // Add products that exist (not null)
            for ($i = 1; $i <= 5; $i++) {
                if (!empty($reservation["product_name_$i"])) {
                    $products[] = [
                        'name' => $reservation["product_name_$i"],
                        'brand' => $reservation["product_brand_$i"],
                        'model' => $reservation["product_model_$i"],
                        'price' => $reservation["product_price_$i"]
                    ];
                }
            }
            
            $reservation['products'] = $products;
            
            // Remove individual product fields from response
            for ($i = 1; $i <= 5; $i++) {
                unset($reservation["product_name_$i"]);
                unset($reservation["product_brand_$i"]);
                unset($reservation["product_model_$i"]);
                unset($reservation["product_price_$i"]);
            }
        }
        
        echo json_encode([
            'pending_count' => $pending_count,
            'reservations' => $reservations
        ]);
    } else {
        throw new Exception('Error fetching pending reservations: ' . $conn->errorInfo()[2]);
    }
    
} catch (Exception $e) {
    error_log("Error fetching pending reservations: " . $e->getMessage());
    echo json_encode(['error' => 'Internal server error']);
} finally {
    if ($conn) {
        $conn = null;
    }
}
?> 