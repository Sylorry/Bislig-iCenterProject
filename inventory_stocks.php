<?php
session_start();
require_once 'db.php';

// Get database connection
try {
    $pdo = getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Inventory - iCenter</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: { primary: "#1a1a1a", secondary: "#404040" },
            borderRadius: {
              none: "0px",
              sm: "4px",
              DEFAULT: "8px",
              md: "12px",
              lg: "16px",
              xl: "20px",
              "2xl": "24px",
              "3xl": "32px",
              full: "9999px",
              button: "8px",
            },
          },
        },
      };
    </script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    
    <!-- Animate.css for smooth animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Remixicon for enhanced icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <!-- Additional libraries for export functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
      body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
      }
      
      /* Enhanced Header */
      header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
      }
      
      /* Header specific styles */
      .header-logo {
        height: 65px;
        width: auto;
        border: 2px solid white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      }
      
      .header-date {
        font-size: 14px;
        font-weight: 600;
      }
      
      .header-time {
        font-size: 12px;
        opacity: 0.8;
      }
      
      .admin-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
      }
      
      .admin-avatar:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
      }
      
      .admin-label {
        font-size: 10px;
        font-weight: 600;
        margin-top: 4px;
      }
    </style>
</head>
<body>

<!-- Enhanced Header -->
<header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
  <div class="flex justify-between items-center px-8 py-6 space-x-4">
    <div class="flex items-center space-x-6">
      <div class="ml-2 mr-10 text-sm text-white flex items-center space-x-6">
        <img src="images/iCenter.png" alt="Logo" class="h-20 w-auto border-2 border-white rounded-lg shadow-lg mr-4" />
        <div class="flex flex-col space-y-1">
          <span class="font-semibold text-lg"><?php echo date('l, F j, Y'); ?></span>
          <div class="text-white/80 text-sm">
            <i class="ri-time-line mr-2"></i>
            <span id="currentTime"></span>
          </div>
    </div>
  </div>
</div>
    <div class="flex items-center space-x-8">
      <div class="flex items-center space-x-4">
      <div class="flex flex-col items-center group cursor-pointer">
         <div
          class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-black font-medium shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-110"
         >
          <i class="ri-user-line text-lg"></i>
         </div>
        <span class="text-white text-xs font-semibold mt-2 group-hover:text-blue-300 transition-colors duration-300">ADMIN</span>
       </div>
     </div>
   </div>
 </header>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="header-section">
        <!-- Back Button -->
        <div class="back-button-container">
            <a href="admin.php" class="btn-back" title="Back to Admin Dashboard">
                <i class="fas fa-arrow-left mr-2"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <!-- Main Card -->
            <div class="main-card animate__animated animate__fadeInUp animate__delay-1s">
                <div class="card-header">

                    <!-- Stats Cards below title -->
                    <div class="stats-section" style="margin: 0 0 20px 0;">
                        <div class="row g-3 mb-0 justify-content-center">
                            <?php
                            try {
                                // Total products
                                $totalQuery = "SELECT COUNT(*) as total FROM products WHERE (archived IS NULL OR archived = 0)";
                                $totalStmt = $pdo->prepare($totalQuery);
                                $totalStmt->execute();
                                $totalProducts = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                                // Low stock products
                                $lowStockQuery = "SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity <= 5 AND stock_quantity > 0 AND (archived IS NULL OR archived = 0)";
                                $lowStockStmt = $pdo->prepare($lowStockQuery);
                                $lowStockStmt->execute();
                                $lowStockProducts = $lowStockStmt->fetch(PDO::FETCH_ASSOC)['low_stock'] ?? 0;
                                // Out of stock products
                                $outOfStockQuery = "SELECT COUNT(*) as out_of_stock FROM products WHERE stock_quantity = 0 AND (archived IS NULL OR archived = 0)";
                                $outOfStockStmt = $pdo->prepare($outOfStockQuery);
                                $outOfStockStmt->execute();
                                $outOfStockProducts = $outOfStockStmt->fetch(PDO::FETCH_ASSOC)['out_of_stock'] ?? 0;
                                // In stock products (products with stock > 5)
                                $inStockQuery = "SELECT COUNT(*) as in_stock FROM products WHERE stock_quantity > 5 AND (archived IS NULL OR archived = 0)";
                                $inStockStmt = $pdo->prepare($inStockQuery);
                                $inStockStmt->execute();
                                $inStockProducts = $inStockStmt->fetch(PDO::FETCH_ASSOC)['in_stock'] ?? 0;
                            } catch (PDOException $e) {
                                error_log("Database error: " . $e->getMessage());
                                $totalProducts = $lowStockProducts = $outOfStockProducts = $inStockProducts = 0;
                            }
                            ?>
                            <div class="col-xl-3 col-md-6">
                                <div class="stat-card stat-card-primary">
                                    <div class="stat-icon">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-number"><?= number_format($totalProducts) ?></h3>
                                        <p class="stat-label">Total Products</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="stat-card stat-card-warning" id="lowStockCard" style="cursor:pointer;" title="Click to view low stock items">
                                    <div class="stat-icon">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-number"><?= number_format($lowStockProducts) ?></h3>
                                        <p class="stat-label">Low Stock</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="stat-card stat-card-danger" id="outOfStockCard" style="cursor:pointer;" title="Click to view out of stock items">
                                    <div class="stat-icon">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-number"><?= number_format($outOfStockProducts) ?></h3>
                                        <p class="stat-label">Out of Stock</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="stat-card stat-card-success">
                                    <div class="stat-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-number"><?= number_format($inStockProducts) ?></h3>
                                        <p class="stat-label">In Stock</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="action-buttons" style="margin-top: 2px; margin-bottom: 20px;">
                        <button class="btn btn-action" onclick="viewStockInReport()" title="View Stock In Report" style="background: #000000; color: #ffffff; border: 2px solid #000000;">
                            <i class="fas fa-arrow-down"></i>
                            <span>Stock In</span>
                        </button>
                        <button class="btn btn-action" onclick="viewStockOutReport()" title="View Stock Out Report" style="background: #000000; color: #ffffff; border: 2px solid #000000;">
                            <i class="fas fa-arrow-up"></i>
                            <span>Stock Out</span>
                        </button>
                        <button class="btn btn-action" onclick="viewReturnsReport()" title="Report Defect" style="background: #000000; color: #ffffff; border: 2px solid #000000;">
                            <i class="fas fa-undo"></i>
                            <span>Report Defect</span>
                        </button>
                        <button class="btn btn-action" onclick="openRestockPage()" title="Restock Products" style="background: #000000; color: #ffffff; border: 2px solid #000000;">
                            <i class="fas fa-plus-circle"></i>
                            <span>Restock</span>
                        </button>
                        <button class="btn btn-action" onclick="openReturnsPage()" title="Returns" style="background: #000000; color: #ffffff; border: 2px solid #000000;">
                            <i class="fas fa-undo"></i>
                            <span>Returns</span>
                        </button>
                        <button class="btn btn-action" onclick="printTable()" title="Print Inventory" style="background: #000000; color: #ffffff; border: 2px solid #000000;">
                            <i class="fas fa-print"></i>
                            <span>Print</span>
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="table-container">
                        <table id="productsTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: auto; min-width: 250px;">Product<br>ID</th>
                                    <th class="text-center" style="width: 200px; min-width: 200px;">Product<br>Name</th>
                                    <th class="text-center" style="width: 100px; min-width: 100px;">Brand</th>
                                    <th class="text-center" style="width: 100px; min-width: 100px;">Model</th>
                                    <th class="text-center" style="width: 100px; min-width: 100px;">Storage</th>
                                    <th class="text-center" style="width: 180px; min-width: 180px; max-width: 180px;">Stock<br>Level</th>
                                    <th class="text-center" style="width: 100px; min-width: 100px; max-width: 100px;">Purchase<br>Price</th>
                                    <th class="text-center" style="width: 100px; min-width: 100px; max-width: 100px;">Selling<br>Price</th>
                                    <th class="text-center" style="width: 80px; min-width: 80px; max-width: 80px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $query = "SELECT * FROM products WHERE (archived IS NULL OR archived = 0) ORDER BY product ASC";
                                    $stmt = $pdo->prepare($query);
                                    $stmt->execute();
                                    
                                    // Debug: Check if we have data
                                    $rowCount = $stmt->rowCount();
                                    if ($rowCount === 0) {
                                        echo "<tr><td colspan='9' class='text-center text-warning'>No products found in database. Please add some products first.</td></tr>";
                                    } else {
                                        echo "<!-- Found {$rowCount} products -->"; // Debug comment
                                    }
                                    
                                    if ($stmt) {
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            // Safely handle null values
                                            $productId = htmlspecialchars($row['product_id'] ?? '');
                                            $product = htmlspecialchars($row['product'] ?? '');
                                            $brand = htmlspecialchars($row['brand'] ?? '');
                                            $model = htmlspecialchars($row['model'] ?? '');
                                            $storage = htmlspecialchars($row['storage'] ?? '');
                                            $stockQty = intval($row['stock_quantity'] ?? 0);
                                            $purchasePrice = floatval($row['purchase_price'] ?? 0);
                                            $sellingPrice = floatval($row['selling_price'] ?? 0);
                                            $status = htmlspecialchars($row['status'] ?? 'inactive');

                                            // Determine stock status
                                            $stockClass = 'stock-high';
                                            $stockIcon = 'fas fa-check-circle';
                                            $stockLabel = 'In Stock';
                                            if ($stockQty <= 0) {
                                                $stockClass = 'stock-out';
                                                $stockIcon = 'fas fa-times-circle';
                                                $stockLabel = 'Out of Stock';
                                            } elseif ($stockQty <= 5) {
                                                $stockClass = 'stock-low';
                                                $stockIcon = 'fas fa-exclamation-triangle';
                                                $stockLabel = 'Low Stock';
                                            }

                                            // Determine status badge class
                                            $statusClass = 'status-badge ';
                                            switch(strtolower($status)) {
                                                case 'active':
                                                    $statusClass .= 'status-active';
                                                    break;
                                                case 'inactive':
                                                    $statusClass .= 'status-inactive';
                                                    break;
                                                case 'pending':
                                                    $statusClass .= 'status-pending';
                                                    break;
                                                default:
                                                    $statusClass .= 'status-secondary';
                                            }

                                            echo "<tr class='product-row'>";
                                            echo "<td class='product-id'>{$productId}</td>";
                                            echo "<td class='product-name'>{$product}</td>";
                                            echo "<td class='product-brand'>{$brand}</td>";
                                            echo "<td class='product-model'>{$model}</td>";
                                            echo "<td class='product-storage'>{$storage}</td>";
                                            echo "<td class='stock-cell'>";
                                            echo "<div class='stock-indicator {$stockClass}'>";
                                            echo "<i class='{$stockIcon}'></i>";
                                            echo "<span class='stock-number'>{$stockQty}</span>";
                                            echo "<span class='stock-label'>{$stockLabel}</span>";
                                            echo "</div>";
                                            echo "</td>";
                                            echo "<td class='price-cell'>₱" . number_format($purchasePrice, 2) . "</td>";
                                            echo "<td class='price-cell'>₱" . number_format($sellingPrice, 2) . "</td>";
                                            echo "<td class='status-cell'>";
                                            echo "<span class='{$statusClass}'>{$status}</span>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9' class='text-center no-data'>No products available</td></tr>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<tr><td colspan='9' class='text-center text-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                                    error_log("Database error in products table: " . $e->getMessage());
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Scroll to top button -->
                    <div class="scroll-to-top-container">
                        <button id="scrollToTop" class="btn btn-primary scroll-to-top-btn" title="Scroll to top">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Modal -->
<div class="modal fade" id="quickStatsModal" tabindex="-1" aria-labelledby="quickStatsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickStatsModalLabel">
                    <i class="fas fa-chart-bar"></i> Inventory Analytics
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="stockChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock In Report Modal -->
<div class="modal fade" id="stockInModal" tabindex="-1" aria-labelledby="stockInModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="stockInModalLabel">
                    <i class="fas fa-arrow-down"></i> Stock In Report - Movement History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="date" id="stockInStartDate" class="form-control" onchange="filterStockInReport()">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="date" id="stockInEndDate" class="form-control" onchange="filterStockInReport()">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="stockInSearch" class="form-control" placeholder="Search products, brands, models..." onkeyup="searchStockInTable()">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-success w-100" onclick="refreshStockInData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-info w-100" onclick="clearStockInDates()" title="Show all records">
                            <i class="fas fa-list"></i> Show All
                        </button>
                    </div>
                </div>
                
                <!-- Summary Cards -->
                <div class="row mb-4" id="stockInSummary" style="display: none;">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Date Range</h6>
                                <h6 id="dateRange">-</h6>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                    <table id="stockInTable" class="table table-hover table-striped">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th style="min-width: 140px;"><i class="fas fa-calendar"></i> Date & Time</th>
                                <th style="min-width: 200px;"><i class="fas fa-barcode"></i> Product ID</th>
                                <th style="min-width: 120px;"><i class="fas fa-box"></i> Product Name</th>
                                <th style="min-width: 100px;"><i class="fas fa-tag"></i> Brand</th>
                                <th style="min-width: 150px;"><i class="fas fa-cog"></i> Model</th>
                                <th style="min-width: 100px;"><i class="fas fa-hdd"></i> Storage</th>
                                <th style="min-width: 120px;"><i class="fas fa-plus-circle text-success"></i> Quantity Added</th>
                                <th style="min-width: 120px;"><i class="fas fa-arrow-left"></i> Previous Stock</th>
                                <th style="min-width: 120px;"><i class="fas fa-arrow-right"></i> New Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Stock in data will be loaded here -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Loading indicator -->
                <div id="stockInLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading stock in data...</p>
                </div>
                
                <!-- No data message -->
                <div id="stockInNoData" class="text-center py-5" style="display: none;">
                    <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No Stock In Records Found</h4>
                    <p class="text-muted">No stock in movements were recorded for the selected date range.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Out Report Modal -->
<div class="modal fade" id="stockOutModal" tabindex="-1" aria-labelledby="stockOutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="stockOutModalLabel">
                    <i class="fas fa-arrow-up"></i> Stock Out Report - Movement History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="date" id="stockOutStartDate" class="form-control" onchange="filterStockOutReport()">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="date" id="stockOutEndDate" class="form-control" onchange="filterStockOutReport()">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="stockOutSearch" class="form-control" placeholder="Search products, brands, models..." onkeyup="searchStockOutTable()">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger w-100" onclick="refreshStockOutData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-info w-100" onclick="clearStockOutDates()" title="Show all records">
                            <i class="fas fa-list"></i> Show All
                        </button>
                    </div>
                </div>
                
                <!-- Summary Cards -->
                <div class="row mb-4" id="stockOutSummary" style="display: none;">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Date Range</h6>
                                <h6 id="outDateRange">-</h6>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                    <table id="stockOutTable" class="table table-hover table-striped">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th style="min-width: 140px;"><i class="fas fa-calendar"></i> Date & Time</th>
                                <th style="min-width: 200px;"><i class="fas fa-barcode"></i> Product ID</th>
                                <th style="min-width: 120px;"><i class="fas fa-box"></i> Product Name</th>
                                <th style="min-width: 100px;"><i class="fas fa-tag"></i> Brand</th>
                                <th style="min-width: 150px;"><i class="fas fa-cog"></i> Model</th>
                                <th style="min-width: 100px;"><i class="fas fa-hdd"></i> Storage</th>
                                <th style="min-width: 120px;"><i class="fas fa-minus-circle text-danger"></i> Quantity Removed</th>
                                <th style="min-width: 120px;"><i class="fas fa-arrow-left"></i> Previous Stock</th>
                                <th style="min-width: 120px;"><i class="fas fa-arrow-right"></i> New Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Stock out data will be loaded here -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Loading indicator -->
                <div id="stockOutLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading stock out data...</p>
                </div>
                
                <!-- No data message -->
                <div id="stockOutNoData" class="text-center py-5" style="display: none;">
                    <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No Stock Out Records Found</h4>
                    <p class="text-muted">No stock out movements were recorded for the selected date range.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Returns Report Modal -->
<div class="modal fade" id="returnsModal" tabindex="-1" aria-labelledby="returnsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="returnsModalLabel">
                    <i class="fas fa-undo"></i> Returns Report - Defective Items
                </h5>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="date" id="returnsStartDate" class="form-control" onchange="filterReturnsReport()">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="date" id="returnsEndDate" class="form-control" onchange="filterReturnsReport()">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="returnsSearch" class="form-control" placeholder="Search products, brands, models..." onkeyup="searchReturnsTable()">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-warning w-100" onclick="refreshReturnsData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-info w-100" onclick="clearReturnsDates()" title="Show all records">
                            <i class="fas fa-list"></i> Show All
                        </button>
                    </div>
                </div>
                <div class="row mb-4" id="returnsSummary" style="display: none;">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center">
                                <h6 class="card-title">Total Returns</h6>
                                <h4 id="totalReturns">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card bg-info text-dark">
                            <div class="card-body text-center">
                                <h6 class="card-title">Total Quantity Returned</h6>
                                <h4 id="totalQuantityReturned">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card bg-secondary text-dark">
                            <div class="card-body text-center">
                                <h6 class="card-title">Unique Products</h6>
                                <h4 id="uniqueReturnedProducts">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card bg-primary text-dark">
                            <div class="card-body text-center">
                                <h6 class="card-title">Date Range</h6>
                                <h6 id="returnsDateRange">-</h6>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                    <table id="returnsTable" class="table table-hover table-striped">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th style="min-width: 140px;"><i class="fas fa-calendar"></i> Date & Time</th>
                                <th style="min-width: 200px;"><i class="fas fa-barcode"></i> Product ID</th>
                                <th style="min-width: 120px;"><i class="fas fa-box"></i> Product Name</th>
                                <th style="min-width: 100px;"><i class="fas fa-tag"></i> Brand</th>
                                <th style="min-width: 150px;"><i class="fas fa-cog"></i> Model</th>
                                <th style="min-width: 100px;"><i class="fas fa-hdd"></i> Storage</th>
                                <th style="min-width: 120px;"><i class="fas fa-undo text-warning"></i> Quantity Returned</th>
                                <th style="min-width: 120px;"><i class="fas fa-arrow-left"></i> Previous Stock</th>
                                <th style="min-width: 120px;"><i class="fas fa-arrow-right"></i> New Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Returns data will be loaded here -->
                        </tbody>
                    </table>
                </div>
                <div id="returnsLoading" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading returns data...</p>
                </div>
                <div id="returnsNoData" class="text-center py-5" style="display: none;">
                    <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No Returns Records Found</h4>
                    <p class="text-muted">No returns were recorded for the selected date range.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Modal -->
<div class="modal fade" id="lowStockModal" tabindex="-1" aria-labelledby="lowStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                    <div>
                        <h5 class="modal-title mb-0" id="lowStockModalLabel">Low Stock Items</h5>
                        <small class="text-dark">Products with 5 or fewer stocks remaining</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3" id="lowStockSummaryRow" style="display:none;">
                    <div class="col-md-6 mb-2">
                        <div class="card bg-warning bg-opacity-75 text-dark shadow-sm">
                            <div class="card-body py-2 text-center">
                                <div class="fw-bold" style="font-size:1.2rem;">Total Low Stock Items</div>
                                <div id="lowStockTotalItems" style="font-size:2rem;">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="card bg-danger bg-opacity-75 text-white shadow-sm">
                            <div class="card-body py-2 text-center">
                                <div class="fw-bold" style="font-size:1.2rem;">Total Quantity Left</div>
                                <div id="lowStockTotalQty" style="font-size:2rem;">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Search box removed -->
                <div id="lowStockLoading" class="text-center py-4" style="display:none;">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading low stock items...</p>
                </div>
                <div id="lowStockNoData" class="text-center py-4" style="display:none;">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Low Stock Items</h5>
                    <p class="text-muted">All products have sufficient stock.</p>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle" id="lowStockTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Product ID</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Storage</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Out of Stock Modal (copied and adapted from Low Stock Modal) -->
<div class="modal fade" id="outOfStockModal" tabindex="-1" aria-labelledby="outOfStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-times-circle fa-lg"></i>
                    <div>
                        <h5 class="modal-title mb-0" id="outOfStockModalLabel">Out of Stock Items</h5>
                        <small class="text-white">Products with 0 stock remaining</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="outOfStockLoading" class="text-center py-4" style="display:none;">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading out of stock items...</p>
                </div>
                <div id="outOfStockNoData" class="text-center py-4" style="display:none;">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Out of Stock Items</h5>
                    <p class="text-muted">All products have stock available.</p>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle outOfStockTable" id="outOfStockTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Product ID</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Storage</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern CSS Variables */
:root {
    --primary-color: #2563eb;
    --primary-dark: #1d4ed8;
    --secondary-color: #64748b;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #06b6d4;
    --light-color: #f8fafc;
    --dark-color: #1e293b;
    --border-color: #e2e8f0;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --border-radius: 12px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #fff;
    min-height: 100vh;
    color: #000000;
    line-height: 1.6;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.95) 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(10px);
}

