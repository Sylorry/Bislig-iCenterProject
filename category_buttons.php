<?php
$categories = getAllCategories($conn);
echo '<div id="categoryButtons">';
echo '<button class="category-btn active" data-category="all">All Products</button>';
foreach ($categories as $category) {
  echo '<button class="category-btn" data-category="'.htmlspecialchars($category).'">'.htmlspecialchars($category).'</button>';
}
echo '</div>';
?>