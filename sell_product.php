<?php
require_once 'db.php';

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id']) || !isset($input['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$product_id = $input['product_id'];
$quantity = (int)$input['quantity'];

if ($quantity <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Quantity must be greater than zero.']);
    exit;
}

try {
    $conn->beginTransaction();

    // Check if product exists and get current stock, selling price, and purchase price
    $stmt = $conn->prepare("SELECT stock_quantity, selling_price, purchase_price, product, brand, model FROM products WHERE product_id = :product_id");
    $stmt->bindValue(':product_id', $product_id, PDO::PARAM_STR);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit;
    }

    $current_stock = (int)$product['stock_quantity'];
    $selling_price = (float)$product['selling_price'];
    $purchase_price = (float)$product['purchase_price'];

    if ($quantity > $current_stock) {
        $conn->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Quantity exceeds current stock.']);
        exit;
    }

    // Update stock quantity in products table
    $new_stock = $current_stock - $quantity;
    $updateStmt = $conn->prepare("UPDATE products SET stock_quantity = :new_stock WHERE product_id = :product_id");
    $updateStmt->bindValue(':new_stock', $new_stock, PDO::PARAM_INT);
    $updateStmt->bindValue(':product_id', $product_id, PDO::PARAM_STR);
    $updateStmt->execute();

    // Insert record into stock_out table to track the stock movement
    $stockOutStmt = $conn->prepare("INSERT INTO stock_out (product_id, quantity, previous_stock, new_stock, created_by, notes, created_at) VALUES (:product_id, :quantity, :previous_stock, :new_stock, :created_by, :notes, NOW())");
    $stockOutStmt->bindValue(':product_id', $product_id, PDO::PARAM_STR);
    $stockOutStmt->bindValue(':quantity', -$quantity, PDO::PARAM_INT); // Negative quantity for stock out
    $stockOutStmt->bindValue(':previous_stock', $current_stock, PDO::PARAM_INT);
    $stockOutStmt->bindValue(':new_stock', $new_stock, PDO::PARAM_INT);
    $stockOutStmt->bindValue(':created_by', 'Admin User', PDO::PARAM_STR);
    $stockOutStmt->bindValue(':notes', 'Product sold - ' . $product['brand'] . ' ' . $product['model'] . ' (Qty: ' . $quantity . ')', PDO::PARAM_STR);
    $stockOutStmt->execute();

    // Use purchase price from products table for stock record
    $date_of_purchase = date('Y-m-d');

    // Insert a new record into stocks table to record sold quantity
    $insertStockStmt = $conn->prepare("INSERT INTO stocks (product_id, purchase_price, date_of_purchase, quantity_sold) VALUES (:product_id, :purchase_price, :date_of_purchase, :quantity_sold)");
    $insertStockStmt->bindValue(':product_id', $product_id, PDO::PARAM_STR);
    $insertStockStmt->bindValue(':purchase_price', $purchase_price);
    $insertStockStmt->bindValue(':date_of_purchase', $date_of_purchase);
    $insertStockStmt->bindValue(':quantity_sold', $quantity, PDO::PARAM_INT);
    $insertStockStmt->execute();

    $stock_id = $conn->lastInsertId();

    $dateOfSale = date('Y-m-d H:i:s');
    $stockRevenue = $quantity * $selling_price;

    // Insert into sales table
    $insertSalesStmt = $conn->prepare("INSERT INTO sales (sales_id, stock_id, product_id, selling_price, quantity_sold, stock_revenue, date_of_sale, purchase_price) 
                                     VALUES (NULL, :stock_id, :product_id, :selling_price, :quantity_sold, :stock_revenue, :date_of_sale, :purchase_price)");
    $insertSalesStmt->bindValue(':stock_id', $stock_id, PDO::PARAM_INT);
    $insertSalesStmt->bindValue(':product_id', $product_id, PDO::PARAM_STR);
    $insertSalesStmt->bindValue(':selling_price', $selling_price);
    $insertSalesStmt->bindValue(':quantity_sold', $quantity, PDO::PARAM_INT);
    $insertSalesStmt->bindValue(':stock_revenue', $stockRevenue);
    $insertSalesStmt->bindValue(':date_of_sale', $dateOfSale);
    $insertSalesStmt->bindValue(':purchase_price', $purchase_price);
    $insertSalesStmt->execute();

    // Calculate gross profit
    $grossProfit = $stockRevenue - ($quantity * $purchase_price);

    // Fetch existing profit record for today or create new
    $profitDate = date('Y-m-d');
    $profitStmt = $conn->prepare("SELECT * FROM profit WHERE date_of_sale = :profit_date LIMIT 1");
    $profitStmt->bindValue(':profit_date', $profitDate);
    $profitStmt->execute();
    $profitRecord = $profitStmt->fetch(PDO::FETCH_ASSOC);

    if ($profitRecord) {
        // Update existing profit record
        $newTotalSales = $profitRecord['total_sales'] + $stockRevenue;
        $newGrossProfit = $newTotalSales - ($quantity * $purchase_price);
        $newNetProfit = $newGrossProfit - $profitRecord['expenses'];

        $updateProfitStmt = $conn->prepare("UPDATE profit SET total_sales = :total_sales, gross_profit = :gross_profit, net_profit = :net_profit WHERE profit_id = :profit_id");
        $updateProfitStmt->bindValue(':total_sales', $newTotalSales);
        $updateProfitStmt->bindValue(':gross_profit', $newGrossProfit);
        $updateProfitStmt->bindValue(':net_profit', $newNetProfit);
        $updateProfitStmt->bindValue(':profit_id', $profitRecord['profit_id'], PDO::PARAM_INT);
        $updateProfitStmt->execute();
    } else {
        // Insert new profit record with zero expenses
        $netProfit = $grossProfit; // expenses assumed zero
        $insertProfitStmt = $conn->prepare("INSERT INTO profit (total_sales, gross_profit, expenses, net_profit, date_of_sale) VALUES (:total_sales, :gross_profit, 0, :net_profit, :date_of_sale)");
        $insertProfitStmt->bindValue(':total_sales', $stockRevenue);
        $insertProfitStmt->bindValue(':gross_profit', $grossProfit);
        $insertProfitStmt->bindValue(':net_profit', $netProfit);
        $insertProfitStmt->bindValue(':date_of_sale', $profitDate);
        $insertProfitStmt->execute();
    }

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Product stock updated and sale recorded successfully.']);
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