.loading-content {
    text-align: center;
    background: white;
    padding: 40px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
}

.loading-content .spinner-border {
    width: 4rem;
    height: 4rem;
    border-width: 0.25em;
}

/* Container */
.container-fluid {
    max-width: 1800px;
    margin: 0 auto;
    padding: 20px;
    position: relative;
}

/* Header Section */
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px 0;
}

.logo-container {
    display: flex;
    align-items: center;
}

.logo-img {
    height: 100px;
    width: auto;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
}

.back-button-container {
    display: flex;
    align-items: center;
}

.btn-back {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 500;
    transition: var(--transition);
    text-decoration: none;
    background: #000;
    backdrop-filter: blur(10px);
    border: 2px solid #000;
    color: #fff;
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    background: #000;
    color: white;
    border-color: #000;
}

/* Stats Section */
.stats-section {
    margin-bottom: 30px;
}

.stat-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 2px solid #000000;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
}

.stat-card:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1), 0 1px 2px -2px rgba(0, 0, 0, 0.1);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.stat-card:hover::before {
    left: 100%;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-card-primary .stat-icon {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
}

.stat-card-success .stat-icon {
    background: linear-gradient(135deg, var(--success-color), #059669);
}

.stat-card-warning .stat-icon {
    background: linear-gradient(135deg, var(--warning-color), #d97706);
}

.stat-card-danger .stat-icon {
    background: linear-gradient(135deg, var(--danger-color), #dc2626);
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: var(--dark-color);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--secondary-color);
    margin: 0;
    font-weight: 500;
}

/* Main Card */
.main-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
    width: 100%;
}

.card-header {
    background: #ffffff;
    color: #333;
    padding: 30px;
    border: none;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.title-section {
    flex: 1;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
    background: transparent;
    color: #333;
    padding: 15px 20px;
    border-radius: 8px;
}

.page-subtitle {
    margin: 8px 0 0 0;
    opacity: 0.9;
    font-size: 1rem;
}

.action-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
}

.btn-action {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 500;
    transition: var(--transition);
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
}

.btn-action:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
    background: #ffffff !important;
    color: #000000 !important;
    border-color: #000000 !important;
}

.btn-action.btn-primary {
    background: rgba(255, 255, 255, 0.2);
}

.btn-action.btn-success {
    background: rgba(16, 185, 129, 0.8);
}

/* Card Body */
.card-body {
    padding: 30px;
}

/* Table Container - Ensure visibility */
.table-container {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    background: white;
    border-radius: 12px;
    overflow: auto;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    max-height: 600px; /* Fixed height for scrolling */
    overflow-y: auto; /* Enable vertical scrolling */
    overflow-x: auto; /* Enable horizontal scrolling if needed */
    width: 100%;
}

/* Custom scrollbar styling */
.table-container::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
    transition: background 0.3s ease;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Firefox scrollbar styling */
.table-container {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

/* Table Styles */
.table {
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    display: table !important;
    visibility: visible !important;
    border: none;
    border-radius: 0;
    overflow: hidden;
    background: white;
    table-layout: fixed; /* Ensure consistent column widths */
}

.table thead th {
    background: linear-gradient(135deg, var(--dark-color), #374151);
    color: white;
    font-weight: 600;
    padding: 16px 12px;
    border: none;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    position: sticky;
    top: 0;
    z-index: 10;
    white-space: normal;
    vertical-align: middle;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-right: 1px solid rgba(255, 255, 255, 0.1);
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
    font-family: Arial, sans-serif;
}

.table thead th:last-child {
    border-right: none;
}

.table tbody {
    display: table-row-group !important;
    visibility: visible !important;
}

.table tbody tr {
    display: table-row !important;
    visibility: visible !important;
    transition: var(--transition);
    border-bottom: 1px solid var(--border-color);
}

.table tbody tr:last-child {
    border-bottom: none;
}

.table tbody tr:hover {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
    transform: translateY(-2px) scale(1.01);
    box-shadow: var(--shadow-md);
}

.table tbody td {
    display: table-cell !important;
    visibility: visible !important;
    padding: 16px 12px;
    vertical-align: middle;
    border: none;
    font-size: 0.875rem;
    word-wrap: break-word;
    border-right: 1px solid var(--border-color);
    position: relative;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-family: Arial, sans-serif;
    color: #000000;
}

.table tbody td:last-child {
    border-right: none;
}

/* Header alignment classes */
.table thead th.text-center {
    text-align: center;
}

.table thead th.text-end {
    text-align: right;
}

/* Enhanced vertical dividers with better styling */
.table tbody td::after {
    content: '';
    position: absolute;
    top: 8px;
    right: 0;
    width: 1px;
    height: calc(100% - 16px);
    background: linear-gradient(to bottom, 
        transparent 0%, 
        var(--border-color) 20%, 
        var(--border-color) 80%, 
        transparent 100%);
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.table tbody tr:hover td::after {
    opacity: 1;
    background: linear-gradient(to bottom, 
        transparent 0%, 
        var(--primary-color) 20%, 
        var(--primary-color) 80%, 
        transparent 100%);
    box-shadow: 0 0 4px rgba(37, 99, 235, 0.3);
}

/* Header vertical dividers with enhanced styling */
.table thead th::after {
    content: '';
    position: absolute;
    top: 8px;
    right: 0;
    width: 1px;
    height: calc(100% - 16px);
    background: linear-gradient(to bottom, 
        transparent 0%, 
        rgba(255, 255, 255, 0.3) 20%, 
        rgba(255, 255, 255, 0.3) 80%, 
        transparent 100%);
    opacity: 0.8;
}

/* Responsive vertical dividers */
@media (max-width: 768px) {
    .table tbody td::after,
    .table thead th::after {
        top: 4px;
        height: calc(100% - 8px);
    }
}

@media (max-width: 576px) {
    .table tbody td::after,
    .table thead th::after {
        top: 2px;
        height: calc(100% - 4px);
        opacity: 0.5;
    }
}

/* Product Row Styles */
.product-id {
    font-weight: 600;
    color: #000000;
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
    min-width: 250px;
    text-align: center;
}

.product-name {
    font-weight: 500;
    color: #000000;
}

.product-brand {
    color: #000000;
}

.product-model {
    font-family: Arial, sans-serif;
    color: #000000;
}

.product-storage {
    color: #000000;
}

/* Stock Indicator */
.stock-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.875rem;
    position: relative;
    overflow: hidden;
    font-family: Arial, sans-serif;
}

.stock-indicator::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.stock-indicator:hover::after {
    transform: translateX(100%);
}

.stock-high {
    background: #dcfce7;
    color: #000000;
}

.stock-low {
    background: #fef3c7;
    color: #000000;
}

.stock-out {
    background: #fee2e2;
    color: #000000;
}

.stock-number {
    font-weight: 700;
    font-size: 1rem;
    color: #000000;
}

.stock-label {
    font-size: 0.75rem;
    opacity: 0.8;
    color: #000000;
}

/* Price Cells */
.price-cell {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: var(--dark-color);
    text-align: right;
}

/* Status Badge */
.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-family: Arial, sans-serif;
}

.status-active {
    background: #dcfce7;
    color: #000000;
}

.status-inactive {
    background: #fee2e2;
    color: #000000;
}

.status-pending {
    background: #fef3c7;
    color: #000000;
}

.status-secondary {
    background: #f1f5f9;
    color: #000000;
}

/* Actions Cell */
.actions-cell {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.action-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.3s, height 0.3s;
}

.action-btn:hover::before {
    width: 100%;
    height: 100%;
}

/* Modal Styles */
.modal-content {
    border-radius: var(--border-radius);
    border: none;
    box-shadow: var(--shadow-lg);
}

.modal-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.modal-title {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container-fluid {
        padding: 10px;
    }
    
    .header-section {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .action-buttons {
        justify-content: center;
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
    }
    
    .table-responsive {
        font-size: 0.75rem;
    }
    
    .actions-cell {
        flex-direction: column;
    }
    
    .table thead th {
        font-size: 0.75rem;
        padding: 12px 8px;
    }
    
    .table thead th:not(:first-child):not(:last-child) {
        min-width: 80px;
    }
}

@media (max-width: 576px) {
    .table thead th {
        font-size: 0.7rem;
        padding: 8px 4px;
    }
}

/* Print Styles */
@media print {
    /* Set landscape orientation */
    @page {
        size: landscape;
        margin: 0.5in;
    }
    
    /* Hide unnecessary elements */
    .loading-overlay,
    .back-button-container,
    .action-buttons,
    .actions-cell,
    .stats-section,
    .dataTables_filter,
    .dataTables_length,
    .dataTables_info,
    .dataTables_paginate,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate,
    .dataTables_wrapper .row:first-child,
    .dataTables_wrapper .row:last-child,
    .dataTables_wrapper .col-sm-12.col-md-6:first-child,
    .dataTables_wrapper .col-sm-12.col-md-6:last-child,
    .dataTables_wrapper .col-sm-12.col-md-5,
    .dataTables_wrapper .col-sm-12.col-md-7,
    .scroll-to-top-container,
    .scroll-to-top-btn {
        display: none !important;
    }
    
    /* Basic container reset */
    .container-fluid {
        padding: 0 !important;
        margin: 0 !important;
        max-width: none !important;
    }
    
    /* Simple card styling */
    .main-card {
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        margin: 0 !important;
    }
    
    /* Simple header */
    .card-header {
        background: white !important;
        color: black !important;
        padding: 10px 0 !important;
        border-bottom: 1px solid #000 !important;
    }
    
    .page-title {
        background: transparent !important;
        color: black !important;
        padding: 0 !important;
        margin: 0 !important;
        font-size: 18px !important;
        font-weight: bold !important;
    }
    
    .page-subtitle {
        color: black !important;
        margin-top: 5px !important;
        font-size: 12px !important;
    }
    
    /* Simple card body */
    .card-body {
        padding: 10px 0 !important;
        background: white !important;
    }
    
    /* Basic table container */
    .table-container {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        background: white !important;
        border-radius: 0 !important;
        overflow: visible !important;
        box-shadow: none !important;
        border: none !important;
        max-height: none !important;
        overflow-y: visible !important;
        overflow-x: visible !important;
    }
    
    /* Basic table styling */
    .table {
        margin: 0 !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        width: 100% !important;
        display: table !important;
        visibility: visible !important;
        border: 1px solid #000 !important;
        background: white !important;
        table-layout: auto !important;
        page-break-inside: auto !important;
    }
    
    /* Simple table header */
    .table thead {
        display: table-header-group !important;
    }
    
    .table thead th {
        background: white !important;
        color: black !important;
        font-weight: bold !important;
        padding: 8px 4px !important;
        border: 1px solid #000 !important;
        font-size: 11px !important;
        text-transform: none !important;
        letter-spacing: normal !important;
        position: static !important;
        white-space: normal !important;
        vertical-align: middle !important;
        box-shadow: none !important;
        text-align: left !important;
        word-wrap: break-word !important;
        overflow: hidden !important;
    }
    
    /* Simple table body */
    .table tbody {
        display: table-row-group !important;
        visibility: visible !important;
    }
    
    .table tbody tr {
        display: table-row !important;
        visibility: visible !important;
        border-bottom: 1px solid #ccc !important;
        page-break-inside: avoid !important;
        page-break-after: auto !important;
        background: white !important;
    }
    
    .table tbody tr:nth-child(even) {
        background: white !important;
    }
    
    .table tbody tr:nth-child(odd) {
        background: white !important;
    }
    
    .table tbody td {
        display: table-cell !important;
        visibility: visible !important;
        padding: 6px 4px !important;
        vertical-align: middle !important;
        border: 1px solid #ccc !important;
        font-size: 10px !important;
        word-wrap: break-word !important;
        white-space: normal !important;
        text-align: left !important;
        background: white !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        color: black !important;
    }
    
    /* Basic column alignments */
    .table tbody td:nth-child(1), .table thead th:nth-child(1) {
        text-align: center !important;
        font-weight: bold !important;
        width: auto !important;
        min-width: 200px !important;
        max-width: none !important;
    }
    
    .table tbody td:nth-child(6), .table thead th:nth-child(6) {
        text-align: center !important;
    }
    
    .table tbody td:nth-child(7), .table thead th:nth-child(7) {
        text-align: right !important;
    }
    
    .table tbody td:nth-child(8), .table thead th:nth-child(8) {
        text-align: right !important;
    }
    
    .table tbody td:nth-child(9), .table thead th:nth-child(9) {
        text-align: center !important;
    }
    
    /* Remove all special styling */
    .stock-indicator {
        display: inline !important;
        padding: 0 !important;
        border-radius: 0 !important;
        font-weight: normal !important;
        font-size: inherit !important;
        border: none !important;
        background: transparent !important;
        color: black !important;
    }
    
    .stock-high, .stock-low, .stock-out {
        background: transparent !important;
        color: black !important;
    }
    
    .status-badge {
        display: inline !important;
        padding: 0 !important;
        border-radius: 0 !important;
        font-size: inherit !important;
        font-weight: normal !important;
        text-transform: none !important;
        border: none !important;
        background: transparent !important;
        color: black !important;
    }
    
    .status-active, .status-inactive, .status-pending, .status-secondary {
        background: transparent !important;
        color: black !important;
    }
    
    /* Remove all hover effects */
    .table tbody tr:hover,
    .btn-action:hover,
    .stock-indicator:hover,
    .status-badge:hover {
        transform: none !important;
        box-shadow: none !important;
        background: white !important;
    }
    
    /* Ensure DataTables doesn't interfere */
    .dataTables_wrapper .dataTables_scrollBody {
        display: block !important;
        overflow: visible !important;
    }
    
    /* Basic page break settings */
    .table thead {
        page-break-after: avoid !important;
    }
    
    .table tbody tr {
        page-break-inside: avoid !important;
    }
    
    /* Simple print header */
    .card-header::before {
        content: "Products Inventory Report" !important;
        display: block !important;
        font-size: 16px !important;
        font-weight: bold !important;
        margin-bottom: 5px !important;
        color: black !important;
    }
    
    /* Simple print date */
    .card-header::after {
        content: "Printed: " attr(data-print-date) !important;
        display: block !important;
        font-size: 10px !important;
        margin-top: 5px !important;
        color: black !important;
    }
}

/* Animation Classes */
.animate__delay-1s {
    animation-delay: 0.1s;
}

.animate__delay-2s {
    animation-delay: 0.2s;
}

/* Modal Detail Groups */
.detail-group {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 4px solid var(--primary-color);
}

.detail-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--secondary-color);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 5px;
    display: block;
}

.detail-value {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--dark-color);
    margin: 0;
    font-family: 'Inter', sans-serif;
}

