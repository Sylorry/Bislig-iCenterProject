<div id="reserveModal" class="modal">
  <div class="modal-content" style="max-width: 500px; width: 90%; margin: 50px auto; background: #fff; border-radius: 16px; padding: 30px; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
    <span class="close" style="position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; color: #666; transition: color 0.3s;">&times;</span>
    
    <h3 style="text-align: center; margin-bottom: 25px; color: #333; font-size: 24px;">Confirm Reservation</h3>
    
    <div style="text-align: center; margin-bottom: 30px;">
      <img class="modal-product-image" src="" alt="" style="max-width: 200px; max-height: 200px; border-radius: 12px; margin: 0 auto 20px; display: block;">
      <h4 class="modal-product-name" style="margin: 15px 0; color: #333; font-size: 20px;"></h4>
      <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
        <p class="modal-product-brand" style="color: #666; margin: 5px 0;"></p>
        <p class="modal-product-model" style="color: #666; margin: 5px 0;"></p>
        <p class="modal-product-price" style="color: #007dd1; font-weight: bold; font-size: 1.2em; margin: 10px 0;"></p>
        <p class="modal-product-id" style="color: #666; margin: 5px 0; font-size: 0.9em;">Product ID: <span></span></p>
      </div>
    </div>
    
    <p style="text-align: center; margin: 20px 0; color: #555;">Would you like to proceed with reserving this product?</p>
    
    <div style="display: flex; justify-content: center; gap: 15px; margin-top: 25px;">
      <button class="modal-btn cancel-btn" style="padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s; background: #f8f9fa; color: #666;">Cancel</button>
      <button class="modal-btn confirm-btn" style="padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s; background: #007dd1; color: white;">Proceed to Reservation</button>
    </div>
  </div>
</div>

<!-- Success Message Modal -->
<div id="successModal" class="modal">
  <div class="modal-content" style="max-width: 400px; width: 90%; margin: 50px auto; background: #fff; border-radius: 16px; padding: 30px; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
    <span class="close" style="position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; color: #666; transition: color 0.3s;">&times;</span>
    
    <div style="text-align: center; margin-bottom: 20px;">
      <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745; margin-bottom: 16px;"></i>
      <h3 style="color: #333; font-size: 20px; margin-bottom: 12px;">Success!</h3>
      <p style="color: #666; font-size: 16px; line-height: 1.5;">Your product has been successfully added to reservations.</p>
    </div>
    
    <div style="display: flex; justify-content: center;">
      <button class="modal-btn confirm-btn" style="padding: 10px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s; background: #007dd1; color: white;">Continue</button>
    </div>
  </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="modal">
  <div class="modal-content" style="max-width: 800px; width: 90%; margin: 5% auto; background: white; border-radius: 15px; padding: 20px; position: relative;">
    <span class="close" style="position: absolute; right: 20px; top: 15px; font-size: 28px; cursor: pointer; color: #666;">&times;</span>
    
    <div style="display: flex; flex-direction: column; gap: 20px;">
      <!-- Image Section -->
      <div class="modal-image-container" style="width: 100%; position: relative;">
        <img class="modal-product-image" src="" alt="" style="width: 100%; height: 400px; object-fit: contain; border-radius: 10px; background: #f8f9fa;">
        <div id="modalThumbnails" class="modal-thumbnails" style="display: flex; gap: 10px; margin-top: 10px; overflow-x: auto; padding: 5px; justify-content: center; flex-wrap: wrap;"></div>
      </div>

      <!-- Info Section -->
      <div class="modal-info" style="flex: 1;">
        <h3 class="modal-product-name" style="font-size: 28px; margin-bottom: 20px; color: #333; text-align: center; font-weight: 600;"></h3>
        
        <div class="modal-details" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 12px;">
          <div class="detail-item" style="text-align: center; grid-column: 1 / -1;">
            <strong style="color: #666; display: block; margin-bottom: 8px; font-size: 14px;">Price</strong>
            <span class="modal-product-price" style="color:rgb(209, 0, 45); font-weight: bold; font-size: 24px;"></span>
          </div>
          <div class="detail-item" style="text-align: center;">
            <strong style="color: #666; display: block; margin-bottom: 8px; font-size: 14px;">Brand</strong>
            <span class="modal-product-brand" style="color: #333; font-size: 16px;"></span>
          </div>
          <div class="detail-item" style="text-align: center;">
            <strong style="color: #666; display: block; margin-bottom: 8px; font-size: 14px;">Model</strong>
            <span class="modal-product-model" style="color: #333; font-size: 16px;"></span>
          </div>
          <div class="detail-item" style="text-align: center;">
            <strong style="color: #666; display: block; margin-bottom: 8px; font-size: 14px;">Storage</strong>
            <span class="modal-product-storage" style="color: #333; font-size: 16px;"></span>
          </div>
        </div>

        <div class="modal-description" style="margin-top: 20px; background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e0e0e0;">
          <p class="modal-product-description" style="color: #444; line-height: 1.6; font-size: 16px; margin: 0;"></p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Customer Care Modals -->
