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
    <title>Return Product - iCenter</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container-fluid">
    <!-- Header Section -->
    <div class="header-section">
        <div class="logo-container">
            <img src="images/iCenter.png" alt="iCenter Logo" class="logo-img">
        </div>
        <div class="back-button-container">
            <a href="inventory_stocks.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Inventory</span>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h2 class="card-title">
                        <i class="fas fa-undo"></i>
                        Return Product
                    </h2>
                    <p class="card-subtitle">Process returned items and update inventory</p>
                </div>
                <div class="card-body">
                    <form id="returnForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_select" class="form-label">Select Product*</label>
                                    <select class="form-select" id="product_select" name="product_id" required>
                                        <option value="">Choose a product...</option>
                                        <?php
                                        try {
                                            $query = "SELECT product_id, product, brand, model, stock_quantity FROM products WHERE (archived IS NULL OR archived = 0) ORDER BY product";
                                            $stmt = $pdo->prepare($query);
                                            $stmt->execute();
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                $display_text = $row['product'] . ' - ' . $row['brand'] . ' ' . $row['model'] . ' (Current Stock: ' . $row['stock_quantity'] . ')';
                                                echo "<option value='{$row['product_id']}' data-current-stock='{$row['stock_quantity']}'>{$display_text}</option>";
                                            }
                                        } catch (PDOException $e) {
                                            echo "<option value=''>Error loading products</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity_to_return" class="form-label">Quantity to Return*</label>
                                    <input type="number" class="form-control" id="quantity_to_return" name="quantity" min="1" required placeholder="Enter quantity">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="current_stock" class="form-label">Current Stock</label>
                                    <input type="text" class="form-control" id="current_stock" readonly value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="new_stock" class="form-label">New Stock (After Return)</label>
                                    <input type="text" class="form-control" id="new_stock" readonly value="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="return_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="return_notes" name="notes" rows="3" placeholder="Enter notes about this return (optional)"></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-undo"></i>
                                Return Product
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='inventory_stocks.php'">
                                Cancel
                            </button>
                        </div>
                    </form>
                    <div id="message" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.logo-img { height: 80px; width: auto; }
.header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding: 20px 0; }
.card { background: rgba(255,255,255,0.95); border-radius: 12px; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); border: 1px solid rgba(255,255,255,0.2); overflow: hidden; }
.card-header { background: linear-gradient(135deg, #64748b, #1e293b); color: white; padding: 30px; border: none; }
.card-title { font-size: 2rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 12px; }
.card-subtitle { margin: 8px 0 0 0; opacity: 0.9; font-size: 1rem; }
.card-body { padding: 30px; }
.form-label { font-weight: 600; color: #1e293b; margin-bottom: 8px; }
.form-control, .form-select { border: 2px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; font-size: 1rem; transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }
.form-control:focus, .form-select:focus { border-color: #64748b; box-shadow: 0 0 0 3px rgba(100,116,139,0.1); outline: none; }
.form-control[readonly] { background-color: #f8fafc; color: #64748b; }
.btn { padding: 12px 24px; border-radius: 8px; font-weight: 600; transition: all 0.3s cubic-bezier(0.4,0,0.2,1); display: inline-flex; align-items: center; gap: 8px; }
.btn-secondary { background: #64748b; border-color: #64748b; color: #fff; }
.btn-secondary:hover { background: #1e293b; border-color: #1e293b; color: #fff; }
.btn-outline-secondary { border-color: #64748b; color: #64748b; background: #fff; }
.btn-outline-secondary:hover { background: #64748b; color: #fff; }
.alert { border-radius: 8px; border: none; padding: 16px 20px; }
.alert-success { background: #dcfce7; color: #166534; }
.alert-danger { background: #fee2e2; color: #991b1b; }
</style>
<script>
$(document).ready(function() {
    // Update stock calculations when product is selected
    $('#product_select').change(function() {
        const selectedOption = $(this).find('option:selected');
        const currentStock = parseInt(selectedOption.data('current-stock')) || 0;
        $('#current_stock').val(currentStock);
        updateNewStock();
    });
    // Update new stock calculation when quantity changes
    $('#quantity_to_return').on('input', function() {
        updateNewStock();
    });
    function updateNewStock() {
        const currentStock = parseInt($('#current_stock').val()) || 0;
        const quantityToReturn = parseInt($('#quantity_to_return').val()) || 0;
        const newStock = currentStock - quantityToReturn;
        $('#new_stock').val(newStock);
    }
    // Handle form submission
    $('#returnForm').submit(function(e) {
        e.preventDefault();
        const formData = {
            product_id: $('#product_select').val(),
            quantity: parseInt($('#quantity_to_return').val()),
            notes: $('#return_notes').val(),
            current_stock: parseInt($('#current_stock').val()),
            new_stock: parseInt($('#new_stock').val())
        };
        if (!formData.product_id) {
            showMessage('Please select a product', 'danger');
            return;
        }
        if (!formData.quantity || formData.quantity <= 0) {
            showMessage('Please enter a valid quantity', 'danger');
            return;
        }
        if (formData.quantity > formData.current_stock) {
            showMessage('Return quantity cannot exceed current stock', 'danger');
            return;
        }
        // Send AJAX request
        $.ajax({
            url: 'process_return.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    $('#returnForm')[0].reset();
                    $('#current_stock').val('0');
                    $('#new_stock').val('0');
                    // Update the product option to reflect new stock
                    const option = $('#product_select option:selected');
                    const newStock = formData.new_stock;
                    const displayText = option.text().replace(/\(Current Stock: \d+\)/, `(Current Stock: ${newStock})`);
                    option.text(displayText);
                    option.data('current-stock', newStock);
                } else {
                    showMessage(response.error, 'danger');
                }
            },
            error: function() {
                showMessage('Network error occurred', 'danger');
            }
        });
    });
    function showMessage(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        $('#message').html(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${icon}"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        }
    }
});
</script>
</body>
</html> 