.detail-value.stock-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
}

/* Enhanced Table Hover Effects */
.table tbody tr:hover {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
    transform: translateY(-2px) scale(1.01);
    box-shadow: var(--shadow-md);
}

/* Enhanced Button Hover Effects */
.btn-action:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
}

/* Enhanced Search Input */
.dataTables_filter input {
    border-radius: 20px;
    border: 2px solid var(--border-color);
    padding: 8px 16px;
    transition: var(--transition);
}

.dataTables_filter input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

/* Enhanced Pagination */
.page-link {
    border-radius: 8px;
    margin: 0 2px;
    border: 1px solid var(--border-color);
    color: var(--dark-color);
    transition: var(--transition);
}

.page-link:hover {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
    transform: translateY(-1px);
}

.page-item.active .page-link {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    :root {
        --light-color: #f8fafc;
        --dark-color: #1e293b;
        --border-color: #e2e8f0;
    }
    body {
        background: #fff;
        color: var(--dark-color);
    }
}

/* Enhanced Focus States */
.btn:focus,
.form-control:focus,
.page-link:focus {
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

/* Enhanced Responsive Design */
@media (max-width: 576px) {
    .stat-card {
        padding: 16px;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 8px;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
    
    .table thead th {
        font-size: 0.7rem;
        padding: 8px 4px;
    }
}

/* Responsive table scroll heights */
@media (max-width: 1200px) {
    .table-container {
        max-height: 500px;
    }
}

@media (max-width: 768px) {
    .table-container {
        max-height: 400px;
    }
    
    .table thead th {
        font-size: 0.75rem;
        padding: 12px 8px;
    }
    
    .table thead th:not(:first-child):not(:last-child) {
        min-width: 80px;
    }
}

@media (max-width: 576px) {
    .table-container {
        max-height: 350px;
    }
    
    .table thead th {
        font-size: 0.7rem;
        padding: 8px 4px;
    }
}

/* Scroll to top button */
.scroll-to-top-container {
    position: relative;
    margin-top: 20px;
    text-align: center;
}

.scroll-to-top-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--primary-color);
    border: none;
    color: white;
    font-size: 18px;
    box-shadow: var(--shadow-lg);
    transition: var(--transition);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
}