<div id="contactUsModal" class="care-modal">
  <div class="modal-content">
    <span class="care-modal-close">&times;</span>
    <h3>Contact Us</h3>
    <p>For any inquiries or assistance, please contact us through:</p>
    <div class="contact-info">
      <p><strong>Phone:</strong> 0976 003 5417</p>
      <p><strong>Email:</strong> support@bisligicenter.com</p>
      <p><strong>Address:</strong> Bislig City, Surigao del Sur</p>
    </div>
  </div>
</div>

<div id="howToOrderModal" class="care-modal">
  <div class="modal-content">
    <span class="care-modal-close">&times;</span>
    <h3>How to Order</h3>
    <div class="order-steps">
      <p>1. Browse our products and select the items you want</p>
      <p>2. Click on the "Reserve" button for your chosen product</p>
      <p>3. Fill out the reservation form with your details</p>
      <p>4. Wait for our confirmation call or message</p>
      <p>5. Visit our store to complete your purchase</p>
    </div>
  </div>
</div>

<div id="returnsRefundsModal" class="care-modal">
  <div class="modal-content">
    <span class="care-modal-close">&times;</span>
    <h3>Returns and Refunds</h3>
    <div class="returns-info">
      <p>We accept returns within 7 days of purchase for the following reasons:</p>
      <ul>
        <li>Manufacturing defects</li>
        <li>Wrong item received</li>
        <li>Damaged during delivery</li>
      </ul>
      <p>Please bring your receipt and the item in its original packaging.</p>
    </div>
  </div>
</div>

<div id="warrantyModal" class="care-modal">
  <div class="modal-content">
    <span class="care-modal-close">&times;</span>
    <h3>Warranty Information</h3>
    <div class="warranty-info">
      <p>All our products come with manufacturer warranty:</p>
      <ul>
        <li>iPhones: 1 year international warranty</li>
        <li>Accessories: 6 months warranty</li>
        <li>Other devices: As per manufacturer policy</li>
      </ul>
      <p>Please keep your receipt and warranty card for any claims.</p>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const reserveModal = document.getElementById('reserveModal');
    const successModal = document.getElementById('successModal');
    const closeBtn = reserveModal.querySelector('.close');
    const cancelBtn = reserveModal.querySelector('.cancel-btn');
    const confirmBtn = reserveModal.querySelector('.confirm-btn');
    const successCloseBtn = successModal.querySelector('.close');
    const successContinueBtn = successModal.querySelector('.confirm-btn');

    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        closeModal(reserveModal);
      });
    }

    if (cancelBtn) {
      cancelBtn.addEventListener('click', () => {
        closeModal(reserveModal);
      });
    }

    if (confirmBtn) {
      confirmBtn.addEventListener('click', () => {
        closeModal(reserveModal);
        openModal(successModal);
      });
    }

    if (successCloseBtn) {
      successCloseBtn.addEventListener('click', () => {
        closeModal(successModal);
        window.location.href = 'reservations.php';
      });
    }

    if (successContinueBtn) {
      successContinueBtn.addEventListener('click', () => {
        closeModal(successModal);
        window.location.href = 'reservations.php';
      });
    }

    // Close modal when clicking outside modal content
    window.addEventListener('click', (event) => {
      if (event.target === reserveModal) {
        closeModal(reserveModal);
      }
      if (event.target === document.getElementById('detailsModal')) {
        document.getElementById('detailsModal').style.display = 'none';
      }
      if (event.target === successModal) {
        closeModal(successModal);
        window.location.href = 'reservations.php';
      }
    });

    // Details modal close button
    const detailsModalClose = document.querySelector('.close');
    if (detailsModalClose) {
      detailsModalClose.addEventListener('click', () => {
        document.getElementById('detailsModal').style.display = 'none';
      });
    }

    // Function to update thumbnails in details modal
    function updateThumbnails(images) {
      const thumbnailsContainer = document.getElementById('modalThumbnails');
      thumbnailsContainer.innerHTML = '';
      images.forEach((src, idx) => {
        const img = document.createElement('img');
        img.src = src;
        img.style.width = '100%';
        img.style.height = '80px';
        img.style.objectFit = 'cover';
        img.style.borderRadius = '8px';
        img.style.cursor = 'pointer';
        img.style.border = idx === 0 ? '2px solid #007dd1' : '2px solid transparent';
        img.style.transition = 'border-color 0.3s ease, transform 0.2s ease';
        
        img.addEventListener('mouseenter', () => {
          img.style.transform = 'scale(1.05)';
        });
        
        img.addEventListener('mouseleave', () => {
          img.style.transform = 'scale(1)';
        });
        
        img.addEventListener('click', () => {
          const mainImage = document.querySelector('.modal-product-image');
          mainImage.src = src;
          // Update active thumbnail
          thumbnailsContainer.querySelectorAll('img').forEach(thumb => {
            thumb.style.border = '2px solid transparent';
          });
          img.style.border = '2px solid #007dd1';
        });
        
        thumbnailsContainer.appendChild(img);
      });
    }

    // Example usage: updateThumbnails with an array of image URLs
    // You should replace this with actual product images dynamically
    // updateThumbnails(['img1.jpg', 'img2.jpg', 'img3.jpg']);
  });
</script>
