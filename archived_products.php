<?php
require_once 'db.php';

// Fetch archived products
try {
$stmt = $conn->query("SELECT * FROM products WHERE archived = 1 ORDER BY product_id ASC");
    $archivedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $archivedProducts = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Archived Products - Inventory System</title>
  <script src="https://cdn.tailwindcss.com/3.4.16"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f8fafc;
      color: #1a202c;
      min-height: 100vh;
    }
    .container {
      padding: 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }
    .header-card {
      background: #ffffff;
      border-radius: 20px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      border: 1px solid #e2e8f0;
    }
    .content-card {
      background: #ffffff;
      border-radius: 20px;
      padding: 2rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      border: 1px solid #e2e8f0;
    }
    h1 {
      font-size: 2.5rem;
      font-weight: 700;
      color: #1a202c;
      margin-bottom: 0.5rem;
    }
    .subtitle {
      color: #64748b;
      font-size: 1.1rem;
      margin-bottom: 2rem;
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    .stat-card {
      background: #1a202c;
      color: white;
      padding: 1.5rem;
      border-radius: 15px;
      text-align: center;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      border: 1px solid #e2e8f0;
    }
    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    .stat-label {
      font-size: 0.9rem;
      opacity: 0.9;
    }
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background: #ffffff;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      border: 1px solid #e2e8f0;
    }
    th, td {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #e2e8f0;
      text-align: left;
    }
    th {
      background: #1a202c;
      color: white;
      font-weight: 600;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    tr:hover {
      background: #f8fafc;
      transform: translateY(-1px);
      transition: all 0.3s ease;
    }
    tr:last-child td {
      border-bottom: none;
    }
    .btn {
      padding: 0.6rem 1.2rem;
      border-radius: 12px;
      font-weight: 600;
      font-size: 0.875rem;
      cursor: pointer;
      border: none;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      text-decoration: none;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .btn-restore {
      background: #1a202c;
      color: white;
      border: 1px solid #1a202c;
    }
    .btn-restore:hover {
      background: #2d3748;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(26, 32, 44, 0.2);
    }
    .btn-back {
      background: #1a202c;
      color: white;
      border: 1px solid #1a202c;
    }
    .btn-back:hover {
      background: #2d3748;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(26, 32, 44, 0.2);
    }
    .btn-view {
      background: #64748b;
      color: white;
      border: 1px solid #64748b;
    }
    .btn-view:hover {
      background: #475569;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(100, 116, 139, 0.2);
    }
    .message {
      margin-bottom: 1.5rem;
      padding: 1rem 1.5rem;
      border-radius: 12px;
      font-weight: 600;
      border-left: 4px solid;
    }
    .message.success {
      background: #f0fdf4;
      color: #166534;
      border-left-color: #22c55e;
    }
    .message.error {
      background: #fef2f2;
      color: #dc2626;
      border-left-color: #ef4444;
    }
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: #64748b;
    }
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }
    .product-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .product-icon {
      width: 40px;
      height: 40px;
      background: #1a202c;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.2rem;
    }
    .price {
      font-weight: 600;
      color: #1a202c;
    }
    .modal {
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
    }
    .modal-content {
      background: white;
      border-radius: 20px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
      border: 1px solid #e2e8f0;
    }
    .modal-btn {
      padding: 0.75rem 1.5rem;
      border-radius: 12px;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
    }
    .modal-btn-cancel {
      background: #f1f5f9;
      color: #475569;
      border: 1px solid #e2e8f0;
    }
    .modal-btn-cancel:hover {
      background: #e2e8f0;
      transform: translateY(-1px);
    }
    .modal-btn-confirm {
      background: #1a202c;
      color: white;
      border: 1px solid #1a202c;
    }
    .modal-btn-confirm:hover {
      background: #2d3748;
      transform: translateY(-1px);
      box-shadow: 0 4px 15px rgba(26, 32, 44, 0.2);
    }
    .brand-badge {
      background: #f1f5f9;
      color: #475569;
      border: 1px solid #e2e8f0;
    }
    .storage-badge {
      background: #f8fafc;
      color: #64748b;
      border: 1px solid #e2e8f0;
    }
    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }
      .stats-grid {
        grid-template-columns: 1fr;
      }
      table {
        font-size: 0.875rem;
      }
      th, td {
        padding: 0.75rem;
      }
    }
  </style>