.scroll-to-top-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
}

.scroll-to-top-btn.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* Enhanced scroll indicators */
.table-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 20px;
    background: linear-gradient(to bottom, rgba(255,255,255,0.8), transparent);
    pointer-events: none;
    z-index: 5;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.table-container::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 20px;
    background: linear-gradient(to top, rgba(255,255,255,0.8), transparent);
    pointer-events: none;
    z-index: 5;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.table-container.scroll-top::before {
    opacity: 1;
}

.table-container.scroll-bottom::after {
    opacity: 1;
}

/* Table border and shadow enhancements */
.table-container {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

/* Enhanced table styling with better borders */
.table {
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    display: table !important;
    visibility: visible !important;
    border: none;
    border-radius: 0;
    overflow: hidden;
    background: white;
    table-layout: fixed; /* Ensure consistent column widths */
}

/* Column-specific styling for better visual separation */
.table tbody td.product-id {
    background: rgba(37, 99, 235, 0.02);
    font-weight: 600;
    color: #000000;
}

.table tbody td.stock-cell {
    background: rgba(16, 185, 129, 0.02);
}

.table tbody td.price-cell {
    background: rgba(245, 158, 11, 0.02);
    font-weight: 500;
}

.table tbody td.status-cell {
    background: rgba(99, 102, 241, 0.02);
}

/* Enhanced hover effects with vertical dividers */
.table tbody tr:hover {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
    transform: translateY(-2px) scale(1.01);
    box-shadow: var(--shadow-md);
}

.table tbody tr:hover td::after {
    opacity: 1;
    background: linear-gradient(to bottom, 
        transparent 0%, 
        var(--primary-color) 20%, 
        var(--primary-color) 80%, 
        transparent 100%);
    box-shadow: 0 0 4px rgba(37, 99, 235, 0.3);
}

/* Special styling for last column divider removal */
.table tbody td:last-child::after,
.table thead th:last-child::after {
    display: none;
}

/* Zebra striping for better row distinction */
.table tbody tr:nth-child(even) {
    background: rgba(248, 250, 252, 0.5);
}

.table tbody tr:nth-child(even):hover {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%) !important;
}

/* Ensure consistent column widths */
.table th:nth-child(1), .table td:nth-child(1) { width: auto; min-width: 250px; }
.table th:nth-child(2), .table td:nth-child(2) { width: 200px; min-width: 200px; max-width: 200px; }
.table th:nth-child(3), .table td:nth-child(3) { width: 100px; min-width: 100px; }
.table th:nth-child(4), .table td:nth-child(4) { width: 100px; min-width: 100px; }
.table th:nth-child(5), .table td:nth-child(5) { width: 100px; min-width: 100px; }
.table th:nth-child(6), .table td:nth-child(6) { width: 180px; min-width: 180px; max-width: 180px; }
.table th:nth-child(7), .table td:nth-child(7) { width: 100px; min-width: 100px; max-width: 100px; }
.table th:nth-child(8), .table td:nth-child(8) { width: 100px; min-width: 100px; max-width: 100px; }
.table th:nth-child(9), .table td:nth-child(9) { width: 80px; min-width: 80px; max-width: 80px; }

/* Handle long content in table cells */
.table tbody td.product-name {
    white-space: normal;
    word-wrap: break-word;
    max-width: 200px;
    font-size: 0.8rem;
    font-family: Arial, sans-serif;
    color: #000000;
    text-align: center;
}

.table tbody td.product-model {
    white-space: normal;
    word-wrap: break-word;
    max-width: 100px;
    font-size: 0.8rem;
    font-family: Arial, sans-serif;
    color: #000000;
    text-align: center;
}

.table tbody td.product-brand {
    white-space: normal;
    word-wrap: break-word;
    max-width: 100px;
    font-family: Arial, sans-serif;
    color: #000000;
    text-align: center;
}

.table tbody td.product-storage {
    white-space: normal;
    word-wrap: break-word;
    max-width: 100px;
    font-family: Arial, sans-serif;
    color: #000000;
    text-align: center;
}

/* Ensure stock indicator fits properly */
.table tbody td.stock-cell {
    white-space: nowrap;
    overflow: hidden;
}

.stock-indicator {
    display: flex;
    align-items: center;
    gap: 4px;
    justify-content: center;
    white-space: nowrap;
}

/* Ensure price cells are properly aligned */
.table tbody td.price-cell {
    white-space: nowrap;
    text-align: center;
    font-family: 'Courier New', monospace;
    color: var(--dark-color);
}

/* Ensure status badges fit properly */
.table tbody td.status-cell {
    white-space: nowrap;
    text-align: center;
}

.status-badge {
    display: inline-block;
    padding: 3px 6px;
    border-radius: 10px;
    font-size: 0.65rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
    font-family: Arial, sans-serif;
    color: #000000;
}

/* Enhanced Stock In Modal Styles */
#stockInModal .modal-content {
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    background: #ffffff;
    border: 1px solid #e5e7eb;
}

/* Modal table column width adjustments */
#stockInTable th:first-child,
#stockOutTable th:first-child,
#returnsTable th:first-child {
    min-width: 100px !important;
    max-width: 100px !important;
    width: 100px !important;
}

#stockInTable td:first-child,
#stockOutTable td:first-child,
#returnsTable td:first-child {
    min-width: 100px !important;
    max-width: 100px !important;
    width: 100px !important;
    font-size: 0.8rem !important;
}

#stockInModal .modal-header {
    background: linear-gradient(135deg, #1f2937, #374151);
    border-radius: 15px 15px 0 0;
    padding: 20px 30px;
    border-bottom: 2px solid #e5e7eb;
}

#stockInModal .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #ffffff;
}

#stockInModal .modal-body {
    padding: 30px;
    background: #ffffff;
}

#stockInModal .input-group {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

#stockInModal .input-group-text {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    color: #6b7280;
}

#stockInModal .form-control {
    border: 1px solid #e5e7eb;
    padding: 12px 16px;
    font-size: 0.9rem;
    background: #ffffff;
    color: #1f2937;
}

#stockInModal .form-control:focus {
    border-color: #1f2937;
    box-shadow: 0 0 0 3px rgba(31, 41, 55, 0.1);
    background: #ffffff;
}

#stockInModal .btn-outline-success {
    border-color: #1f2937;
    color: #1f2937;
    background: #ffffff;
}

#stockInModal .btn-outline-success:hover {
    background: #1f2937;
    color: #ffffff;
    border-color: #1f2937;
}

/* Summary Cards */
#stockInSummary .card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
    background: #ffffff;
}

#stockInSummary .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

#stockInSummary .card-body {
    padding: 20px 15px;
}

#stockInSummary .card-title {
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
    opacity: 0.9;
}

#stockInSummary h4 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
}

#stockInSummary h6 {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
}

/* Summary card colors - pure black theme */
#stockInSummary .card.bg-success {
    background: #000000 !important;
    color: #ffffff;
}

#stockInSummary .card.bg-info {
    background: #000000 !important;
    color: #ffffff;
}

#stockInSummary .card.bg-warning {
    background: #000000 !important;
    color: #ffffff;
}

#stockInSummary .card.bg-primary {
    background: #000000 !important;
    color: #ffffff;
}

/* Enhanced Table Styles */
#stockInTable {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    background: #ffffff;
    border: 1px solid #e5e7eb;
}

#stockInTable thead th {
    background: linear-gradient(135deg, #1f2937, #374151);
    color: #ffffff;
    font-weight: 600;
    padding: 15px 12px;
    border: none;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: sticky;
    top: 0;
    z-index: 10;
}

#stockInTable tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f3f4f6;
    background: #ffffff;
}

#stockInTable tbody tr:hover {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

#stockInTable tbody tr:nth-child(even) {
    background: #fafafa;
}

#stockInTable tbody tr:nth-child(even):hover {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
}

#stockInTable tbody td {
    padding: 12px;
    vertical-align: middle;
    border: none;
    font-size: 0.85rem;
    color: #1f2937;
}

/* Badge Styles - grey theme */
#stockInTable .badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 20px;
}

#stockInTable .badge.bg-success {
    background: linear-gradient(135deg, #6b7280, #4b5563) !important;
    color: #ffffff;
}

#stockInTable .badge.bg-info {
    background: linear-gradient(135deg, #9ca3af, #6b7280) !important;
    color: #ffffff;
}

#stockInTable .badge.bg-secondary {
    background: linear-gradient(135deg, #d1d5db, #9ca3af) !important;
    color: #1f2937;
}

#stockInTable .badge.bg-primary {
    background: linear-gradient(135deg, #1f2937, #374151) !important;
    color: #ffffff;
}

/* Loading and No Data States */
#stockInLoading, #stockInNoData {
    padding: 40px 20px;
    background: #ffffff;
}

#stockInLoading .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.25em;
    color: #6b7280;
}

#stockInNoData i {
    color: #9ca3af;
}

#stockInNoData h4 {
    color: #6b7280;
    font-weight: 600;
}

#stockInNoData p {
    color: #9ca3af;
}

/* Search Results */
#stockInNoResults {
    padding: 40px 20px;
    background: #f9fafb;
    border-radius: 12px;
    margin-top: 20px;
    border: 1px solid #e5e7eb;
}

#stockInNoResults i {
    color: #9ca3af;
}

/* Responsive Design */
@media (max-width: 768px) {
    #stockInModal .modal-dialog {
        margin: 10px;
    }
    
    #stockInModal .modal-body {
        padding: 20px 15px;
    }
    
    #stockInSummary .card-body {
        padding: 15px 10px;
    }
    
    #stockInSummary h4 {
        font-size: 1.4rem;
    }
    
    #stockInTable thead th {
        font-size: 0.7rem;
        padding: 10px 8px;
    }
    
    #stockInTable tbody td {
        font-size: 0.8rem;
        padding: 10px 8px;
    }
    
    #stockInTable .badge {
        font-size: 0.7rem;
        padding: 4px 8px;
    }
}

/* Animation for table rows */
.stock-in-row {
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
    transform: translateY(20px);
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Enhanced modal footer */
#stockInModal .modal-footer {
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    padding: 20px 30px;
    border-radius: 0 0 15px 15px;
}

#stockInModal .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 10px 20px;
    transition: all 0.3s ease;
}

#stockInModal .btn-secondary {
    background: #6b7280;
    border-color: #6b7280;
    color: #ffffff;
}

#stockInModal .btn-secondary:hover {
    background: #4b5563;
    border-color: #4b5563;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#stockInModal .btn-info {
    background: #9ca3af;
    border-color: #9ca3af;
    color: #1f2937;
}

#stockInModal .btn-info:hover {
    background: #6b7280;
    border-color: #6b7280;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#stockInModal .btn-success {
    background: #1f2937;
    border-color: #1f2937;
    color: #ffffff;
}

#stockInModal .btn-success:hover {
    background: #374151;
    border-color: #374151;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Text truncation for long content */
.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Enhanced date/time display */
#stockInTable .d-flex.flex-column small {
    font-size: 0.7rem;
    opacity: 0.7;
    color: #6b7280;
}

