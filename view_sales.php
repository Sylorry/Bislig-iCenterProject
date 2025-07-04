<?php
require_once 'db.php';

function fetchSalesMetrics($conn) {
    try {
        if (!$conn) {
            throw new PDOException("Database connection not available");
        }

        // Get total revenue
        $totalRevenueStmt = $conn->query("
            SELECT COALESCE(SUM(stock_revenue), 0) AS total_revenue 
            FROM sales
        ");
        $totalRevenue = $totalRevenueStmt->fetchColumn();

        // Get total orders (count of unique sales)
        $totalOrdersStmt = $conn->query("SELECT COUNT(*) AS total_orders FROM sales");
        $totalOrders = $totalOrdersStmt->fetchColumn();

        // Calculate average order value
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Calculate conversion rate (assuming 1000 visitors as a placeholder)
        $totalVisitors = 1000; // Placeholder value
        $conversionRate = $totalVisitors > 0 ? ($totalOrders / $totalVisitors) * 100 : 0;

        // Get sales trend data (last 7 days)
        $salesTrendStmt = $conn->query("
            SELECT 
                DATE(date_of_sale) AS sale_date,
                COALESCE(SUM(stock_revenue), 0) AS daily_total
            FROM sales
            WHERE date_of_sale >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(date_of_sale)
            ORDER BY sale_date ASC
        ");

        $salesTrendData = $salesTrendStmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare data for the charts
        $salesTrendDates = [];
        $salesTrendTotals = [];

        // Fill in dates for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $salesTrendDates[] = date('M d', strtotime($date));

            // Find if there's data for this date
            $found = false;
            foreach ($salesTrendData as $data) {
                if ($data['sale_date'] == $date) {
                    $salesTrendTotals[] = (float)$data['daily_total'];
                    $found = true;
                    break;
                }
            }

            // If no data for this date, set to 0
            if (!$found) {
                $salesTrendTotals[] = 0;
            }
        }

        // Get category revenue data
        $categoryRevenueStmt = $conn->query("
            SELECT 
                COALESCE(p.brand, 'Unbranded') AS category,
                COALESCE(SUM(s.stock_revenue), 0) AS revenue
            FROM sales s
            JOIN stocks st ON s.stock_id = st.stock_id
            JOIN products p ON st.product_id = p.product_id
            GROUP BY p.brand
            ORDER BY revenue DESC
            LIMIT 5
        ");

        $categoryRevenueData = [];
        while ($row = $categoryRevenueStmt->fetch(PDO::FETCH_ASSOC)) {
            $categoryRevenueData[] = [
                'value' => (float)$row['revenue'],
                'name' => $row['category']
            ];
        }

        // First, let's get basic sales data to verify it exists
        try {
            // Simple query to get sales data first
            $recentSalesStmt = $conn->query("
                SELECT 
                    s.*,
                    pr.product as product_name,
                    pr.brand,
                    pr.model,
                    (s.stock_revenue - (s.quantity_sold * s.purchase_price)) as calculated_gross_profit,
                    ((s.stock_revenue - (s.quantity_sold * s.purchase_price)) - COALESCE(p.expenses, 0)) as calculated_net_profit,
                    p.expenses
                FROM sales s
                LEFT JOIN products pr ON s.product_id = pr.product_id
                LEFT JOIN profit p ON s.sales_id = p.sales_id
                ORDER BY s.date_of_sale DESC
            ");

            if ($recentSalesStmt === false) {
                throw new PDOException("Query failed: " . print_r($conn->errorInfo(), true));
            }

            $recentSales = $recentSalesStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug information
            error_log("Number of sales records found: " . count($recentSales));
            if (count($recentSales) === 0) {
                error_log("No sales records found in the database");
            } else {
                error_log("First sales record: " . print_r($recentSales[0], true));
            }

            // Calculate total profits
            $totalGrossProfit = 0;
            $totalNetProfit = 0;
            foreach ($recentSales as $sale) {
                $totalGrossProfit += $sale['calculated_gross_profit'];
                $totalNetProfit += $sale['calculated_net_profit'];
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $recentSales = [];
        }

        // Get profit data separately
        $profitData = [];
        if (!empty($recentSales)) {
            $salesIds = array_column($recentSales, 'sales_id');
            $placeholders = str_repeat('?,', count($salesIds) - 1) . '?';
            $profitStmt = $conn->prepare("
                SELECT sales_id, gross_profit, expenses, net_profit 
                FROM profit 
                WHERE sales_id IN ($placeholders)
            ");
            $profitStmt->execute($salesIds);
            while ($row = $profitStmt->fetch(PDO::FETCH_ASSOC)) {
                $profitData[$row['sales_id']] = $row;
            }
        }

        // Calculate average profit per order
        $avgProfitPerOrder = $totalOrders > 0 ? $totalGrossProfit / $totalOrders : 0;

        return [
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $avgOrderValue,
            'conversionRate' => $conversionRate,
            'totalGrossProfit' => $totalGrossProfit,
            'totalNetProfit' => $totalNetProfit,
            'avgProfitPerOrder' => $avgProfitPerOrder,
            'salesTrendDates' => $salesTrendDates,
            'salesTrendTotals' => $salesTrendTotals,
            'categoryRevenueData' => $categoryRevenueData,
            'recentSales' => $recentSales
        ];
    } catch (PDOException $e) {
        error_log("Error fetching sales data: " . $e->getMessage());
        return [
            'totalRevenue' => 0,
            'totalOrders' => 0,
            'avgOrderValue' => 0,
            'conversionRate' => 0,
            'totalGrossProfit' => 0,
            'totalNetProfit' => 0,
            'avgProfitPerOrder' => 0,
            'salesTrendDates' => array_map(function($i) { return date('M d', strtotime("-$i days")); }, range(6, 0)),
            'salesTrendTotals' => array_fill(0, 7, 0),
            'categoryRevenueData' => [],
            'recentSales' => []
        ];
    }
}

// Initialize metrics
$metrics = fetchSalesMetrics($conn);
$totalRevenue = $metrics['totalRevenue'];
$totalOrders = $metrics['totalOrders'];
$avgOrderValue = $metrics['avgOrderValue'];
$conversionRate = $metrics['conversionRate'];
$totalGrossProfit = $metrics['totalGrossProfit'];
$totalNetProfit = $metrics['totalNetProfit'];
$avgProfitPerOrder = $metrics['avgProfitPerOrder'];
$salesTrendDates = $metrics['salesTrendDates'];
$salesTrendTotals = $metrics['salesTrendTotals'];
$categoryRevenueData = $metrics['categoryRevenueData'];
$recentSales = $metrics['recentSales'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>View Sales - Inventory System</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }
        a {
            color: #475569;
            transition: all 0.3s ease;
        }
        a:hover {
            color: #0f172a;
            text-decoration: none;
        }
        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            background-color: #e2e8f0;
            color: #475569;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .status-badge.completed {
            background-color: #dcfce7;
            color: #166534;
            box-shadow: 0 2px 6px rgba(22, 101, 52, 0.1);
        }
        .status-badge.pending {
            background-color: #fef3c7;
            color: #92400e;
            box-shadow: 0 2px 6px rgba(146, 64, 14, 0.1);
        }
        .profit-positive {
            color: #166534;
            font-weight: 600;
        }
        .profit-negative {
            color: #991b1b;
            font-weight: 600;
        }
        main {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 2rem 3rem;
            max-width: 1400px;
            margin: 2rem auto;
        }
        .metric-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .metric-title {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
        }
        .metric-value {
            color: #0f172a;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .metric-trend {
            font-size: 0.75rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .metric-trend.positive {
            color: #166534;
        }
        .metric-trend.negative {
            color: #991b1b;
        }
        .search-container {
            position: relative;
            margin-bottom: 1rem;
        }
        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background-color: #ffffff;
        }
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .table-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        th {
            background-color: #f8fafc;
            padding: 1rem;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
        }
        td {
            padding: 1rem;
            font-size: 0.875rem;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.2s ease;
        }
        tbody tr:hover {
            background-color: #f8fafc;
        }
        tbody tr:nth-child(even) {
            background-color: #f1f5f9;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .no-results {
            text-align: center;
            padding: 2rem;
            color: #64748b;
            font-size: 0.875rem;
        }
        .chart-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .chart-title {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 1rem;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .metric-card {
                break-inside: avoid;
            }
        }
        
        /* Enhanced styles */
        .metric-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 20px 20px 0;
            border-color: transparent #e2e8f0 transparent transparent;
        }
        
        .metric-tooltip {
            position: absolute;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 100;
            display: none;
            max-width: 200px;
            line-height: 1.4;
        }
        
        .metric-card:hover .metric-tooltip {
            display: block;
        }
        
        .profit-indicator {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 8px;
        }
        
        .profit-indicator.positive {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .profit-indicator.negative {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .table-filters {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        
        .filter-button {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        
        .filter-button:hover {
            background: #e2e8f0;
        }
        
        .filter-button.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .date-range-filter {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .date-input {
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            font-size: 13px;
        }
        
        .summary-row {
            background: #f8fafc;
            font-weight: 600;
        }
        
        .chart-legend {
            display: flex;
            gap: 16px;
            margin-top: 12px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #64748b;
        }
        
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>
    <div class="flex">
        <div class="flex-1">
            <main class="p-8">
                <div class="mb-6 no-print">
                    <a href="admin.php" class="inline-flex items-center text-sm px-4 py-2 border-2 border-blue-500 rounded-md transition-all duration-300 hover:bg-black hover:text-white">
                        <div class="w-4 h-4 flex items-center justify-center mr-1">
                            <i class="ri-arrow-left-line"></i>
                        </div>
                        Back to Dashboard
                    </a>
                </div>
                
                <!-- Enhanced Sales Metrics Dashboard -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
                    <div class="metric-card">
                        <h3 class="metric-title">Total Revenue</h3>
                        <p class="metric-value">₱<?php echo number_format($totalRevenue, 2); ?></p>
                        <div class="metric-tooltip">Total revenue from all sales transactions</div>
                    </div>
                    <div class="metric-card">
                        <h3 class="metric-title">Total Orders</h3>
                        <p class="metric-value"><?php echo number_format($totalOrders); ?></p>
                        <div class="metric-tooltip">Total number of sales transactions processed</div>
                    </div>
                    <div class="metric-card">
                        <h3 class="metric-title">Average Order Value</h3>
                        <p class="metric-value">₱<?php echo number_format($avgOrderValue, 2); ?></p>
                        <div class="metric-tooltip">Average amount per sales transaction</div>
                    </div>
                    <div class="metric-card">
                        <h3 class="metric-title">Total Gross Profit</h3>
                        <p class="metric-value">
                            ₱<?php echo number_format($totalGrossProfit, 2); ?>
                            <span class="profit-indicator <?php echo $totalGrossProfit >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $totalGrossProfit >= 0 ? '+' : ''; ?><?php echo number_format(($totalGrossProfit / $totalRevenue) * 100, 1); ?>%
                            </span>
                        </p>
                        <div class="metric-tooltip">Total profit before expenses (Revenue - Cost of Goods)</div>
                    </div>
                    <div class="metric-card">
                        <h3 class="metric-title">Total Net Profit</h3>
                        <p class="metric-value">
                            ₱<?php echo number_format($totalNetProfit, 2); ?>
                            <span class="profit-indicator <?php echo $totalNetProfit >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $totalNetProfit >= 0 ? '+' : ''; ?><?php echo number_format(($totalNetProfit / $totalRevenue) * 100, 1); ?>%
                            </span>
                        </p>
                        <div class="metric-tooltip">Total profit after expenses (Gross Profit - Expenses)</div>
                    </div>
                    <div class="metric-card">
                        <h3 class="metric-title">Profit Margin</h3>
                        <p class="metric-value"><?php echo $totalRevenue > 0 ? number_format(($totalGrossProfit / $totalRevenue) * 100, 2) : '0.00'; ?>%</p>
                        <div class="metric-tooltip">Percentage of revenue that is profit</div>
                    </div>
                </div>

                <!-- Enhanced Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="chart-container">
                        <h3 class="chart-title">Sales Trend</h3>
                        <div id="salesTrendChart" style="height: 300px;"></div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <div class="legend-color" style="background: #3b82f6;"></div>
                                <span>Daily Revenue</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: rgba(59, 130, 246, 0.2);"></div>
                                <span>Trend Area</span>
                            </div>
                        </div>
                    </div>
                    <div class="chart-container">
                        <h3 class="chart-title">Revenue by Category</h3>
                        <div id="categoryPieChart" style="height: 300px;"></div>
                    </div>
                </div>

                <!-- Enhanced Sales Transactions Table -->
                <div class="table-container">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <h3 class="text-base font-semibold text-gray-800">Sales History</h3>
                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="date-range-filter">
                                    <input type="date" id="startDate" class="date-input" placeholder="Start Date">
                                    <span>to</span>
                                    <input type="date" id="endDate" class="date-input" placeholder="End Date">
                                </div>
                                <div class="search-container">
                                    <i class="ri-search-line search-icon"></i>
                                    <input type="text" id="searchInput" placeholder="Search by Product Name, Brand, or Date" class="search-input" />
                                </div>
                            </div>
                        </div>
                        <div class="table-filters mt-4">
                            <button class="filter-button active" data-filter="all">All Sales</button>
                            <button class="filter-button" data-filter="profitable">Profitable</button>
                            <button class="filter-button" data-filter="loss">Loss</button>
                            <button class="filter-button" data-filter="recent">Last 7 Days</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table id="salesTable">
                            <thead>
                                <tr>
                                    <th>Sales ID</th>
                                    <th>Stock ID</th>
                                    <th>Date of Sale</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Selling Price</th>
                                    <th>Purchase Price</th>
                                    <th>Stock Revenue</th>
                                    <th>Gross Profit</th>
                                    <th>Expenses</th>
                                    <th>Net Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentSales)): ?>
                                    <tr>
                                        <td colspan="11" class="no-results">
                                            <div class="text-center py-8">
                                                <i class="ri-inbox-line text-4xl text-gray-400 mb-2"></i>
                                                <p class="text-gray-500">No sales data available</p>
                                                <?php if (isset($e)): ?>
                                                    <p class="text-red-500 mt-2">Error: <?php echo htmlspecialchars($e->getMessage()); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $totalQuantity = 0;
                                    $totalRevenue = 0;
                                    $totalGrossProfit = 0;
                                    $totalExpenses = 0;
                                    $totalNetProfit = 0;
                                    
                                    foreach ($recentSales as $sale): 
                                        $totalQuantity += $sale['quantity_sold'];
                                        $totalRevenue += $sale['stock_revenue'];
                                        $totalGrossProfit += $sale['calculated_gross_profit'];
                                        $totalExpenses += $sale['expenses'] ?? 0;
                                        $totalNetProfit += $sale['calculated_net_profit'];
                                    endforeach;
                                    ?>
                                    <!-- Summary Row -->
                                    <tr class="summary-row">
                                        <td colspan="4">Total</td>
                                        <td><?php echo number_format($totalQuantity); ?></td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>₱<?php echo number_format($totalRevenue, 2); ?></td>
                                        <td class="<?php echo $totalGrossProfit >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                            <?php echo $totalGrossProfit >= 0 ? '+' : ''; ?>₱<?php echo number_format($totalGrossProfit, 2); ?>
                                        </td>
                                        <td>₱<?php echo number_format($totalExpenses, 2); ?></td>
                                        <td class="<?php echo $totalNetProfit >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                            <?php echo $totalNetProfit >= 0 ? '+' : ''; ?>₱<?php echo number_format($totalNetProfit, 2); ?>
                                        </td>
                                    </tr>
                                    <?php foreach ($recentSales as $sale): ?>
                                        <tr>
                                            <td class="font-medium">#<?php echo htmlspecialchars($sale['sales_id']); ?></td>
                                            <td>#<?php echo htmlspecialchars($sale['stock_id']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($sale['date_of_sale'])); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($sale['product_name'] ?? 'N/A'); ?>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($sale['brand'] ?? 'N/A'); ?> 
                                                    <?php echo htmlspecialchars($sale['model'] ?? ''); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($sale['quantity_sold']); ?></td>
                                            <td>₱<?php echo number_format($sale['selling_price'], 2); ?></td>
                                            <td>₱<?php echo number_format($sale['purchase_price'], 2); ?></td>
                                            <td>₱<?php echo number_format($sale['stock_revenue'], 2); ?></td>
                                            <td class="<?php echo $sale['calculated_gross_profit'] > 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                                <?php echo $sale['calculated_gross_profit'] > 0 ? '+' : ''; ?>₱<?php echo number_format($sale['calculated_gross_profit'], 2); ?>
                                            </td>
                                            <td>₱<?php echo number_format($sale['expenses'] ?? 0, 2); ?></td>
                                            <td class="<?php echo $sale['calculated_net_profit'] > 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                                <?php echo $sale['calculated_net_profit'] > 0 ? '+' : ''; ?>₱<?php echo number_format($sale['calculated_net_profit'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Show loading overlay
            const loadingOverlay = document.querySelector('.loading-overlay');
            loadingOverlay.classList.add('active');

            // Initialize charts only if their containers exist
            const salesTrendChartContainer = document.getElementById('salesTrendChart');
            const categoryPieChartContainer = document.getElementById('categoryPieChart');
            
            let salesTrendChart = null;
            let categoryPieChart = null;

            if (salesTrendChartContainer) {
                salesTrendChart = echarts.init(salesTrendChartContainer);
                const salesTrendOption = {
                    tooltip: {
                        trigger: 'axis',
                        formatter: function(params) {
                            return params[0].name + '<br/>' + 
                                   params[0].seriesName + ': ₱' + params[0].value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: <?php echo json_encode($salesTrendDates); ?>,
                        axisLine: { lineStyle: { color: '#cbd5e1' } },
                        axisLabel: { color: '#64748b' }
                    },
                    yAxis: {
                        type: 'value',
                        axisLine: { show: false },
                        axisTick: { show: false },
                        splitLine: { lineStyle: { color: '#e2e8f0' } },
                        axisLabel: { 
                            color: '#64748b',
                            formatter: function(value) {
                                return '₱' + value.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                            }
                        }
                    },
                    series: [{
                        name: 'Revenue',
                        data: <?php echo json_encode($salesTrendTotals); ?>,
                        type: 'line',
                        smooth: true,
                        lineStyle: { width: 3, color: '#3b82f6' },
                        areaStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: 'rgba(59, 130, 246, 0.2)' },
                                { offset: 1, color: 'rgba(59, 130, 246, 0.05)' }
                            ])
                        },
                        symbol: 'circle',
                        symbolSize: 8,
                        itemStyle: {
                            color: '#3b82f6',
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }
                    }]
                };
                salesTrendChart.setOption(salesTrendOption);
            }

            if (categoryPieChartContainer) {
                categoryPieChart = echarts.init(categoryPieChartContainer);
                const categoryData = <?php echo json_encode($categoryRevenueData); ?>;
                const categoryPieOption = {
                    tooltip: {
                        trigger: 'item',
                        formatter: function(params) {
                            return params.name + '<br/>' + 
                                   'Revenue: ₱' + params.value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + 
                                   ' (' + params.percent + '%)';
                        }
                    },
                    legend: {
                        bottom: 0,
                        left: 'center',
                        textStyle: { color: '#64748b' },
                        formatter: function(name) {
                            return name.length > 12 ? name.substring(0, 10) + '...' : name;
                        }
                    },
                    series: [{
                        name: 'Revenue',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        avoidLabelOverlap: false,
                        itemStyle: { 
                            borderRadius: 8, 
                            borderColor: '#ffffff', 
                            borderWidth: 2 
                        },
                        label: { show: false },
                        emphasis: { 
                            label: { 
                                show: true, 
                                fontSize: '12', 
                                fontWeight: 'bold' 
                            } 
                        },
                        labelLine: { show: false },
                        data: categoryData.length > 0 ? categoryData : [{ value: 0, name: 'No Data' }]
                    }]
                };
                categoryPieChart.setOption(categoryPieOption);
            }

            // Enhanced search and filter functionality
            const searchInput = document.getElementById('searchInput');
            const salesTable = document.getElementById('salesTable');
            const filterButtons = document.querySelectorAll('.filter-button');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            
            function applyFilters() {
                const searchFilter = searchInput.value.toLowerCase().trim();
                const dateFilter = {
                    start: startDate.value ? new Date(startDate.value) : null,
                    end: endDate.value ? new Date(endDate.value) : null
                };
                const activeFilter = document.querySelector('.filter-button.active').dataset.filter;
                
                const rows = salesTable.tBodies[0].rows;
                let hasResults = false;
                
                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    if (row.classList.contains('summary-row')) continue;
                    
                    const product = row.cells[3].textContent.toLowerCase();
                    const date = new Date(row.cells[2].textContent);
                    const grossProfit = parseFloat(row.cells[8].textContent.replace(/[^0-9.-]+/g, ''));
                    const isRecent = (new Date() - date) <= 7 * 24 * 60 * 60 * 1000;
                    
                    let show = true;
                    
                    // Apply search filter
                    if (searchFilter && !product.includes(searchFilter)) {
                        show = false;
                    }
                    
                    // Apply date filter
                    if (dateFilter.start && date < dateFilter.start) show = false;
                    if (dateFilter.end && date > dateFilter.end) show = false;
                    
                    // Apply category filter
                    switch (activeFilter) {
                        case 'profitable':
                            if (grossProfit <= 0) show = false;
                            break;
                        case 'loss':
                            if (grossProfit >= 0) show = false;
                            break;
                        case 'recent':
                            if (!isRecent) show = false;
                            break;
                    }
                    
                    row.style.display = show ? '' : 'none';
                    if (show) hasResults = true;
                }
                
                // Update no results message
                const noResultsRow = salesTable.querySelector('.no-results');
                if (!hasResults && !noResultsRow) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="11" class="no-results"><div class="text-center py-8"><i class="ri-search-line text-4xl text-gray-400 mb-2"></i><p class="text-gray-500">No matching results found</p></div></td>';
                    salesTable.tBodies[0].appendChild(tr);
                } else if (hasResults && noResultsRow) {
                    noResultsRow.parentNode.removeChild(noResultsRow);
                }
            }
            
            // Event listeners for filters
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    applyFilters();
                });
            });
            
            [searchInput, startDate, endDate].forEach(element => {
                if (element) {
                    element.addEventListener('input', () => {
                        clearTimeout(window.searchTimeout);
                        window.searchTimeout = setTimeout(applyFilters, 300);
                    });
                }
            });

            // Resize charts when window size changes
            const resizeCharts = () => {
                if (salesTrendChart) salesTrendChart.resize();
                if (categoryPieChart) categoryPieChart.resize();
            };

            window.addEventListener('resize', resizeCharts);
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (salesTrendChart) salesTrendChart.dispose();
                if (categoryPieChart) categoryPieChart.dispose();
            });

            // Hide loading overlay after everything is loaded
            setTimeout(() => {
                loadingOverlay.classList.remove('active');
            }, 500);
        });
    </script>
</body>
</html>