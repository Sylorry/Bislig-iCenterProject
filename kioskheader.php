<header>
  <nav class="menu-bar">
    <img src="images/icenter.png" alt="Logo" class="logo" />
    <div class="menu-wrapper">
      <ul>
        <li><a href="kiosk.php#container3">SHOP NOW</a></li>
        <li>
          <span class="dropdown-toggle" data-target="productsDropdown">
            PRODUCTS <i class="fas fa-caret-down"></i>
          </span>
          <div id="productsDropdown" class="dropdown-menu">
            <?php
            $productCategories = getAllCategories($conn);
            $linkMap = [
              'ACCESSORIES' => 'accessories.php',
              'AIRPODS' => 'airpods.php',
              'ANDROID' => 'android.php',
              'IPAD' => 'ipad.php',
              'IPHONE' => 'iphone.php',
              'PC SET' => 'pcset.php',
              'PRINTER' => 'printer.php',
              'LAPTOP' => 'laptop.php',
            ];
            foreach($productCategories as $category) {
              if(!in_array($category, ['Featured', 'Best Seller'])) {
                $link = isset($linkMap[$category]) ? $linkMap[$category] : 'get_categories.php?category=' . urlencode($category);
                echo '<a href="' . htmlspecialchars($link) . '">' . htmlspecialchars($category) . '</a>';
              }
            }
            ?>
          </div>
        </li>
        <li>
          <a href="kiosk.php#container2">COLLECTIONS</a>
        </li>
        <li>
          <a href="reservations.php">RESERVATIONS</a>
        </li>
        <li>
          <span class="dropdown-toggle" data-target="careDropdown">
            CUSTOMER CARE <i class="fas fa-caret-down"></i>
          </span>
          <div id="careDropdown" class="dropdown-menu">
            <a href="javascript:void(0);" class="care-modal-trigger" data-modal="contactUsModal">Contact Us</a>
            <a href="javascript:void(0);" class="care-modal-trigger" data-modal="howToOrderModal">How to Order</a>
            <!-- Removed Online Shipping as per user request -->
            <a href="javascript:void(0);" class="care-modal-trigger" data-modal="returnsRefundsModal">Returns and Refunds</a>
            <a href="javascript:void(0);" class="care-modal-trigger" data-modal="warrantyModal">Warranty</a>
          </div>
        </li>
      </ul>
    </div>
    <div class="search-container">
      <a href="javascript:void(0);" id="searchIcon" title="Search" class="search-icon">
        <i class="fas fa-search"></i>
      </a>
      <input
        type="text"
        id="searchInput"
        class="search-input"
        placeholder="Search for products or brands..."
        onkeyup="searchProducts()"
      />
      <button id="clearSearchBtn" title="Clear Search" style="display:none; border:none; background:none; cursor:pointer; font-size:18px; color:#555; margin-left:4px;">&times;</button>
    </div>
  </nav>
</header>