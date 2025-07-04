<?php
require_once 'db.php';
require_once 'functions.php';
$conn = getConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BISLIG iCENTER</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Roboto&family=Roboto+Slab&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="kiosk.css">
</head>

<body>
  <?php include 'kioskheader.php'; ?>

  <div id="container1">
    <div class="video-container">
      <video id="video0" class="active" controls autoplay muted loop>
        <source src="images/iPhone 16 Pro.mp4" type="video/mp4">
      </video> 
      <div class="video-overlay">
        <h3 class="video-title">iPhone 16 Pro</h3>
        <p class="video-description">Experience the future of mobile technology</p>
        <div class="video-controls">
          <button class="video-control-btn play">
            <i class="fas fa-play"></i>
          </button>
          <button class="video-control-btn pause">
            <i class="fas fa-pause"></i>
          </button>
          <button class="video-control-btn mute">
            <i class="fas fa-volume-up"></i>
          </button>
        </div>
      </div>
    </div>
    <div id="introMessage">
      <h2>Welcome to BiSLIG iCENTER</h2>
      <p>Your premier destination for cutting-edge technology and exceptional service. We bring you the latest innovations in mobile devices, accessories, and tech solutions, all backed by our commitment to quality and customer satisfaction.</p>
      <div class="feature-grid">
        <div class="feature-item">
          <i class="fas fa-mobile-alt"></i>
          <h3>Latest iPhone Models</h3>
          <p>Experience the newest Apple innovations with our extensive iPhone collection</p>
        </div>
        </div>
        <div class="feature-item">
          <i class="fas fa-tags"></i>
          <h3>Competitive Pricing</h3>
          <p>Get the best value with our competitive prices and special offers</p>
        </div>
        <div class="feature-item">
          <i class="fas fa-shield-alt"></i>
          <h3>Warranty Coverage</h3>
          <p>Shop with confidence with our comprehensive warranty protection</p>
        </div>
      </div>
    </div>
  </div>

  <div id="container2">
    <h2>Our Collections</h2>
    <p>Explore our diverse range of collections featuring the latest accessories, gadgets, and tech essentials curated to meet your needs.</p>
    <div id="productGrid2">
      <?php include 'kioskcollections.php'; ?>
    </div>
  </div>

  <div id="container3">
    <h2>
      Our PRODUCTS
      <span></span>
    </h2>
    <?php include 'category_buttons.php'; ?>
    <div id="carouselContainer">
      <button id="carouselPrev">&#8249;</button>
      <button id="carouselNext">&#8250;</button>
      <div id="productGrid">
        <?php include 'kioskproducts.php'; ?>
      </div>
    </div>
  </div>

  <?php include 'kioskmodals.php'; ?>

  <div id="container4">
    <footer>
      <p>&copy; <?php echo date("Y"); ?> BISLIG iCENTER. All rights reserved.</p>
      <p>
        Contact us: support@bisligicenter.com | 0976 003 5417
      </p>
       
      <div class="footer-icons">
        <a href="https://www.facebook.com/bisligicenter" target="_blank" title="Facebook">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="javascript:void(0);" id="callIcon" title="Call Us">
          <i class="fas fa-phone"></i>
        </a>
        <a href="https://maps.app.goo.gl/jfV82bbHNnCWb3jXA" target="_blank" title="Google Map">
          <i class="fas fa-map-marker-alt"></i>
        </a>
      </div>
    </footer>
  </div>

  <!-- Call Modal -->
  <div id="callModal" class="modal">
    <div class="modal-content">
      <span id="closeCallModal" class="close">&times;</span>
      <h3>Contact Number</h3>
      <p>Ernie E. Mag-aso</p>
      <p>0976 003 5417</p>
    </div>
  </div>

  <script src="kiosk.js"></script>
</body>
</html>