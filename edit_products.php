<?php
require_once 'db.php';

function redirectToView() {
    header('Location: view_products.php');
    exit;
}

// Function to handle image upload and validation
function handleImageUpload($imageField, $product_code, &$error) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB max file size

    if (isset($_FILES[$imageField]) && $_FILES[$imageField]['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES[$imageField]['tmp_name'];
        $originalName = $_FILES[$imageField]['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Validate file extension
        if (!in_array($ext, $allowedExtensions)) {
            $error = "Invalid file type for $imageField. Allowed types: " . implode(', ', $allowedExtensions);
            return null;
        }

        // Validate file size
        if ($_FILES[$imageField]['size'] > $maxFileSize) {
            $error = "File size for $imageField exceeds 2MB limit.";
            return null;
        }

        // Sanitize filename to prevent issues
        $safeProductCode = preg_replace('/[^a-zA-Z0-9_-]/', '_', $product_code);
        $timestamp = time();
        $filename = $safeProductCode . '_' . substr($imageField, 5) . '_' . $timestamp . '.' . $ext;
        $destination = 'product_images/' . $filename;

        if (!file_exists('product_images')) {
            mkdir('product_images', 0755, true);
        }

        if (move_uploaded_file($tmpName, $destination)) {
            return $destination;
        } else {
            $error = "Failed to move uploaded file for $imageField.";
            return null;
        }
    }
    return null;
}

// Function to record sale
function recordSale($conn, $productId, $selling_price, $old_stock_quantity, $new_stock_quantity) {
    if ($old_stock_quantity - $new_stock_quantity === 1) {
        try {
            $stmtStock = $conn->prepare("SELECT stock_id, purchase_price FROM stocks WHERE product_id = ? ORDER BY date_of_purchase DESC LIMIT 1");
            $stmtStock->execute([$productId]);
            $stock = $stmtStock->fetch(PDO::FETCH_ASSOC);

            if ($stock) {
                $stock_id = $stock['stock_id'];
                $purchase_price = $stock['purchase_price'];
                $selling_price_val = (float)$selling_price;
                $cogs = (float)$purchase_price;

                // Call record_sale.php via HTTP POST
                $url = 'http://' . $_SERVER['HTTP_HOST'] . '/admin/record_sale.php';
                $postData = json_encode([
                    'stock_id' => $stock_id,
                    'selling_price' => $selling_price_val,
                    'quantity_sold' => $old_stock_quantity - $new_stock_quantity,
                    'cogs' => $cogs,
                    'date_of_sale' => date('Y-m-d')
                ]);

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode !== 200) {
                    error_log("Failed to record sale: HTTP status $httpCode, response: $response");
                } else {
                    error_log("Sale recorded successfully: HTTP status $httpCode, response: $response");
                }
            }
        } catch (Exception $e) {
            error_log("Error recording sale: " . $e->getMessage());
        }
    }
}

$productId = $_GET['product_id'] ?? null;
if (!$productId) {
    redirectToView();
}

$error = '';
$success = '';

