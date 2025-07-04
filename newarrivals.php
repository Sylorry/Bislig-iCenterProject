<?php
require_once 'db.php';
require_once 'functions.php';
$conn = getDBConnection();

try {
    $itemsPerPage = 12;
    $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($currentPage - 1) * $itemsPerPage;

    // Get model brand filter from GET parameter
    $modelBrandFilter = isset($_GET['model_brand']) ? strtolower(trim($_GET['model_brand'])) : '';

    // Get storage filter from GET parameter
    $storageFilter = isset($_GET['storage']) ? strtolower(trim($_GET['storage'])) : '';

    // Determine sorting order based on GET parameter
    $sortOption = isset($_GET['sort']) ? $_GET['sort'] : '';
    $orderByClause = 'product ASC'; // default order
    switch ($sortOption) {
        case 'price_asc':
            $orderByClause = 'selling_price ASC';
            break;
        case 'price_desc':
            $orderByClause = 'selling_price DESC';
            break;
        case 'stock_asc':
            $orderByClause = 'stock_quantity ASC';
            break;
        case 'stock_desc':
            $orderByClause = 'stock_quantity DESC';
            break;
    }

    // Fetch distinct model brands for New Arrivals
    $modelStmt = $conn->prepare("SELECT DISTINCT LOWER(model) AS model FROM products WHERE LOWER(product) = 'newarrivals' ORDER BY model ASC");
    $modelStmt->execute();
    $modelBrands = $modelStmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch distinct storage options for New Arrivals that have products
    $storageStmt = $conn->prepare("SELECT DISTINCT LOWER(storage) AS storage FROM products WHERE LOWER(product) = 'newarrivals' ORDER BY storage ASC");
    $storageStmt->execute();
    $storageOptions = $storageStmt->fetchAll(PDO::FETCH_COLUMN);

    // Remove storageWithEntries query as it's redundant
    $storageWithEntries = [];

    // Check if there are products with null or empty storage
    $nullStorageStmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE LOWER(product) = 'newarrivals' AND (storage IS NULL OR storage = '')");
    $nullStorageStmt->execute();
    $hasNullStorage = $nullStorageStmt->fetchColumn() > 0;

    // Build count query with filters
    $countQuery = "SELECT COUNT(*) FROM products WHERE LOWER(product) = 'newarrivals'";
    $countParams = [];
    if ($modelBrandFilter !== '' && $modelBrandFilter !== 'all models') {
        $countQuery .= " AND LOWER(model) = :model_brand";
        $countParams[':model_brand'] = $modelBrandFilter;
    }
    if ($storageFilter !== '' && $storageFilter !== 'all storages') {
        $countQuery .= " AND LOWER(storage) = :storage";
        $countParams[':storage'] = $storageFilter;
    }
    $countStmt = $conn->prepare($countQuery);
    foreach ($countParams as $key => $value) {
        $countStmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalProductsCount = $countStmt->fetchColumn();

    // Build select query with filters
    $selectQuery = "SELECT * FROM products WHERE LOWER(product) = 'newarrivals'";
    $selectParams = [];
    if ($modelBrandFilter !== '' && $modelBrandFilter !== 'all models') {
        $selectQuery .= " AND LOWER(model) = :model_brand";
        $selectParams[':model_brand'] = $modelBrandFilter;
    }
    if ($storageFilter !== '' && $storageFilter !== 'all storages') {
        $selectQuery .= " AND LOWER(storage) = :storage";
        $selectParams[':storage'] = $storageFilter;
    }
    $selectQuery .= " ORDER BY $orderByClause LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($selectQuery);
    foreach ($selectParams as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalPages = ceil($totalProductsCount / $itemsPerPage);
} catch (PDOException $e) {
    echo '<div class="text-red-500">Error loading New Arrivals products: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $products = [];
    $totalPages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>New Arrivals Products - Inventory System</title>
  <script src="https://cdn.tailwindcss.com/3.4.16"></script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
      color: #1f2937;
      min-height: 100vh;
      margin: 0;
      padding: 0;
    }
    .product-card {
      background-color: #ffffff;
      color: #1f2937;
      padding: 1.5rem;
      border-radius: 1rem;
      border: 1px solid #e5e7eb;
      box-shadow: 0 6px 20px rgba(0,0,0,0.12);
      transition: box-shadow 0.4s ease, border-color 0.4s ease, transform 0.4s ease;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }
    .product-card:hover {
      border-color: #2563eb;
      box-shadow: 0 16px 40px rgba(37, 99, 235, 0.5);
      transform: translateY(-10px);
      z-index: 10;
    }
    .product-card img {
      width: 140px;
      height: 140px;
      object-fit: cover;
      border-radius: 0.75rem;
      margin-bottom: 1.25rem;
      transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    .product-card img:hover {
      transform: scale(1.2);
      box-shadow: 0 12px 24px rgba(37, 99, 235, 0.6);
      z-index: 20;
    }
    .product-card h3 {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 0.75rem;
      color: #1e40af;
    }
    .product-card p {
      margin: 0.3rem 0;
      font-size: 1.1rem;
      color: #334155;
    }
    .status-badge {
      padding: 0.5rem 1rem;
      border-radius: 9999px;
      font-weight: 700;
      display: inline-block;
      margin-top: 1rem;
      font-size: 1rem;
      position: relative;
      cursor: default;
      user-select: none;
      box-shadow: 0 0 8px rgba(0,0,0,0.15);
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 2.5rem;
      margin-bottom: 3.5rem;
      padding: 1.5rem 2.5rem;
      transition: all 0.3s ease;
    }
    /* Model Brand Buttons */
    #modelBrandButtons a {
      font-weight: 700;
      padding: 0.6rem 1.2rem;
      border-radius: 9999px;
      border: 2px solid transparent;
      transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
      user-select: none;
      text-decoration: none;
      color: #334155;
      background-color: #e5e8f0;
      box-shadow: 0 3px 8px rgba(0,0,0,0.12);
    }
    #modelBrandButtons a:hover {
      background-color: #2563eb;
      color: white;
      border-color: #1e40af;
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.7);
    }
    #modelBrandButtons a.bg-blue-600 {
      background-color: #2563eb !important;
      color: white !important;
      border-color: #1e40af !important;
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.7) !important;
    }
    /* Pagination */
    nav.flex a {
      font-weight: 700;
      padding: 0.6rem 0.85rem;
      border-radius: 0.5rem;
      text-decoration: none;
      color: #334155;
      background-color: #e5e8f0;
      box-shadow: 0 3px 8px rgba(0,0,0,0.12);
      transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
    }
    nav.flex a:hover {
      background-color: #2563eb;
      color: white;
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.7);
    }
    nav.flex a.bg-blue-600 {
      background-color: #2563eb !important;
      color: white !important;
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.7) !important;
    }
  </style>