#stockInTable .d-flex.flex-column strong {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1f2937;
}

/* Product info styling */
#stockInTable .d-flex.flex-column strong:first-child {
    color: #1f2937;
}

#stockInTable .d-flex.flex-column small {
    color: #6b7280;
}

/* User info styling */
#stockInTable .d-flex.align-items-center i {
    color: #64748b;
    font-size: 0.9rem;
}

/* Movement ID styling */
#stockInTable .badge.bg-dark {
    font-family: 'Courier New', monospace;
    font-size: 0.7rem;
}

/* Enhanced Stock Out Modal Styles */
#stockOutModal .modal-content {
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    background: #ffffff;
    border: 1px solid #e5e7eb;
}

#stockOutModal .modal-header {
    background: linear-gradient(135deg, #1f2937, #374151);
    border-radius: 15px 15px 0 0;
    padding: 20px 30px;
    border-bottom: 2px solid #e5e7eb;
}

#stockOutModal .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #ffffff;
}

#stockOutModal .modal-body {
    padding: 30px;
    background: #ffffff;
}

#stockOutModal .input-group {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

#stockOutModal .input-group-text {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    color: #6b7280;
}

#stockOutModal .form-control {
    border: 1px solid #e5e7eb;
    padding: 12px 16px;
    font-size: 0.9rem;
    background: #ffffff;
    color: #1f2937;
}

#stockOutModal .form-control:focus {
    border-color: #1f2937;
    box-shadow: 0 0 0 3px rgba(31, 41, 55, 0.1);
    background: #ffffff;
}

#stockOutModal .btn-outline-danger {
    border-color: #1f2937;
    color: #1f2937;
    background: #ffffff;
}

#stockOutModal .btn-outline-danger:hover {
    background: #1f2937;
    color: #ffffff;
    border-color: #1f2937;
}

/* Summary Cards */
#stockOutSummary .card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
    background: #ffffff;
}

#stockOutSummary .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

#stockOutSummary .card-body {
    padding: 20px 15px;
}

#stockOutSummary .card-title {
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
    opacity: 0.9;
}

#stockOutSummary h4 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
}

#stockOutSummary h6 {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
}

/* Summary card colors - pure black theme */
#stockOutSummary .card.bg-danger {
    background: #000000 !important;
    color: #ffffff;
}

#stockOutSummary .card.bg-info {
    background: #000000 !important;
    color: #ffffff;
}

#stockOutSummary .card.bg-warning {
    background: #000000 !important;
    color: #ffffff;
}

#stockOutSummary .card.bg-primary {
    background: #000000 !important;
    color: #ffffff;
}

/* Enhanced Table Styles */
#stockOutTable {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    background: #ffffff;
    border: 1px solid #e5e7eb;
}

#stockOutTable thead th {
    background: linear-gradient(135deg, #1f2937, #374151);
    color: #ffffff;
    font-weight: 600;
    padding: 15px 12px;
    border: none;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: sticky;
    top: 0;
    z-index: 10;
}

#stockOutTable tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f3f4f6;
    background: #ffffff;
}

#stockOutTable tbody tr:hover {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

#stockOutTable tbody tr:nth-child(even) {
    background: #fafafa;
}

#stockOutTable tbody tr:nth-child(even):hover {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
}

#stockOutTable tbody td {
    padding: 12px;
    vertical-align: middle;
    border: none;
    font-size: 0.85rem;
    color: #1f2937;
}

/* Badge Styles - grey theme */
#stockOutTable .badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 20px;
}

#stockOutTable .badge.bg-danger {
    background: linear-gradient(135deg, #6b7280, #4b5563) !important;
    color: #ffffff;
}

#stockOutTable .badge.bg-info {
    background: linear-gradient(135deg, #9ca3af, #6b7280) !important;
    color: #ffffff;
}

#stockOutTable .badge.bg-secondary {
    background: linear-gradient(135deg, #d1d5db, #9ca3af) !important;
    color: #1f2937;
}

#stockOutTable .badge.bg-primary {
    background: linear-gradient(135deg, #1f2937, #374151) !important;
    color: #ffffff;
}

/* Loading and No Data States */
#stockOutLoading, #stockOutNoData {
    padding: 40px 20px;
    background: #ffffff;
}

#stockOutLoading .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.25em;
    color: #6b7280;
}

#stockOutNoData i {
    color: #9ca3af;
}

#stockOutNoData h4 {
    color: #6b7280;
    font-weight: 600;
}

#stockOutNoData p {
    color: #9ca3af;
}

/* Search Results */
#stockOutNoResults {
    padding: 40px 20px;
    background: #f9fafb;
    border-radius: 12px;
    margin-top: 20px;
    border: 1px solid #e5e7eb;
}

#stockOutNoResults i {
    color: #9ca3af;
}

/* Responsive Design */
@media (max-width: 768px) {
    #stockOutModal .modal-dialog {
        margin: 10px;
    }
    
    #stockOutModal .modal-body {
        padding: 20px 15px;
    }
    
    #stockOutSummary .card-body {
        padding: 15px 10px;
    }
    
    #stockOutSummary h4 {
        font-size: 1.4rem;
    }
    
    #stockOutTable thead th {
        font-size: 0.7rem;
        padding: 10px 8px;
    }
    
    #stockOutTable tbody td {
        font-size: 0.8rem;
        padding: 10px 8px;
    }
    
    #stockOutTable .badge {
        font-size: 0.7rem;
        padding: 4px 8px;
    }
}

/* Animation for table rows */
.stock-out-row {
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
    transform: translateY(20px);
}

/* Enhanced modal footer */
#stockOutModal .modal-footer {
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    padding: 20px 30px;
    border-radius: 0 0 15px 15px;
}

#stockOutModal .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 10px 20px;
    transition: all 0.3s ease;
}

#stockOutModal .btn-secondary {
    background: #6b7280;
    border-color: #6b7280;
    color: #ffffff;
}

#stockOutModal .btn-secondary:hover {
    background: #4b5563;
    border-color: #4b5563;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#stockOutModal .btn-info {
    background: #9ca3af;
    border-color: #9ca3af;
    color: #1f2937;
}

#stockOutModal .btn-info:hover {
    background: #6b7280;
    border-color: #6b7280;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#stockOutModal .btn-danger {
    background: #1f2937;
    border-color: #1f2937;
    color: #ffffff;
}

#stockOutModal .btn-danger:hover {
    background: #374151;
    border-color: #374151;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Text truncation for long content */
.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Enhanced date/time display */
#stockOutTable .d-flex.flex-column small {
    font-size: 0.7rem;
    opacity: 0.7;
    color: #6b7280;
}

#stockOutTable .d-flex.flex-column strong {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1f2937;
}

/* Product info styling */
#stockOutTable .d-flex.flex-column strong:first-child {
    color: #1f2937;
}

#stockOutTable .d-flex.flex-column small {
    color: #6b7280;
}

/* User info styling */
#stockOutTable .d-flex.align-items-center i {
    color: #64748b;
    font-size: 0.9rem;
}

/* Movement ID styling */
#stockOutTable .badge.bg-dark {
    font-family: 'Courier New', monospace;
    font-size: 0.7rem;
}

/* Enhanced Returns Modal Styles */
#returnsModal .modal-content {
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    background: #ffffff;
    border: 1px solid #e5e7eb;
}

#returnsModal .modal-header {
    background: linear-gradient(135deg, #1f2937, #374151);
    border-radius: 15px 15px 0 0;
    padding: 20px 30px;
    border-bottom: 2px solid #e5e7eb;
}

#returnsModal .modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #ffffff;
}

#returnsModal .modal-body {
    padding: 30px;
    background: #ffffff;
}

#returnsModal .input-group {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

#returnsModal .input-group-text {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    color: #6b7280;
}

#returnsModal .form-control {
    border: 1px solid #e5e7eb;
    padding: 12px 16px;
    font-size: 0.9rem;
    background: #ffffff;
    color: #1f2937;
}

#returnsModal .form-control:focus {
    border-color: #1f2937;
    box-shadow: 0 0 0 3px rgba(31, 41, 55, 0.1);
    background: #ffffff;
}

#returnsModal .btn-outline-warning {
    border-color: #1f2937;
    color: #1f2937;
    background: #ffffff;
}

#returnsModal .btn-outline-warning:hover {
    background: #1f2937;
    color: #ffffff;
    border-color: #1f2937;
}

/* Summary Cards */
#returnsSummary .card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
    background: #ffffff;
}

#returnsSummary .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

#returnsSummary .card-body {
    padding: 20px 15px;
}

#returnsSummary .card-title {
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
    opacity: 0.9;
}

#returnsSummary h4 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
}

#returnsSummary h6 {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
}

/* Summary card colors - pure black theme */
#returnsSummary .card.bg-warning {
    background: #000000 !important;
    color: #ffffff;
}

#returnsSummary .card.bg-info {
    background: #000000 !important;
    color: #ffffff;
}

#returnsSummary .card.bg-secondary {
    background: #000000 !important;
    color: #ffffff;
}

#returnsSummary .card.bg-primary {
    background: #000000 !important;
    color: #ffffff;
}

/* Enhanced Table Styles */
#returnsTable {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    background: #ffffff;
    border: 1px solid #e5e7eb;
}

#returnsTable thead th {
    background: linear-gradient(135deg, #1f2937, #374151);
    color: #ffffff;
    font-weight: 600;
    padding: 15px 12px;
    border: none;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: sticky;
    top: 0;
    z-index: 10;
}

#returnsTable tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f3f4f6;
    background: #ffffff;
}

#returnsTable tbody tr:hover {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

#returnsTable tbody tr:nth-child(even) {
    background: #fafafa;
}

#returnsTable tbody tr:nth-child(even):hover {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
}

#returnsTable tbody td {
    padding: 12px;
    vertical-align: middle;
    border: none;
    font-size: 0.85rem;
    color: #1f2937;
}

/* Badge Styles - grey theme */
#returnsTable .badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 20px;
}

#returnsTable .badge.bg-warning {
    background: linear-gradient(135deg, #6b7280, #4b5563) !important;
    color: #ffffff;
}

#returnsTable .badge.bg-info {
    background: linear-gradient(135deg, #9ca3af, #6b7280) !important;
    color: #ffffff;
}

#returnsTable .badge.bg-secondary {
    background: linear-gradient(135deg, #d1d5db, #9ca3af) !important;
    color: #1f2937;
}

#returnsTable .badge.bg-primary {
    background: linear-gradient(135deg, #1f2937, #374151) !important;
    color: #ffffff;
}

/* Loading and No Data States */
#returnsLoading, #returnsNoData {
    padding: 40px 20px;
    background: #ffffff;
}

#returnsLoading .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.25em;
    color: #6b7280;
}

#returnsNoData i {
    color: #9ca3af;
}

#returnsNoData h4 {
    color: #6b7280;
    font-weight: 600;
}

#returnsNoData p {
    color: #9ca3af;
}

/* Search Results */
#returnsNoResults {
    padding: 40px 20px;
    background: #f9fafb;
    border-radius: 12px;
    margin-top: 20px;
    border: 1px solid #e5e7eb;
}

#returnsNoResults i {
    color: #9ca3af;
}

/* Responsive Design */
@media (max-width: 768px) {
    #returnsModal .modal-dialog {
        margin: 10px;
    }
    
    #returnsModal .modal-body {
        padding: 20px 15px;
    }
    
    #returnsSummary .card-body {
        padding: 15px 10px;
    }
    
    #returnsSummary h4 {
        font-size: 1.4rem;
    }
    
    #returnsTable thead th {
        font-size: 0.7rem;
        padding: 10px 8px;
    }
    
    #returnsTable tbody td {
        font-size: 0.8rem;
        padding: 10px 8px;
    }
    
    #returnsTable .badge {
        font-size: 0.7rem;
        padding: 4px 8px;
    }
}

/* Animation for table rows */
.returns-row {
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
    transform: translateY(20px);
}

/* Enhanced modal footer */
#returnsModal .modal-footer {
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    padding: 20px 30px;
    border-radius: 0 0 15px 15px;
}

#returnsModal .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 10px 20px;
    transition: all 0.3s ease;
}

#returnsModal .btn-secondary {
    background: #6b7280;
    border-color: #6b7280;
    color: #ffffff;
}