</head>
  <body>
    <header class="bg-[#1a1a1a] shadow-sm border-b border-gray-800 mb-6">
      <div class="flex justify-between items-center px-8 py-4 space-x-4">
        <div class="ml-12 mr-10 text-sm text-white">
          <span><?php echo date('l, F j, Y'); ?></span>
        </div>
        <div class="flex items-center space-x-4">
          <!-- Admin icon removed as requested -->
        </div>
      </div>
    </header>
  <div class="container">
    <div class="header-card">
      <div class="mb-6 flex flex-wrap gap-4">
        <a href="admin.php" class="btn btn-back">
          <i class="fas fa-arrow-left"></i>
          Back to Dashboard
        </a>
        <a href="view_products.php" class="btn btn-view">
          <i class="fas fa-eye"></i>
          View Products
        </a>
      </div>
      
      <h1><i class="fas fa-archive mr-3"></i>Archived Products</h1>
      <p class="subtitle">Manage and restore archived products from your inventory</p>
      
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number"><?php echo count($archivedProducts); ?></div>
          <div class="stat-label">Total Archived</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">₱<?php echo number_format(array_sum(array_column($archivedProducts, 'selling_price')), 2); ?></div>
          <div class="stat-label">Total Value</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?php echo count(array_unique(array_column($archivedProducts, 'brand'))); ?></div>
          <div class="stat-label">Unique Brands</div>
        </div>
      </div>
    </div>

    <div class="content-card">
      <?php if (isset($error)): ?>
        <div class="message error">
          <i class="fas fa-exclamation-triangle mr-2"></i>
          Error loading archived products: <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>
      
      <?php if (count($archivedProducts) === 0): ?>
        <div class="empty-state">
          <i class="fas fa-archive"></i>
          <h3 class="text-xl font-semibold mb-2">No Archived Products</h3>
          <p>There are currently no products in the archive.</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table>
            <thead>
              <tr>
                <th>Product</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Storage</th>
                <th>Purchase Price</th>
                <th>Selling Price</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($archivedProducts as $product): ?>
                <tr>
                  <td>
                    <div class="product-info">
                      <div class="product-icon">
                        <i class="fas fa-box"></i>
                      </div>
                      <div>
                        <div class="font-semibold"><?php echo htmlspecialchars($product['product']); ?></div>
                        <div class="text-sm text-gray-500">ID: <?php echo htmlspecialchars($product['product_id']); ?></div>
                      </div>
                    </div>
                  </td>
                                     <td>
                     <span class="px-3 py-1 brand-badge rounded-full text-sm font-medium">
                       <?php echo htmlspecialchars($product['brand']); ?>
                     </span>
                   </td>
                   <td><?php echo htmlspecialchars($product['model']); ?></td>
                   <td>
                     <?php if (!empty($product['storage'])): ?>
                       <span class="px-2 py-1 storage-badge rounded text-sm">
                         <?php echo htmlspecialchars($product['storage']); ?>
                       </span>
                     <?php else: ?>
                       <span class="text-gray-400 text-sm">N/A</span>
                     <?php endif; ?>
                   </td>
                  <td class="price">₱<?php echo number_format($product['purchase_price'], 2); ?></td>
                  <td class="price">₱<?php echo number_format($product['selling_price'], 2); ?></td>
                  <td>
                    <button class="btn btn-restore" data-product-id="<?php echo htmlspecialchars($product['product_id']); ?>">
                      <i class="fas fa-undo"></i>
                      Restore
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const restoreButtons = document.querySelectorAll('.btn-restore');
      const modal = document.getElementById('restoreModal');
      const confirmBtn = document.getElementById('confirmRestore');
      const cancelBtn = document.getElementById('cancelRestore');
      let currentProductId = null;

      function openModal(productId) {
        currentProductId = productId;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      }

      function closeModal() {
        currentProductId = null;
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
      }

      restoreButtons.forEach(button => {
        button.addEventListener('click', function () {
          const productId = this.dataset.productId;
          openModal(productId);
        });
      });

      cancelBtn.addEventListener('click', closeModal);

      // Close modal when clicking outside
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          closeModal();
        }
      });

      confirmBtn.addEventListener('click', function () {
        if (!currentProductId) return;
        
        // Show loading state
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Restoring...';
        confirmBtn.disabled = true;
        
        fetch('restore_product.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ product_id: currentProductId })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Show success message with better styling
            const successDiv = document.createElement('div');
            successDiv.className = 'message success fixed top-4 right-4 z-50';
            successDiv.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${data.message}`;
            document.body.appendChild(successDiv);
            
            setTimeout(() => {
              location.reload();
            }, 1500);
          } else {
            alert('Failed to restore product: ' + data.message);
          }
          closeModal();
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while restoring the product.');
          closeModal();
        })
        .finally(() => {
          confirmBtn.innerHTML = '<i class="fas fa-undo mr-2"></i>Restore';
          confirmBtn.disabled = false;
        });
      });
    });
  </script>

  <!-- Enhanced Restore Confirmation Modal -->
  <div id="restoreModal" class="modal fixed inset-0 flex items-center justify-center hidden z-50">
    <div class="modal-content p-8 max-w-md w-full mx-4">
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-undo text-2xl text-green-600"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Confirm Restore</h3>
        <p class="text-gray-600">Are you sure you want to restore this product? It will be moved back to the active inventory.</p>
      </div>
      <div class="flex justify-end space-x-4">
        <button id="cancelRestore" class="modal-btn modal-btn-cancel">Cancel</button>
        <button id="confirmRestore" class="modal-btn modal-btn-confirm">
          <i class="fas fa-undo mr-2"></i>Restore
        </button>
      </div>
    </div>
  </div>
</body>
</html>