try {
    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        redirectToView();
    }
} catch (PDOException $e) {
    $error = "Error fetching product: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and update product
    $category_id = $_POST['category_id'] ?? '';
    $product_code = $_POST['product'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $model = $_POST['model'] ?? '';
    $storage = $_POST['storage'] ?? '';
    $status = $_POST['status'] ?? '';
    $stock_quantity = $_POST['stock_quantity'] ?? '';
    $purchase_price = $_POST['purchase_price'] ?? '';
    $selling_price = $_POST['selling_price'] ?? '';

    // Basic validation
    if (!$category_id || !$product_code || !$brand || !$model || !$storage || !$status || !is_numeric($stock_quantity) || !is_numeric($purchase_price) || !is_numeric($selling_price)) {
        $error = "Please fill in all required fields correctly.";
    } else {
        // Additional validation for numeric fields
        if ((int)$stock_quantity < 0) {
            $error = "Stock quantity cannot be negative.";
        } elseif ((float)$purchase_price < 0) {
            $error = "Purchase price cannot be negative.";
        } elseif ((float)$selling_price < 0) {
            $error = "Selling price cannot be negative.";
        } else {
            try {
                // Begin transaction
                $conn->beginTransaction();

                // Update product details except images
                $updateQuery = "UPDATE products SET category_id = ?, product = ?, brand = ?, model = ?, storage = ?, status = ?, stock_quantity = ?, purchase_price = ?, selling_price = ? WHERE product_id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->execute([$category_id, $product_code, $brand, $model, $storage, $status, $stock_quantity, $purchase_price, $selling_price, $productId]);

                // Handle image uploads for image1 to image8
                $imagePaths = [];
                for ($i = 1; $i <= 8; $i++) {
                    $imageField = 'image' . $i;
                    $uploadedPath = handleImageUpload($imageField, $product_code, $error);
                    if ($error) {
                        break;
                    }
                    if ($uploadedPath !== null) {
                        $imagePaths[$imageField] = $uploadedPath;
                    }
                }

                // Update image paths if any uploaded
                if (!empty($imagePaths)) {
                    $setParts = [];
                    $params = [];
                    foreach ($imagePaths as $col => $path) {
                        $setParts[] = "$col = ?";
                        $params[] = $path;
                    }
                    $params[] = $productId;
                    $updateImagesQuery = "UPDATE products SET " . implode(', ', $setParts) . " WHERE product_id = ?";
                    $stmt = $conn->prepare($updateImagesQuery);
                    $stmt->execute($params);
                }

                $conn->commit();
                $success = "Product updated successfully.";

                // After successful product update, record sale if stock quantity decreased by 1
                $old_stock_quantity = $product['stock_quantity'];
                $new_stock_quantity = (int)$stock_quantity;
                recordSale($conn, $productId, $selling_price, $old_stock_quantity, $new_stock_quantity);

                // Refresh product data
                $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (Exception $e) {
                $conn->rollBack();
                $error = "Error updating product: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Product - Bislig iCenter</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            border-color: #3b82f6;
            transform: translateY(-1px);
        }

        .form-input {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
        }

        .form-input:hover {
            border-color: #3b82f6;
            transform: translateY(-1px);
        }

        .image-preview {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
        }

        .image-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
        }

        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .alert-error {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-left: 4px solid #ef4444;
        }

        .alert-success {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-left: 4px solid #22c55e;
        }

        .file-input-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .file-input-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .file-input {
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-input-label:hover {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }

        .modal {
            backdrop-filter: blur(8px);
        }

        .modal-content {
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .section-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            transition: all 0.3s ease;
        }

        .form-group:focus-within label {
            color: #3b82f6;
            transform: translateY(-2px);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .status-badge.old {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border: 2px solid #f59e0b;
        }

        .status-badge.current {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border: 2px solid #3b82f6;
        }

        .status-badge.new {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
            border: 2px solid #22c55e;
        }

        /* Enhanced scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
        }

        /* Loading animation */
        .loading {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        /* Enhanced focus states */
        *:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* Enhanced text selection */
        ::selection {
            background: rgba(59, 130, 246, 0.2);
            color: #1f2937;
        }
    </style>
</head>
<body class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="card p-8">
            <!-- Enhanced Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                        <i class="ri-edit-2-line text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Edit Product</h1>
                        <p class="text-gray-600 mt-1">Update product information and images</p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="view_products.php" class="btn-secondary px-6 py-3 text-gray-700 flex items-center justify-center">
                        <i class="ri-list-check mr-2"></i> Back to Products
                    </a>
                    <a href="admin.php" class="btn-primary px-6 py-3 text-white flex items-center justify-center">
                        <i class="ri-dashboard-line mr-2"></i> Dashboard
                    </a>
                </div>
            </div>

            <!-- Enhanced Alerts -->
            <?php if ($error): ?>
                <div class="alert alert-error p-4 mb-8 flex items-center">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-4">
                        <i class="ri-error-warning-line text-xl text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-red-800">Error</h3>
                        <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success p-4 mb-8 flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                        <i class="ri-checkbox-circle-line text-xl text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-green-800">Success</h3>
                        <p class="text-green-700"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Enhanced Form -->
            <form method="POST" enctype="multipart/form-data" class="space-y-8" id="editProductForm" onsubmit="return showUpdateConfirmation(event)">
                <!-- Product Information Section -->
                <div class="section-header">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="ri-information-line mr-2 text-blue-600"></i>
                        Product Information
                    </h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <div class="form-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="ri-folder-line mr-2 text-blue-600"></i> Category ID
                            </label>
                            <input type="text" name="category_id" value="<?php echo htmlspecialchars($product['category_id']); ?>" 
                                   class="form-input w-full border-2 border-gray-200 p-3 focus:outline-none" required />
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="ri-barcode-line mr-2 text-blue-600"></i> Product Code
                            </label>
                            <input type="text" name="product" value="<?php echo htmlspecialchars($product['product']); ?>" 
                                   class="form-input w-full border-2 border-gray-200 p-3 focus:outline-none" required />
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="ri-trademark-line mr-2 text-blue-600"></i> Brand
                            </label>
                            <input type="text" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" 
                                   class="form-input w-full border-2 border-gray-200 p-3 focus:outline-none" required />
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="ri-device-line mr-2 text-blue-600"></i> Model
                            </label>
                            <input type="text" name="model" value="<?php echo htmlspecialchars($product['model']); ?>" 
                                   class="form-input w-full border-2 border-gray-200 p-3 focus:outline-none" required />
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <div class="form-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="ri-hard-drive-line mr-2 text-blue-600"></i> Storage
                            </label>
                            <input type="text" name="storage" value="<?php echo !empty($product['storage']) ? htmlspecialchars($product['storage']) : 'Not Available'; ?>" 
                                   class="form-input w-full border-2 border-gray-200 p-3 focus:outline-none" required />
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="ri-price-tag-3-line mr-2 text-blue-600"></i> Status
                            </label>
                            <select name="status" class="form-input w-full border-2 border-gray-200 p-3 focus:outline-none" required>
                                <option value="old" <?php if ($product['status'] === 'old') echo 'selected'; ?>>OLD</option>
                                <option value="current" <?php if ($product['status'] === 'current') echo 'selected'; ?>>CURRENT</option>
                                <option value="new" <?php if ($product['status'] === 'new') echo 'selected'; ?>>NEW</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="ri-stack-line mr-2 text-blue-600"></i> Stock Quantity
                            </label>
                            <input type="number" name="stock_quantity" id="stock_quantity" 
                                   value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" 
                                   class="form-input w-full border-2 border-gray-200 p-3 focus:outline-none" 
                                   min="0" required />
                            <input type="hidden" id="original_stock_quantity" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                    <i class="ri-money-dollar-circle-line mr-2 text-green-600"></i> Purchase Price
                                </label>
                                <input type="number" name="purchase_price" value="<?php echo htmlspecialchars($product['purchase_price']); ?>" 
                                       class="form-input w-full border-2 border-gray-200 p-3 focus:outline-none" 
                                       min="0" step="0.01" required />
                            </div>
                            <div class="form-group">
                                <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                    <i class="ri-money-dollar-box-line mr-2 text-red-600"></i> Selling Price
                                </label>
                                <input type="number" name="selling_price" value="<?php echo htmlspecialchars($product['selling_price']); ?>" 
                                       class="form-input w-full border-2 border-gray-200 p-3 focus:outline-none" 
                                       min="0" step="0.01" required />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Images Section -->
                <div class="section-header">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="ri-image-line mr-2 text-blue-600"></i> Product Images
                    </h2>
                    <p class="text-sm text-gray-600 mt-2">Upload new images to replace existing ones. Drag and drop or click to select files.</p>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-gray-700 flex items-center">
                            <i class="ri-image-add-line mr-2 text-blue-600"></i> Image <?php echo $i; ?>
                        </label>
                        
                        <?php if (!empty($product['image'.$i])): ?>
                            <div class="mb-3">
                                <img src="<?php echo htmlspecialchars($product['image'.$i]); ?>" 
                                     alt="Product Image <?php echo $i; ?>" 
                                     class="w-full h-32 object-cover rounded-xl border-2 border-gray-200 image-preview shadow-md" />
                            </div>
                        <?php endif; ?>
                        
                        <div class="file-input-container">
                            <input type="file" name="image<?php echo $i; ?>" id="image<?php echo $i; ?>" 
                                   class="file-input" accept="image/*" />
                            <label for="image<?php echo $i; ?>" class="file-input-label">
                                <i class="ri-upload-cloud-line text-2xl text-gray-400 mb-2"></i>
                                <span class="text-sm text-gray-600">Choose file</span>
                            </label>
                        </div>
                        
                        <div class="mt-3">
                            <img id="preview_image<?php echo $i; ?>" src="#" alt="Image <?php echo $i; ?> Preview" 
                                 class="w-full h-32 object-cover rounded-xl border-2 border-gray-200 image-preview shadow-md hidden" />
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- Enhanced Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-end gap-4 pt-8 border-t border-gray-200">
                    <a href="view_products.php" class="btn-secondary px-8 py-3 text-gray-700 flex items-center justify-center">
                        <i class="ri-close-line mr-2"></i> Cancel
                    </a>
                    <button type="submit" class="btn-primary px-8 py-3 text-white flex items-center justify-center">
                        <i class="ri-save-line mr-2"></i> Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enhanced Update Confirmation Modal -->
    <div id="updateConfirmationModal" class="fixed inset-0 modal flex items-center justify-center hidden z-50">
        <div class="modal-content bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="ri-question-line text-2xl text-white"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Confirm Update</h2>
                <p class="text-gray-600 mb-8">Are you sure you want to update this product's information?</p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <button id="cancelUpdateBtn" class="btn-secondary px-6 py-3 text-gray-700 flex items-center justify-center">
                        <i class="ri-close-line mr-2"></i> Cancel
                    </button>
                    <button id="confirmUpdateBtn" class="btn-primary px-6 py-3 text-white flex items-center justify-center">
                        <i class="ri-check-line mr-2"></i> Confirm Update
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Success Modal -->
    <div id="successModal" class="fixed inset-0 modal flex items-center justify-center hidden z-50">
        <div class="modal-content bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="ri-checkbox-circle-line text-2xl text-white"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Success</h2>
                <p class="text-gray-600 mb-8"><?php echo htmlspecialchars($success); ?></p>
                <button id="closeModalBtn" class="btn-primary px-6 py-3 text-white flex items-center justify-center mx-auto">
                    <i class="ri-close-line mr-2"></i> Close
                </button>
            </div>
        </div>
    </div>

<script>
let currentForm = null;

function showUpdateConfirmation(event) {
    event.preventDefault();
    currentForm = event.target;
    
    // Show update confirmation modal with animation
    const modal = document.getElementById('updateConfirmationModal');
    modal.classList.remove('hidden');
    modal.classList.add('animate-fadeIn');
    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    const updateModal = document.getElementById('updateConfirmationModal');
    const cancelUpdateBtn = document.getElementById('cancelUpdateBtn');
    const confirmUpdateBtn = document.getElementById('confirmUpdateBtn');

    cancelUpdateBtn.addEventListener('click', function() {
        updateModal.classList.add('hidden');
        currentForm = null;
    });

    confirmUpdateBtn.addEventListener('click', function() {
        if (currentForm) {
            // Check if stock quantity changed by 1
            const originalQty = parseInt(document.getElementById('original_stock_quantity').value);
            const newQty = parseInt(document.getElementById('stock_quantity').value);
            
            if (originalQty - newQty === 1) {
                // Show sale confirmation modal
                updateModal.classList.add('hidden');
                document.getElementById('confirmationModal').classList.remove('hidden');
            } else {
                // Submit form directly
                currentForm.submit();
            }
        }
        updateModal.classList.add('hidden');
    });

    // Close modal when clicking outside
    updateModal.addEventListener('click', function(event) {
        if (event.target === updateModal) {
            updateModal.classList.add('hidden');
            currentForm = null;
        }
    });

    // Enhanced file input preview
    for (let i = 1; i <= 8; i++) {
        const input = document.getElementById('image' + i);
        const preview = document.getElementById('preview_image' + i);
        const label = input.nextElementSibling;

        input.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    label.style.borderColor = '#22c55e';
                    label.style.background = 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.classList.add('hidden');
                label.style.borderColor = '#d1d5db';
                label.style.background = 'linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%)';
            }
        });

        // Drag and drop functionality
        label.addEventListener('dragover', function(e) {
            e.preventDefault();
            label.style.borderColor = '#3b82f6';
            label.style.background = 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)';
        });

        label.addEventListener('dragleave', function(e) {
            e.preventDefault();
            label.style.borderColor = '#d1d5db';
            label.style.background = 'linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%)';
        });

        label.addEventListener('drop', function(e) {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                input.files = files;
                input.dispatchEvent(new Event('change'));
            }
        });
    }
});

// Existing sale confirmation modal code
var form = document.getElementById('editProductForm');
var modal = document.getElementById('confirmationModal');
var confirmBtn = document.getElementById('confirmBtn');
var cancelBtn = document.getElementById('cancelBtn');

if (form && modal && confirmBtn && cancelBtn) {
    confirmBtn.addEventListener('click', function() {
        modal.classList.add('hidden');
        if (currentForm) {
            currentForm.submit();
        }
    });

    cancelBtn.addEventListener('click', function() {
        modal.classList.add('hidden');
        currentForm = null;
    });
}
</script>
</body>
</html>