#returnsModal .btn-secondary:hover {
    background: #4b5563;
    border-color: #4b5563;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#returnsModal .btn-warning {
    background: #1f2937;
    border-color: #1f2937;
    color: #ffffff;
}

#returnsModal .btn-warning:hover {
    background: #374151;
    border-color: #374151;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Text truncation for long content */
.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Enhanced date/time display */
#returnsTable .d-flex.flex-column small {
    font-size: 0.7rem;
    opacity: 0.7;
    color: #6b7280;
}

#returnsTable .d-flex.flex-column strong {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1f2937;
}

/* Product info styling */
#returnsTable .d-flex.flex-column strong:first-child {
    color: #1f2937;
}

#returnsTable .d-flex.flex-column small {
    color: #6b7280;
}

/* User info styling */
#returnsTable .d-flex.align-items-center i {
    color: #64748b;
    font-size: 0.9rem;
}

/* Movement ID styling */
#returnsTable .badge.bg-dark {
    font-family: 'Courier New', monospace;
    font-size: 0.7rem;
}

/* Low Stock Modal */
.lowStockModal {
    --bs-modal-width: 80%;
}

.lowStockModal .modal-content {
    border-radius: 1rem;
}

.lowStockModal .modal-header {
    background-color: #f8f9fa;
    border-bottom: none;
}

.lowStockModal .modal-title {
    font-size: 1.5rem;
    font-weight: 600;
}

.lowStockModal .modal-body {
    padding: 2rem;
}

.lowStockModal .form-control {
    margin-bottom: 1rem;
}

.lowStockModal .btn {
    width: 100%;
}

.lowStockModal .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.lowStockModal .btn-secondary:hover {
    background-color: #5c636a;
    border-color: #565e64;
}

.lowStockModal .btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.lowStockModal .btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
}

.lowStockModal .btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.lowStockModal .btn-success:hover {
    background-color: #218838;
}

/* Out of Stock Modal */
.outOfStockModal {
    --bs-modal-width: 80%;
}

.outOfStockModal .modal-content {
    border-radius: 1rem;
}

.outOfStockModal .modal-header {
    background-color: #f8f9fa;
    border-bottom: none;
}

.outOfStockModal .modal-title {
    font-size: 1.5rem;
    font-weight: 600;
}

.outOfStockModal .modal-body {
    padding: 2rem;
}

.outOfStockModal .form-control {
    margin-bottom: 1rem;
}

.outOfStockModal .btn {
    width: 100%;
}

.outOfStockModal .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.outOfStockModal .btn-secondary:hover {
    background-color: #5c636a;
    border-color: #565e64;
}

.outOfStockModal .btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.outOfStockModal .btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
}

.outOfStockModal .btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.outOfStockModal .btn-success:hover {
    background-color: #218838;
}

/* Out of Stock Table */
.outOfStockTable {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    background: #ffffff;
    border: 1px solid #e5e7eb;
}

.outOfStockTable thead th {
    background: linear-gradient(135deg, #1f2937, #374151);
    color: #ffffff;
    font-weight: 600;
    padding: 15px 12px;
    border: none;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.outOfStockTable tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f3f4f6;
    background: #ffffff;
}

.outOfStockTable tbody tr:hover {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.outOfStockTable tbody tr:nth-child(even) {
    background: #fafafa;
}

.outOfStockTable tbody tr:nth-child(even):hover {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
}

.outOfStockTable tbody td {
    padding: 12px;
    vertical-align: middle;
    border: none;
    font-size: 0.85rem;
    color: #1f2937;
}

/* Badge Styles - grey theme */
.outOfStockTable .badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 20px;
}

.outOfStockTable .badge.bg-danger {
    background: linear-gradient(135deg, #6b7280, #4b5563) !important;
    color: #ffffff;
}

.outOfStockTable .badge.bg-info {
    background: linear-gradient(135deg, #9ca3af, #6b7280) !important;
    color: #ffffff;
}

.outOfStockTable .badge.bg-secondary {
    background: linear-gradient(135deg, #d1d5db, #9ca3af) !important;
    color: #1f2937;
}

.outOfStockTable .badge.bg-primary {
    background: linear-gradient(135deg, #1f2937, #374151) !important;
    color: #ffffff;
}

/* Loading and No Data States */
.outOfStockLoading, .outOfStockNoData {
    padding: 40px 20px;
    background: #ffffff;
}

.outOfStockLoading .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.25em;
    color: #6b7280;
}

.outOfStockNoData i {
    color: #9ca3af;
}

.outOfStockNoData h4 {
    color: #6b7280;
    font-weight: 600;
}

.outOfStockNoData p {
    color: #9ca3af;
}

/* Search Results */
.outOfStockNoResults {
    padding: 40px 20px;
    background: #f9fafb;
    border-radius: 12px;
    margin-top: 20px;
    border: 1px solid #e5e7eb;
}

.outOfStockNoResults i {
    color: #9ca3af;
}

/* Responsive Design */
@media (max-width: 768px) {
    .outOfStockModal .modal-dialog {
        margin: 10px;
    }
    
    .outOfStockModal .modal-body {
        padding: 20px 15px;
    }
    
    .outOfStockSummary .card-body {
        padding: 15px 10px;
    }
    
    .outOfStockSummary h4 {
        font-size: 1.4rem;
    }
    
    .outOfStockTable thead th {
        font-size: 0.7rem;
        padding: 10px 8px;
    }
    
    .outOfStockTable tbody td {
        font-size: 0.8rem;
        padding: 10px 8px;
    }
    
    .outOfStockTable .badge {
        font-size: 0.7rem;
        padding: 4px 8px;
    }
}

/* Animation for table rows */
.outOfStock-row {
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
    transform: translateY(20px);
}

/* Enhanced modal footer */
#outOfStockModal .modal-footer {
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    padding: 20px 30px;
    border-radius: 0 0 15px 15px;
}

#outOfStockModal .btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 10px 20px;
    transition: all 0.3s ease;
}

#outOfStockModal .btn-secondary {
    background: #6b7280;
    border-color: #6b7280;
    color: #ffffff;
}

#outOfStockModal .btn-secondary:hover {
    background: #4b5563;
    border-color: #4b5563;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#outOfStockModal .btn-info {
    background: #9ca3af;
    border-color: #9ca3af;
    color: #1f2937;
}

#outOfStockModal .btn-info:hover {
    background: #6b7280;
    border-color: #6b7280;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

#outOfStockModal .btn-danger {
    background: #1f2937;
    border-color: #1f2937;
    color: #ffffff;
}

#outOfStockModal .btn-danger:hover {
    background: #374151;
    border-color: #374151;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Text truncation for long content */
.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Enhanced date/time display */
#outOfStockTable .d-flex.flex-column small {
    font-size: 0.7rem;
    opacity: 0.7;
    color: #6b7280;
}

#outOfStockTable .d-flex.flex-column strong {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1f2937;
}

/* Product info styling */
#outOfStockTable .d-flex.flex-column strong:first-child {
    color: #1f2937;
}

#outOfStockTable .d-flex.flex-column small {
    color: #6b7280;
}

/* User info styling */
#outOfStockTable .d-flex.align-items-center i {
    color: #64748b;
    font-size: 0.9rem;
}

/* Movement ID styling */
#outOfStockTable .badge.bg-dark {
    font-family: 'Courier New', monospace;
    font-size: 0.7rem;
}

/* Add this script after the loadLowStockItems function */
function loadOutOfStockItems() {
    $('#outOfStockLoading').show();
    $('#outOfStockNoData').hide();
    $('#outOfStockTable tbody').empty();
    $.ajax({
        url: 'get_out_of_stock_products.php', // Use the new endpoint
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#outOfStockLoading').hide();
            if (response && response.length > 0) {
                response.forEach(function(item) {
                    const row = `<tr>
                        <td>${item.product_id}</td>
                        <td>${item.product}</td>
                        <td>${item.brand}</td>
                        <td>${item.model}</td>
                        <td>${item.storage}</td>
                        <td><span class="badge bg-danger text-white">${item.stock_quantity}</span></td>
                    </tr>`;
                    $('#outOfStockTable tbody').append(row);
                });
            } else {
                $('#outOfStockNoData').show();
            }
        },
        error: function(xhr, status, error) {
            $('#outOfStockLoading').hide();
            $('#outOfStockNoData').show();
        }
    });
}

/* Add this to the document ready block to open the modal on stat-card click */
$('#outOfStockCard').on('click', function() {
    $('#outOfStockModal').modal('show');
    loadOutOfStockItems();
});


</style>

