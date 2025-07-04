<?php
header('Content-Type: application/json');
require_once 'db.php';

$response = ["data" => [], "summary" => []];

try {
    $pdo = getConnection();

    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : null;

    $where = '';
    $params = [];
    if ($start_date && $end_date) {
        $where = 'WHERE DATE(return_date) BETWEEN :start_date AND :end_date';
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }

    $sql = "SELECT * FROM returned_items $where ORDER BY return_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $returns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['data'] = array_map(function($row) {
        return [
            'return_id' => $row['return_id'],
            'product_id' => $row['product_id'],
            'product_name' => $row['product_name'],
            'brand' => $row['brand'],
            'model' => $row['model'],
            'storage' => $row['storage'],
            'quantity_returned' => $row['quantity_returned'],
            'previous_stock' => $row['previous_stock'],
            'new_stock' => $row['new_stock'],
            'reason' => $row['reason'],
            'returned_by' => $row['returned_by'],
            'return_date' => $row['return_date'],
            'remarks' => $row['remarks'],
            'date' => date('Y-m-d', strtotime($row['return_date'])),
            'date_time' => $row['return_date'],
        ];
    }, $returns);

    // Summary
    $response['summary'] = [
        'total_returns' => count($returns),
        'total_quantity_returned' => array_sum(array_column($returns, 'quantity_returned')),
        'unique_products' => count(array_unique(array_column($returns, 'product_id'))),
        'date_range' => $start_date && $end_date ? "$start_date to $end_date" : '-',
    ];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
} 