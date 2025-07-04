<?php
$products = getAllProducts($conn);
foreach ($products as $product) {
  if (!empty($product['archived']) && $product['archived']) {
    continue;
  }
  $imagePath = 'images/default.png';
  if (!empty($product['image1'])) {
    $images = explode(',', $product['image1']);
    $imagePath = trim($images[0]);
  }
  $description = !empty($product['description']) ? $product['description'] : '';
  $price = !empty($product['selling_price']) ? '₱' . number_format($product['selling_price'], 2) : 'Price not available';
  $productName = !empty($product['product']) ? $product['product'] : 'Unnamed Product';
  $productId = !empty($product['product_id']) ? $product['product_id'] : 0;
  $brand = !empty($product['brand']) ? $product['brand'] : '';
  $model = !empty($product['model']) ? $product['model'] : '';
  $categoryName = !empty($product['category_name']) ? $product['category_name'] : 'Uncategorized';
  $storage = !empty($product['storage']) ? $product['storage'] : '';
  $images = array_map('trim', explode(',', $product['image1']));
  if (!empty($product['image2'])) $images[] = $product['image2'];
  if (!empty($product['image3'])) $images[] = $product['image3'];
  if (!empty($product['image4'])) $images[] = $product['image4'];
  if (!empty($product['image5'])) $images[] = $product['image5'];
  $imagesJson = htmlspecialchars(json_encode($images));
  $secondImage = isset($images[1]) ? $images[1] : '';
  
  echo '<div style="display: inline-block; margin: 0 5px; width: 350px; vertical-align: top;">';
  echo '<div class="card" style="width: 350px;" data-product-id="'.$productId.'" data-description="'.htmlspecialchars($description).'" data-price="'.$price.'" data-brand="'.htmlspecialchars($brand).'" data-model="'.htmlspecialchars($model).'" data-category="'.htmlspecialchars($categoryName).'" data-storage="'.htmlspecialchars($storage).'" data-images=\''.$imagesJson.'\' data-second-image="'.htmlspecialchars($secondImage).'">';
  echo '<button class="reserve-btn">Reserve</button>';
  echo '<img src="'.$imagePath.'" alt="'.htmlspecialchars($productName).'" style="width: 100%; height: 320px; object-fit: cover; border-radius: 15px;">';
  echo '<h3 class="card-link">'.htmlspecialchars($productName).'</h3>';
  if ($brand !== '') echo '<p>'.htmlspecialchars($brand).'</p>';
  if ($model !== '') echo '<p>Model: '.htmlspecialchars($model).'</p>';
  if ($storage !== '') echo '<p>Storage: '.htmlspecialchars($storage).'</p>';
  echo '<p class="card-price">'.$price.'</p>';
  echo '<div style="display: flex; justify-content: center; align-items: center; margin-top: 10px;">';
  echo '<button class="details-btn">See Details</button>';
  echo '</div>';
  echo '</div>';
  echo '</div>';
}
?>