<script>
$(document).ready(function() {
    // Initialize DataTable with enhanced features
    var table = $('#productsTable').DataTable({
        responsive: false, // Disable responsive to maintain column alignment
        autoWidth: false,
        scrollY: '500px', // Enable vertical scrolling
        scrollCollapse: true, // Collapse scroll when not needed
        scrollX: true, // Enable horizontal scrolling if needed
        ordering: false, // Disable sorting functionality
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
            search: "<i class='fas fa-search'></i> Search:",
            searchPlaceholder: "Type to filter products...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ products",
            infoEmpty: "No products available",
            infoFiltered: "(filtered from _MAX_ total products)",
            zeroRecords: "No matching products found",
            paginate: {
                first: "<i class='fas fa-angle-double-left'></i>",
                last: "<i class='fas fa-angle-double-right'></i>",
                next: "<i class='fas fa-angle-right'></i>",
                previous: "<i class='fas fa-angle-left'></i>"
            }
        },
        columnDefs: [
            {
                targets: [0],
                type: 'string',
                className: 'text-center',
                width: 'auto',
                orderable: false
            },
            {
                targets: [1],
                className: 'text-center',
                width: '200px',
                orderable: false
            },
            {
                targets: [2],
                className: 'text-center',
                width: '120px',
                orderable: false
            },
            {
                targets: [3],
                className: 'text-center',
                width: '150px',
                orderable: false
            },
            {
                targets: [4],
                className: 'text-center',
                width: '100px',
                orderable: false
            },
            {
                targets: [5],
                type: 'num',
                className: 'text-center',
                width: '180px',
                orderable: false
            },
            {
                targets: [6, 7],
                type: 'currency',
                className: 'text-center',
                width: '140px',
                orderable: false
            },
            {
                targets: [8],
                className: 'text-center',
                width: '80px',
                orderable: false
            }
        ],
        initComplete: function() {
            // Remove the problematic search input addition to headers
            // This was causing issues with table header display
            console.log('DataTable initialized successfully');
            
            // Ensure table is visible
            $('#productsTable').show();
            $('.table-container').show();
        },
        drawCallback: function() {
            // Add row animations
            $('.product-row').each(function(index) {
                $(this).css('animation-delay', (index * 0.05) + 's');
                $(this).addClass('animate__animated animate__fadeInUp');
            });
            
            // Ensure table body is visible
            $('.table tbody').show();
            $('.dataTables_scrollBody').show();
        }
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Debug: Check if table has data
    setTimeout(function() {
        var rowCount = $('#productsTable tbody tr').length;
        console.log('Table row count:', rowCount);
        
        if (rowCount === 0) {
            console.log('No data found in table');
            // Add a test row to see if the table structure is working
            $('#productsTable tbody').append('<tr><td colspan="9" class="text-center">Loading data...</td></tr>');
        }
    }, 1000);

    $('#lowStockCard').on('click', function() {
        $('#lowStockModal').modal('show');
        loadLowStockItems();
    });

    $('#outOfStockCard').on('click', function() {
        $('#outOfStockModal').modal('show');
        loadOutOfStockItems();
    });

    optimizeTablePerformance();
    
    // Scroll to top functionality
    const scrollToTopBtn = document.getElementById('scrollToTop');
    const tableContainer = document.querySelector('.table-container');
    
    // Show/hide scroll to top button
    function toggleScrollToTop() {
        if (tableContainer.scrollTop > 200) {
            scrollToTopBtn.classList.add('show');
        } else {
            scrollToTopBtn.classList.remove('show');
        }
    }
    
    // Scroll to top when button is clicked
    scrollToTopBtn.addEventListener('click', function() {
        tableContainer.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Update scroll indicators
    function updateScrollIndicators() {
        const scrollTop = tableContainer.scrollTop;
        const scrollHeight = tableContainer.scrollHeight;
        const clientHeight = tableContainer.clientHeight;
        
        // Remove existing classes
        tableContainer.classList.remove('scroll-top', 'scroll-bottom');
        
        // Add appropriate classes
        if (scrollTop > 10) {
            tableContainer.classList.add('scroll-top');
        }
        
        if (scrollTop + clientHeight < scrollHeight - 10) {
            tableContainer.classList.add('scroll-bottom');
        }
    }
    
    // Add scroll event listeners
    tableContainer.addEventListener('scroll', function() {
        toggleScrollToTop();
        updateScrollIndicators();
    });
    
    // Initialize scroll indicators
    updateScrollIndicators();
    
    // Keyboard shortcuts for scrolling
    document.addEventListener('keydown', function(e) {
        if (e.target.closest('.table-container')) {
            if (e.key === 'Home') {
                e.preventDefault();
                tableContainer.scrollTo({ top: 0, behavior: 'smooth' });
            } else if (e.key === 'End') {
                e.preventDefault();
                tableContainer.scrollTo({ 
                    top: tableContainer.scrollHeight, 
                    behavior: 'smooth' 
                });
            }
        }
    });
});

// Enhanced Functions
function refreshTable() {
    // Add a small delay to show the loading state
    setTimeout(() => {
        location.reload();
    }, 300);
}

function printTable() {
    // Add print date to header
    var now = new Date();
    var printDate = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();
    $('.card-header').attr('data-print-date', 'Printed on: ' + printDate);
    
    // Wait for any animations to complete
    setTimeout(() => {
        window.print();
    }, 500);
}

function viewArchivedProducts() {
    // Redirect to archived products page
    window.location.href = 'archived_products.php';
}

// Stock In Report Functions
function viewStockInReport() {
    $('#stockInModal').modal('show');
    loadStockInData();
}

function loadStockInData() {
    // Show loading indicator
    $('#stockInLoading').show();
    $('#stockInNoData').hide();
    $('#stockInTable tbody').empty();
    $('#stockInSummary').hide();
    
    // Set default date range (last 30 days) - but allow showing all if no dates
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 30);
    
    document.getElementById('stockInStartDate').value = startDate.toISOString().split('T')[0];
    document.getElementById('stockInEndDate').value = endDate.toISOString().split('T')[0];
    
    // Load data via AJAX
    $.ajax({
        url: 'get_stock_in_report.php',
        type: 'POST',
        data: {
            start_date: startDate.toISOString().split('T')[0],
            end_date: endDate.toISOString().split('T')[0],
            show_all: false
        },
        success: function(response) {
            $('#stockInLoading').hide();
            
            try {
                if (response.error) {
                    if (response.setup_required) {
                        $('#stockInNoData').html(`
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h5 class="text-warning">Setup Required</h5>
                            <p class="text-muted">${response.error}</p>
                            <button class="btn btn-primary" onclick="setupStockMovements()">
                                <i class="fas fa-database"></i> Setup Stock Movements
                            </button>
                        `).show();
                    } else {
                        $('#stockInNoData').html(`
                            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <h5 class="text-danger">Error Loading Data</h5>
                            <p class="text-muted">${response.error}</p>
                        `).show();
                    }
                    return;
                }
                
                const data = response.data || [];
                const summary = response.summary || {};
                
                populateStockInTable(data);
                updateStockInSummary(summary);
                
                if (data.length === 0) {
                    $('#stockInNoData').show();
                } else {
                    $('#stockInSummary').show();
                }
                
            } catch (e) {
                console.error('Error parsing stock in data:', e);
                $('#stockInNoData').html(`
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Data Parse Error</h5>
                    <p class="text-muted">Error parsing the response data. Please try again.</p>
                `).show();
            }
        },
        error: function(xhr, status, error) {
            $('#stockInLoading').hide();
            console.error('AJAX Error:', error);
            $('#stockInNoData').html(`
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5 class="text-danger">Network Error</h5>
                <p class="text-muted">Failed to load stock in data. Please check your connection and try again.</p>
            `).show();
        }
    });
}

function populateStockInTable(data) {
    const tbody = $('#stockInTable tbody');
    tbody.empty();
    
    if (data.length === 0) {
        return;
    }
    
    data.forEach(function(item, index) {
        const row = `
            <tr class="stock-in-row" data-product-id="${item.product_id}">
                <td style="min-width: 140px;">
                    <div class="d-flex flex-column">
                        <small class="text-muted">${item.date}</small>
                        <strong>${item.date_time ? item.date_time.split(' ')[1] : ''}</strong>
                    </div>
                </td>
                <td style="min-width: 200px;">
                    <span class="badge bg-primary">${item.product_id}</span>
                </td>
                <td style="min-width: 120px;">
                    <div class="d-flex flex-column">
                        <strong>${item.product_name}</strong>
                        <small class="text-muted">ID: ${item.product_id}</small>
                    </div>
                </td>
                <td style="min-width: 100px;">${item.brand || 'N/A'}</td>
                <td style="min-width: 150px;">${item.model || 'N/A'}</td>
                <td style="min-width: 100px;">
                    <span class="badge bg-secondary">${item.storage || 'N/A'}</span>
                </td>
                <td style="min-width: 120px;" class="text-center">
                    <span class="badge bg-success fs-6">+${item.quantity_added}</span>
                </td>
                <td style="min-width: 120px;" class="text-center">
                    <span class="badge bg-secondary">${item.previous_stock}</span>
                </td>
                <td style="min-width: 120px;" class="text-center">
                    <span class="badge bg-info fs-6">${item.new_stock}</span>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    // Add animation to rows
    $('.stock-in-row').each(function(index) {
        $(this).css('animation-delay', (index * 0.05) + 's');
        $(this).addClass('animate__animated animate__fadeInUp');
    });
}

function updateStockInSummary(summary) {
    $('#dateRange').text(summary.date_range || '-');
}

function filterStockInReport() {
    const startDate = document.getElementById('stockInStartDate').value;
    const endDate = document.getElementById('stockInEndDate').value;
    
    // If no dates are selected, show all records
    if (!startDate && !endDate) {
        loadStockInData();
        return;
    }
    
    // Show loading indicator
    $('#stockInLoading').show();
    $('#stockInNoData').hide();
    $('#stockInSummary').hide();
    
    $.ajax({
        url: 'get_stock_in_report.php',
        type: 'POST',
        data: { start_date: startDate, end_date: endDate },
        success: function(response) {
            $('#stockInLoading').hide();
            
            try {
                if (response.error) {
                    $('#stockInNoData').html(`
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Filter Error</h5>
                        <p class="text-muted">${response.error}</p>
                    `).show();
                    return;
                }
                
                const data = response.data || [];
                const summary = response.summary || {};
                
                populateStockInTable(data);
                updateStockInSummary(summary);
                
                if (data.length === 0) {
                    $('#stockInNoData').show();
                } else {
                    $('#stockInSummary').show();
                }
                
            } catch (e) {
                console.error('Error parsing filtered stock in data:', e);
                $('#stockInNoData').html(`
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Data Parse Error</h5>
                    <p class="text-muted">Error parsing the filtered data.</p>
                `).show();
            }
        },
        error: function() {
            $('#stockInLoading').hide();
            $('#stockInNoData').html(`
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5 class="text-danger">Filter Error</h5>
                <p class="text-muted">Failed to filter data. Please try again.</p>
            `).show();
        }
    });
}

function searchStockInTable() {
    const searchTerm = $('#stockInSearch').val().toLowerCase();
    const rows = $('#stockInTable tbody tr');
    
    rows.each(function() {
        const text = $(this).text().toLowerCase();
        if (text.includes(searchTerm)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    
    // Show/hide no results message
    const visibleRows = rows.filter(':visible');
    if (visibleRows.length === 0 && searchTerm !== '') {
        if ($('#stockInNoData').is(':visible')) {
            $('#stockInNoData').hide();
        }
        if (!$('#stockInNoResults').length) {
            $('#stockInTable').after(`
                <div id="stockInNoResults" class="text-center py-4">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Results Found</h5>
                    <p class="text-muted">No stock in records match your search: "${searchTerm}"</p>
                </div>
            `);
        }
    } else {
        $('#stockInNoResults').remove();
    }
}

function refreshStockInData() {
    loadStockInData();
}

function clearStockInDates() {
    document.getElementById('stockInStartDate').value = '';
    document.getElementById('stockInEndDate').value = '';
    loadStockInData();
}

function setupStockMovements() {
    // This function would typically redirect to a setup page or run the SQL script
    if (confirm('This will set up the stock movements tracking system. Continue?')) {
        window.location.href = 'create_stock_movements_table.sql';
    }
}

function exportStockInReport() {
    const startDate = document.getElementById('stockInStartDate').value;
    const endDate = document.getElementById('stockInEndDate').value;
    
    window.open(`export_stock_in_report.php?start_date=${startDate}&end_date=${endDate}`, '_blank');
}

// Stock Out Report Functions
function viewStockOutReport() {
    $('#stockOutModal').modal('show');
    loadStockOutData();
}

function loadStockOutData() {
    // Show loading indicator
    $('#stockOutLoading').show();
    $('#stockOutNoData').hide();
    $('#stockOutTable tbody').empty();
    $('#stockOutSummary').hide();
    
    // Set default date range (last 30 days) - but allow showing all if no dates
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 30);
    
    document.getElementById('stockOutStartDate').value = startDate.toISOString().split('T')[0];
    document.getElementById('stockOutEndDate').value = endDate.toISOString().split('T')[0];
    
    // Load data via AJAX
    $.ajax({
        url: 'get_stock_out_report.php',
        type: 'POST',
        data: {
            start_date: startDate.toISOString().split('T')[0],
            end_date: endDate.toISOString().split('T')[0],
            show_all: false
        },
        success: function(response) {
            $('#stockOutLoading').hide();
            
            try {
                if (response.error) {
                    if (response.setup_required) {
                        $('#stockOutNoData').html(`
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h5 class="text-warning">Setup Required</h5>
                            <p class="text-muted">${response.error}</p>
                            <button class="btn btn-primary" onclick="setupStockMovements()">
                                <i class="fas fa-database"></i> Setup Stock Movements
                            </button>
                        `).show();
                    } else {
                        $('#stockOutNoData').html(`
                            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <h5 class="text-danger">Error Loading Data</h5>
                            <p class="text-muted">${response.error}</p>
                        `).show();
                    }
                    return;
                }
                
                const data = response.data || [];
                const summary = response.summary || {};
                
                populateStockOutTable(data);
                updateStockOutSummary(summary);
                
                if (data.length === 0) {
                    $('#stockOutNoData').show();
                } else {
                    $('#stockOutSummary').show();
                }
                
            } catch (e) {
                console.error('Error parsing stock out data:', e);
                $('#stockOutNoData').html(`
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Data Parse Error</h5>
                    <p class="text-muted">Error parsing the response data. Please try again.</p>
                `).show();
            }
        },
        error: function(xhr, status, error) {
            $('#stockOutLoading').hide();
            console.error('AJAX Error:', error);
            $('#stockOutNoData').html(`
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5 class="text-danger">Network Error</h5>
                <p class="text-muted">Failed to load stock out data. Please check your connection and try again.</p>
            `).show();
        }
    });
}

function populateStockOutTable(data) {
    const tbody = $('#stockOutTable tbody');
    tbody.empty();
    
    if (data.length === 0) {
        return;
    }
    
    data.forEach(function(item, index) {
        const row = `
            <tr class="stock-out-row" data-product-id="${item.product_id}">
                <td style="min-width: 140px;">
                    <div class="d-flex flex-column">
                        <small class="text-muted">${item.date}</small>
                        <strong>${item.date_time ? item.date_time.split(' ')[1] : ''}</strong>
                    </div>
                </td>
                <td style="min-width: 200px;">
                    <span class="badge bg-primary">${item.product_id}</span>
                </td>
                <td style="min-width: 120px;">
                    <div class="d-flex flex-column">
                        <strong>${item.product_name}</strong>
                        <small class="text-muted">ID: ${item.product_id}</small>
                    </div>
                </td>
                <td style="min-width: 100px;">${item.brand || 'N/A'}</td>
                <td style="min-width: 150px;">${item.model || 'N/A'}</td>
                <td style="min-width: 100px;">
                    <span class="badge bg-secondary">${item.storage || 'N/A'}</span>
                </td>
                <td style="min-width: 120px;" class="text-center">
                    <span class="badge bg-danger fs-6">-${item.quantity_removed}</span>
                </td>
                <td style="min-width: 120px;" class="text-center">
                    <span class="badge bg-secondary">${item.previous_stock}</span>
                </td>
                <td style="min-width: 120px;" class="text-center">
                    <span class="badge bg-info fs-6">${item.new_stock}</span>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    // Add animation to rows
    $('.stock-out-row').each(function(index) {
        $(this).css('animation-delay', (index * 0.05) + 's');
        $(this).addClass('animate__animated animate__fadeInUp');
    });
}

function updateStockOutSummary(summary) {
    $('#totalOutMovements').text(summary.total_movements || 0);
    $('#totalQuantityRemoved').text(summary.total_quantity_removed || 0);
    $('#uniqueOutProducts').text(summary.unique_products || 0);
    $('#outDateRange').text(summary.date_range || '-');
}

function filterStockOutReport() {
    const startDate = document.getElementById('stockOutStartDate').value;
    const endDate = document.getElementById('stockOutEndDate').value;
    
    // If no dates are selected, show all records
    if (!startDate && !endDate) {
        loadStockOutData();
        return;
    }
    
    // Show loading indicator
    $('#stockOutLoading').show();
    $('#stockOutNoData').hide();
    $('#stockOutSummary').hide();
    
    $.ajax({
        url: 'get_stock_out_report.php',
        type: 'POST',
        data: { start_date: startDate, end_date: endDate },
        success: function(response) {
            $('#stockOutLoading').hide();
            
            try {
                if (response.error) {
                    $('#stockOutNoData').html(`
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Filter Error</h5>
                        <p class="text-muted">${response.error}</p>
                    `).show();
                    return;
                }
                
                const data = response.data || [];
                const summary = response.summary || {};
                
                populateStockOutTable(data);
                updateStockOutSummary(summary);
                
                if (data.length === 0) {
                    $('#stockOutNoData').show();
                } else {
                    $('#stockOutSummary').show();
                }
                
            } catch (e) {
                console.error('Error parsing filtered stock out data:', e);
                $('#stockOutNoData').html(`
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Data Parse Error</h5>
                    <p class="text-muted">Error parsing the filtered data.</p>
                `).show();
            }
        },
        error: function() {
            $('#stockOutLoading').hide();
            $('#stockOutNoData').html(`
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5 class="text-danger">Filter Error</h5>
                <p class="text-muted">Failed to filter data. Please try again.</p>
            `).show();
        }
    });
}

function searchStockOutTable() {
    const searchTerm = $('#stockOutSearch').val().toLowerCase();
    const rows = $('#stockOutTable tbody tr');
    
    rows.each(function() {
        const text = $(this).text().toLowerCase();
        if (text.includes(searchTerm)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    
    // Show/hide no results message
    const visibleRows = rows.filter(':visible');
    if (visibleRows.length === 0 && searchTerm !== '') {
        if ($('#stockOutNoData').is(':visible')) {
            $('#stockOutNoData').hide();
        }
        if (!$('#stockOutNoResults').length) {
            $('#stockOutTable').after(`
                <div id="stockOutNoResults" class="text-center py-4">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Results Found</h5>
                    <p class="text-muted">No stock out records match your search: "${searchTerm}"</p>
                </div>
            `);
        }
    } else {
        $('#stockOutNoResults').remove();
    }
}

function refreshStockOutData() {
    loadStockOutData();
}

function clearStockOutDates() {
    document.getElementById('stockOutStartDate').value = '';
    document.getElementById('stockOutEndDate').value = '';
    loadStockOutData();
}

function exportStockOutReport() {
    const startDate = document.getElementById('stockOutStartDate').value;
    const endDate = document.getElementById('stockOutEndDate').value;
    
    window.open(`export_stock_out_report.php?start_date=${startDate}&end_date=${endDate}`, '_blank');
}

// Restock function
function openRestockPage() {
    window.location.href = 'restock_product.php';
}

// Returns function
function openReturnsPage() {
    window.location.href = 'returns.php';
}

// Returns Modal Functions
function viewReturnsReport() {
    $('#returnsModal').modal('show');
    loadReturnsData();
}

function loadReturnsData() {
    $('#returnsLoading').show();
    $('#returnsNoData').hide();
    $('#returnsTable tbody').empty();
    $('#returnsSummary').hide();

    // Set default date range (last 30 days) - but allow showing all if no dates
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 30);

    document.getElementById('returnsStartDate').value = startDate.toISOString().split('T')[0];
    document.getElementById('returnsEndDate').value = endDate.toISOString().split('T')[0];

    // Load data via AJAX
    $.ajax({
        url: 'get_returns_report.php',
        type: 'POST',
        data: {
            start_date: startDate.toISOString().split('T')[0],
            end_date: endDate.toISOString().split('T')[0],
            show_all: false
        },
        success: function(response) {
            $('#returnsLoading').hide();
            try {
                if (response.error) {
                    $('#returnsNoData').html(`
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Error Loading Data</h5>
                        <p class="text-muted">${response.error}</p>
                    `).show();
                    return;
                }
                const data = response.data || [];
                const summary = response.summary || {};
                populateReturnsTable(data);
                updateReturnsSummary(summary);
                if (data.length === 0) {
                    $('#returnsNoData').show();
                } else {
                    $('#returnsSummary').show();
                }
            } catch (e) {
                console.error('Error parsing returns data:', e);
                $('#returnsNoData').html(`
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Data Parse Error</h5>
                    <p class="text-muted">Error parsing the response data. Please try again.</p>
                `).show();
            }
        },
        error: function(xhr, status, error) {
            $('#returnsLoading').hide();
            console.error('AJAX Error:', error);
            $('#returnsNoData').html(`
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5 class="text-danger">Network Error</h5>
                <p class="text-muted">Failed to load returns data. Please check your connection and try again.</p>
            `).show();
        }
    });
}

function populateReturnsTable(data) {
    const tbody = $('#returnsTable tbody');
    tbody.empty();
    if (data.length === 0) return;
    data.forEach(function(item, index) {
        const row = `
            <tr class="returns-row" data-product-id="${item.product_id}">
                <td style="min-width: 140px;">
                    <div class="d-flex flex-column">
                        <small class="text-muted">${item.date}</small>
                        <strong>${item.date_time ? item.date_time.split(' ')[1] : ''}</strong>
                    </div>
                </td>
                <td style="min-width: 200px;">
                    <span class="badge bg-primary">${item.product_id}</span>
                </td>
                <td style="min-width: 120px;">
                    <div class="d-flex flex-column">
                        <strong>${item.product_name}</strong>
                        <small class="text-muted">ID: ${item.product_id}</small>
                    </div>
                </td>
                <td style="min-width: 100px;">${item.brand || 'N/A'}</td>
                <td style="min-width: 150px;">${item.model || 'N/A'}</td>
                <td style="min-width: 100px;">
                    <span class="badge bg-secondary">${item.storage || 'N/A'}</span>
                </td>
                <td style="min-width: 120px;" class="text-center">
                    <span class="badge bg-warning fs-6">-${item.quantity_returned}</span>
                </td>
                <td style="min-width: 120px;" class="text-center">
                    <span class="badge bg-secondary">${item.previous_stock}</span>
                </td>
                <td style="min-width: 120px;" class="text-center">
                    <span class="badge bg-info fs-6">${item.new_stock}</span>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    // Add animation to rows
    $('.returns-row').each(function(index) {
        $(this).css('animation-delay', (index * 0.05) + 's');
        $(this).addClass('animate__animated animate__fadeInUp');
    });
}

function updateReturnsSummary(summary) {
    $('#totalReturns').text(summary.total_returns || 0);
    $('#totalQuantityReturned').text(summary.total_quantity_returned || 0);
    $('#uniqueReturnedProducts').text(summary.unique_products || 0);
    $('#returnsDateRange').text(summary.date_range || '-');
}

function filterReturnsReport() {
    const startDate = document.getElementById('returnsStartDate').value;
    const endDate = document.getElementById('returnsEndDate').value;
    
    // If no dates are selected, show all records
    if (!startDate && !endDate) {
        loadReturnsData();
        return;
    }
    $('#returnsLoading').show();
    $('#returnsNoData').hide();
    $('#returnsSummary').hide();
    $.ajax({
        url: 'get_returns_report.php',
        type: 'POST',
        data: { start_date: startDate, end_date: endDate },
        success: function(response) {
            $('#returnsLoading').hide();
            try {
                if (response.error) {
                    $('#returnsNoData').html(`
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Filter Error</h5>
                        <p class="text-muted">${response.error}</p>
                    `).show();
                    return;
                }
                const data = response.data || [];
                const summary = response.summary || {};
                populateReturnsTable(data);
                updateReturnsSummary(summary);
                if (data.length === 0) {
                    $('#returnsNoData').show();
                } else {
                    $('#returnsSummary').show();
                }
            } catch (e) {
                console.error('Error parsing filtered returns data:', e);
                $('#returnsNoData').html(`
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Data Parse Error</h5>
                    <p class="text-muted">Error parsing the filtered data.</p>
                `).show();
            }
        },
        error: function() {
            $('#returnsLoading').hide();
            $('#returnsNoData').html(`
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5 class="text-danger">Filter Error</h5>
                <p class="text-muted">Failed to filter data. Please try again.</p>
            `).show();
        }
    });
}

function searchReturnsTable() {
    const searchTerm = $('#returnsSearch').val().toLowerCase();
    const rows = $('#returnsTable tbody tr');
    rows.each(function() {
        const text = $(this).text().toLowerCase();
        if (text.includes(searchTerm)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    // Show/hide no results message
    const visibleRows = rows.filter(':visible');
    if (visibleRows.length === 0 && searchTerm !== '') {
        if ($('#returnsNoData').is(':visible')) {
            $('#returnsNoData').hide();
        }
        if (!$('#returnsNoResults').length) {
            $('#returnsTable').after(`
                <div id="returnsNoResults" class="text-center py-4">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Results Found</h5>
                    <p class="text-muted">No returns records match your search: "${searchTerm}"</p>
                </div>
            `);
        }
    } else {
        $('#returnsNoResults').remove();
    }
}

function refreshReturnsData() {
    loadReturnsData();
}

function clearReturnsDates() {
    document.getElementById('returnsStartDate').value = '';
    document.getElementById('returnsEndDate').value = '';
    loadReturnsData();
}

// Error handling for images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.onerror = function() {
            this.style.display = 'none';
        };
    });
});

// Add smooth scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Enhanced Keyboard Shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + F to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        $('.dataTables_filter input').focus();
    }
    
    // Ctrl/Cmd + P to print
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        printTable();
    }
    
    // Ctrl/Cmd + R to refresh
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        refreshTable();
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        $('.modal').modal('hide');
    }
});

// Enhanced Error Handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
});

// Enhanced Network Status Detection
window.addEventListener('online', function() {
    console.log('Connection restored!');
});

window.addEventListener('offline', function() {
    console.log('No internet connection. Some features may be limited.');
});

// Enhanced Table Performance
function optimizeTablePerformance() {
    // Debounce search input
    let searchTimeout;
    $('.dataTables_filter input').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            table.search(this.value).draw();
        }, 300);
    });
    
    // Lazy load images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Time update function for header
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    document.getElementById('currentTime').textContent = timeString;
}

// Update time every second
setInterval(updateTime, 1000);
updateTime(); // Initial call

function loadLowStockItems() {
    $('#lowStockLoading').show();
    $('#lowStockNoData').hide();
    $('#lowStockTable tbody').empty();
    $.ajax({
        url: 'get_low_stock_products.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#lowStockLoading').hide();
            if (response && response.length > 0) {
                response.forEach(function(item) {
                    const row = `<tr>
                        <td>${item.product_id}</td>
                        <td>${item.product}</td>
                        <td>${item.brand}</td>
                        <td>${item.model}</td>
                        <td>${item.storage}</td>
                        <td><span class="badge bg-warning text-dark">${item.stock_quantity}</span></td>
                    </tr>`;
                    $('#lowStockTable tbody').append(row);
                });
            } else {
                $('#lowStockNoData').show();
            }
        },
        error: function() {
            $('#lowStockLoading').hide();
            $('#lowStockNoData').show();
        }
    });
}
</script>

</body>
</html>