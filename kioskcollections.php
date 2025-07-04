<?php
// Add CSS styles for hover effect
echo '<style>
.collection-card {
    text-decoration: none;
    color: inherit;
}

.collection-title {
    transition: all 0.3s ease;
    display: inline-block;
}

.collection-title:hover {
    color: #007bff;
    transform: translateX(5px);
}

.collection-title i {
    transition: transform 0.3s ease;
}

.collection-title:hover i {
    transform: translateX(5px);
}
</style>';

$items = [
  'ACCESSORIES' => [
    'image' => 'images/Accessoriesgif.gif',
    'description' => 'Essential accessories to complement your devices.',
    'price' => '₱100 - ₱5,000',
  ],
  'AIRPODS' => [
    'image' => 'images/airpodsgif.gif',
    'description' => 'High-quality wireless earbuds for immersive sound.',
    'price' => '₱3,000 - ₱15,000',
  ],
  'PC SET' => [
    'image' => 'images/pc.png',
    'description' => 'Complete PC setups for home and office.',
    'price' => '₱10,000 - ₱100,000',
  ],
  'ANDROID' => [
    'image' => 'images/Androidgif.gif',
    'description' => 'Latest Android smartphones and gadgets.',
    'price' => '₱5,000 - ₱50,000',
  ],
  'IPAD' => [
    'image' => 'images/Ipadgif.gif',
    'description' => 'Powerful tablets for work and play.',
    'price' => '₱15,000 - ₱60,000',
  ],
  'NEW ARRIVALS' => [
    'image' => 'images/Newarrivalsgif.gif',
    'description' => 'Newest arrivals in our collection.',
    'price' => 'Varies by product',
  ],
  'IPHONE' => [
    'image' => 'images/Iphonegif.gif',
    'description' => 'Apple iPhones with cutting-edge technology.',
    'price' => '₱20,000 - ₱80,000',
  ],
  'LAPTOP' => [
    'image' => 'images/Laptopgif.gif',
    'description' => 'Portable laptops for productivity on the go.',
    'price' => '₱15,000 - ₱90,000',
  ],
  'PRINTER' => [
    'image' => 'images/Printergif.gif',
    'description' => 'Reliable printers for all your printing needs.',
    'price' => '₱3,000 - ₱25,000',
  ],
];

foreach ($items as $item => $details):
  $linkMap = [
    'IPHONE' => 'iphone.php',
    'IPAD' => 'ipad.php',
    'AIRPODS' => 'airpods.php',
    'PC SET' => 'pcset.php',
    'LAPTOP' => 'laptop.php',
    'PRINTER' => 'printer.php',
    'ANDROID' => 'android.php',
    'ACCESSORIES' => 'accessories.php',
    'NEW ARRIVALS' => 'newarrivals.php',
  ];
  $link = isset($linkMap[$item]) ? $linkMap[$item] : '#';
  $serverImagePath = __DIR__ . '/' . $details['image'];
  if (!file_exists($serverImagePath)):
    $details['image'] = 'uploads/default_thumbnail.jpg';
    $serverImagePath = __DIR__ . '/' . $details['image'];
    if (!file_exists($serverImagePath)):
      $details['image'] = 'uploads/default.jpg';
      $serverImagePath = __DIR__ . '/' . $details['image'];
      if (!file_exists($serverImagePath)):
        $details['image'] = '';
      endif;
    endif;
  endif;
?>
<a href="<?php echo htmlspecialchars($link); ?>" class="collection-card">
  <?php if ($details['image'] !== ''): ?>
    <img src="<?php echo htmlspecialchars($details['image']); ?>" alt="<?php echo htmlspecialchars($item); ?>" class="collection-image">
  <?php endif; ?>
  <div class="collection-content">
    <h3 class="collection-title">
      <?php echo htmlspecialchars($item); ?>
      <i class="fas fa-arrow-right"></i>
    </h3>
    <p class="collection-description"><?php echo htmlspecialchars($details['description']); ?></p>
    <div class="collection-price">
      <i class="fas fa-tag"></i>
      <?php echo htmlspecialchars($details['price']); ?>
    </div>
  </div>
</a>
<?php endforeach; ?>