</head>
<body>
  <header class="p-8 flex items-center space-x-4 bg-gray-100 border-b border-gray-300">
    <a href="kiosk.php" class="inline-flex items-center px-4 py-2 bg-black text-white rounded-md shadow hover:bg-gray-800 transition-colors duration-200 font-semibold">
      &larr; Back to Dashboard
    </a>
    <h1 class="text-3xl font-semibold ml-4">New Arrivals Products</h1>
  </header>

  <main class="p-8">
    <!-- Model Brand Buttons -->
    <div class="flex flex-wrap gap-2 mb-4 justify-center" id="modelBrandButtons">
      <?php
      $currentModelBrand = $modelBrandFilter !== '' ? $modelBrandFilter : 'all models';
      $activeClass = ($currentModelBrand === 'all models') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300 hover:border-gray-500';
      $urlParams = [];
      $urlParams['page'] = 1;
      $urlParams['sort'] = $sortOption;
      $urlParams['storage'] = $storageFilter;
      $queryString = http_build_query($urlParams);
      echo '<a href="?' . $queryString . '" class="px-4 py-2 rounded-full border border-transparent cursor-pointer transition-colors duration-300 ' . $activeClass . '" onclick="handleModelBrandClick(event)">All Models</a>';

      foreach ($modelBrands as $model) {
          $modelLower = strtolower($model);
          if ($modelLower === 'not available') {
              // Skip rendering "Not Available" model brand button
              continue;
          }
          $activeClass = ($modelLower === $currentModelBrand) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300 hover:border-gray-500';
          $urlParams = [];
          $urlParams['model_brand'] = $modelLower;
          $urlParams['page'] = 1;
          $urlParams['sort'] = $sortOption;
          $urlParams['storage'] = $storageFilter;
          $queryString = http_build_query($urlParams);
          echo '<a href="?' . $queryString . '" class="px-4 py-2 rounded-full border border-transparent cursor-pointer transition-colors duration-300 ' . $activeClass . '" onclick="handleModelBrandClick(event)">' . htmlspecialchars($model) . '</a>';
      }
      ?>
    </div>

      <!-- Storage Buttons -->
      <div class="flex flex-wrap gap-2 mb-6 justify-center" id="storageButtons">
        <?php
        $currentStorage = $storageFilter !== '' ? $storageFilter : 'all storages';
        $activeClassStorage = ($currentStorage === 'all storages') ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300 hover:border-gray-500';
        $urlParamsStorage = [];
        $urlParamsStorage['page'] = 1;
        $urlParamsStorage['sort'] = $sortOption;
        $urlParamsStorage['model_brand'] = $modelBrandFilter;
        $queryStringStorage = http_build_query($urlParamsStorage);
        echo '<a href="?' . $queryStringStorage . '" class="px-4 py-2 rounded-full border border-transparent cursor-pointer transition-colors duration-300 ' . $activeClassStorage . '" onclick="handleStorageClick(event)">All Storages</a>';

        // Filter out null, empty, and 'not available' storage values
        $filteredStorageOptions = array_filter($storageOptions, function($storage) {
            $storageLower = strtolower($storage);
            return $storageLower !== '' && $storageLower !== 'not available' && $storageLower !== null;
        });

        foreach ($filteredStorageOptions as $storage) {
            $storageLower = strtolower($storage);
            $activeClassStorage = ($storageLower === $currentStorage) ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300 hover:border-gray-500';
            $urlParamsStorage = [];
            $urlParamsStorage['storage'] = $storageLower;
            $urlParamsStorage['page'] = 1;
            $urlParamsStorage['sort'] = $sortOption;
            $urlParamsStorage['model_brand'] = $modelBrandFilter;
            $queryStringStorage = http_build_query($urlParamsStorage);
            echo '<a href="?' . $queryStringStorage . '" class="px-4 py-2 rounded-full border border-transparent cursor-pointer transition-colors duration-300 ' . $activeClassStorage . '" onclick="handleStorageClick(event)">' . htmlspecialchars($storage) . '</a>';
        }
        ?>
      </div>

    <div class="grid">
      <?php
      if (count($products) === 0) {
          echo '<p class="col-span-full text-center text-gray-500">No New Arrivals products found.</p>';
      } else {
          foreach ($products as $product) {
              $productId = $product['product_id']; // Assuming 'id' is the primary key column
              echo '<div class="product-card">';
              echo '<input type="checkbox" class="compare-checkbox" data-product-id="' . $productId . '" id="compare_' . $productId . '" />';
              echo '<label for="compare_' . $productId . '" class="compare-label">Compare</label>';
              // Display first available image
              $imageDisplayed = false;
              for ($i = 1; $i <= 8; $i++) {
                  $imageColumn = 'image' . $i;
                  if (!$imageDisplayed && !empty($product[$imageColumn])) {
                      echo '<img src="' . htmlspecialchars($product[$imageColumn]) . '" alt="Product Image" />';
                      $imageDisplayed = true;
                  }
              }
              echo '<h3 class="font-bold text-lg mb-2">' . htmlspecialchars($product['product']) . '</h3>';
              echo '<p><strong>Brand:</strong> ' . htmlspecialchars($product['brand']) . '</p>';
              echo '<p><strong>Model:</strong> ' . htmlspecialchars($product['model']) . '</p>';
              $storageDisplay = !empty($product['storage']) ? htmlspecialchars($product['storage']) : 'Not Available';
              echo '<p><strong>Storage:</strong> ' . $storageDisplay . '</p>';
              echo '<p><strong>Purchase Price:</strong> ₱' . number_format($product['purchase_price'], 2) . '</p>';
              echo '<p><strong>Selling Price:</strong> ₱' . number_format($product['selling_price'], 2) . '</p>';
              echo '</div>';
          }
      }
      ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="flex justify-center space-x-2 mt-6">
      <?php for ($page = 1; $page <= $totalPages; $page++): ?>
        <a href="?page=<?= $page ?>&sort=<?= htmlspecialchars($sortOption) ?>&model_brand=<?= htmlspecialchars($modelBrandFilter) ?>&storage=<?= htmlspecialchars($storageFilter) ?>" class="px-3 py-1 rounded <?= $page === $currentPage ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
          <?= $page ?>
        </a>
      <?php endfor; ?>
    </nav>
    <?php endif; ?>
  </main>

  <div id="compareBar" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #2563eb; color: white; padding: 10px 20px; border-radius: 9999px; font-weight: 700; cursor: pointer; display: none; z-index: 1000;">
    Compare Selected Products
  </div>

  <!-- Modal for product comparison -->
  <div id="compareModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-5xl w-full max-h-[80vh] overflow-auto p-6 relative">
      <button id="closeModal" class="absolute top-4 right-4 text-gray-600 hover:text-gray-900 text-2xl font-bold">&times;</button>
      <h2 class="text-2xl font-semibold mb-4">Compare Selected Products</h2>
      <div id="compareContent" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 overflow-auto">
        <!-- Comparison details will be injected here -->
      </div>
    </div>
  </div>

  <script>
    const compareCheckboxes = document.querySelectorAll('.compare-checkbox');
    const compareBar = document.getElementById('compareBar');
    const compareModal = document.getElementById('compareModal');
    const compareContent = document.getElementById('compareContent');
    const closeModal = document.getElementById('closeModal');
    let selectedProducts = [];

    compareCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('change', () => {
        const productId = checkbox.getAttribute('data-product-id');
        if (checkbox.checked) {
          selectedProducts.push(productId);
        } else {
          selectedProducts = selectedProducts.filter(id => id !== productId);
        }
        updateCompareBar();
      });
    });

    function updateCompareBar() {
      if (selectedProducts.length >= 2) {
        compareBar.style.display = 'block';
      } else {
        compareBar.style.display = 'none';
      }
    }

    compareBar.addEventListener('click', () => {
      // Clear previous content
      compareContent.innerHTML = '';

      // Get product cards on the page
      const productCards = document.querySelectorAll('.product-card');

      // Filter selected products details
      const productsToCompare = [];
      productCards.forEach(card => {
        const checkbox = card.querySelector('.compare-checkbox');
        if (checkbox && selectedProducts.includes(checkbox.getAttribute('data-product-id'))) {
          // Extract product details from the card
          const productDetails = {
            id: checkbox.getAttribute('data-product-id'),
            image: card.querySelector('img') ? card.querySelector('img').src : '',
            product: card.querySelector('h3') ? card.querySelector('h3').textContent : '',
            brand: card.querySelector('p:nth-of-type(1)') ? card.querySelector('p:nth-of-type(1)').textContent.replace('Brand: ', '') : '',
            model: card.querySelector('p:nth-of-type(2)') ? card.querySelector('p:nth-of-type(2)').textContent.replace('Model: ', '') : '',
            storage: card.querySelector('p:nth-of-type(3)') ? card.querySelector('p:nth-of-type(3)').textContent.replace('Storage: ', '') : '',
            purchasePrice: card.querySelector('p:nth-of-type(4)') ? card.querySelector('p:nth-of-type(4)').textContent.replace('Purchase Price: ₱', '') : '',
            sellingPrice: card.querySelector('p:nth-of-type(5)') ? card.querySelector('p:nth-of-type(5)').textContent.replace('Selling Price: ₱', '') : '',
          };
          productsToCompare.push(productDetails);
        }
      });

      // Build comparison cards inside modal
      productsToCompare.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card bg-gray-50 p-4 rounded-lg shadow flex flex-col items-center';

        const img = document.createElement('img');
        img.src = product.image;
        img.alt = product.product;
        img.className = 'w-32 h-32 object-cover rounded-md mb-4';
        productCard.appendChild(img);

        const title = document.createElement('h3');
        title.textContent = product.product;
        title.className = 'text-xl font-semibold mb-2';
        productCard.appendChild(title);

        const brand = document.createElement('p');
        brand.textContent = 'Brand: ' + product.brand;
        brand.className = 'mb-1';
        productCard.appendChild(brand);

        const model = document.createElement('p');
        model.textContent = 'Model: ' + product.model;
        model.className = 'mb-1';
        productCard.appendChild(model);

        const storage = document.createElement('p');
        storage.textContent = 'Storage: ' + product.storage;
        storage.className = 'mb-1';
        productCard.appendChild(storage);

        const purchasePrice = document.createElement('p');
        purchasePrice.textContent = 'Purchase Price: ₱' + product.purchasePrice;
        purchasePrice.className = 'mb-1';
        productCard.appendChild(purchasePrice);

        const sellingPrice = document.createElement('p');
        sellingPrice.textContent = 'Selling Price: ₱' + product.sellingPrice;
        sellingPrice.className = 'mb-1';
        productCard.appendChild(sellingPrice);

        compareContent.appendChild(productCard);
      });

      // Show modal
      compareModal.classList.remove('hidden');
    });

    closeModal.addEventListener('click', () => {
      compareModal.classList.add('hidden');
    });

    // Close modal when clicking outside the modal content
    compareModal.addEventListener('click', (event) => {
      if (event.target === compareModal) {
        compareModal.classList.add('hidden');
      }
    });

    function handleModelBrandClick(event) {
      event.preventDefault();
      const url = event.currentTarget.href;
      const modelBrandButtons = document.getElementById('modelBrandButtons');
      modelBrandButtons.style.transition = 'opacity 0.3s ease';
      modelBrandButtons.style.opacity = '0.5';
      setTimeout(() => {
        window.location.href = url;
      }, 300);
    }
    function handleStorageClick(event) {
      event.preventDefault();
      const url = event.currentTarget.href;
      const storageButtons = document.getElementById('storageButtons');
      storageButtons.style.transition = 'opacity 0.3s ease';
      storageButtons.style.opacity = '0.5';
      setTimeout(() => {
        window.location.href = url;
      }, 300);
    }
  </script>
</body>
</html>
