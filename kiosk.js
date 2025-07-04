document.addEventListener('DOMContentLoaded', function () {
  console.log('DOM Content Loaded'); // Debug log

  // Initialize all UI components
  initModals();
  initCategoryFiltering();
  initSearch();
  initCarousel();
  initVideoControls();
  initScrollToTop();
  initProductDetails();
  initProductImageHover();
  
  // Initial UI setup
  centerProductsIfFit();

  // === COMPONENT INITIALIZATION FUNCTIONS ===

  /**
   * Initialize all modal dialogs
   */
  function initModals() {
    console.log('Initializing modals'); // Debug log
    
    // Initialize all modals
    const modals = document.querySelectorAll('.modal, .care-modal');
    console.log('Found modals:', modals.length); // Debug log
    
    modals.forEach(modal => {
      const closeBtn = modal.querySelector('.close, .care-modal-close');
      const modalContent = modal.querySelector('.modal-content');
      
      // Close button click handler
      if (closeBtn) {
        closeBtn.addEventListener('click', () => {
          console.log('Close button clicked'); // Debug log
          closeModal(modal);
        });
      }
      
      // Click outside modal handler
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          console.log('Clicked outside modal'); // Debug log
          closeModal(modal);
        }
      });
      
      // ESC key handler
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'block') {
          console.log('ESC key pressed'); // Debug log
          closeModal(modal);
        }
      });
      
      // Prevent modal content clicks from closing modal
      if (modalContent) {
        modalContent.addEventListener('click', (e) => {
          e.stopPropagation();
        });
      }
    });

    // Call modal specific handlers
    const callModal = document.getElementById('callModal');
    const callIcon = document.getElementById('callIcon');
    const closeCallModal = document.getElementById('closeCallModal');

    if (callIcon && callModal) {
      callIcon.addEventListener('click', () => {
        openModal(callModal);
      });
    }

    if (closeCallModal && callModal) {
      closeCallModal.addEventListener('click', () => {
        closeModal(callModal);
      });
    }

    // Customer care modals
    document.querySelectorAll('.care-modal-trigger').forEach(trigger => {
      trigger.addEventListener('click', function(e) {
        e.preventDefault();
        const modalId = this.getAttribute('data-modal');
        const modal = document.getElementById(modalId);
        if (modal) {
          openModal(modal);
        }
      });
    });
  }

  /**
   * Open a modal with animation
   * @param {HTMLElement} modal - The modal element to open
   */
  function openModal(modal) {
    if (!modal) {
      console.error('Modal element not found');
      return;
    }
    
    console.log('Opening modal:', modal); // Debug log
    
    // Prevent body scrolling
    document.body.style.overflow = 'hidden';
    
    // Show modal
    modal.style.display = 'block';
    
    // Force reflow
    modal.offsetHeight;
    
    // Trigger animation
    requestAnimationFrame(() => {
      modal.classList.add('show');
    });
    
    // Focus trap
    const focusableElements = modal.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    if (focusableElements.length) {
      const firstFocusable = focusableElements[0];
      const lastFocusable = focusableElements[focusableElements.length - 1];
      
      // Focus first element
      firstFocusable.focus();
      
      // Handle tab key
      modal.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
          if (e.shiftKey) {
            if (document.activeElement === firstFocusable) {
              e.preventDefault();
              lastFocusable.focus();
            }
          } else {
            if (document.activeElement === lastFocusable) {
              e.preventDefault();
              firstFocusable.focus();
            }
          }
        }
      });
    }

    // Add ESC key handler
    const escHandler = function(e) {
      if (e.key === 'Escape') {
        closeModal(modal);
      }
    };
    document.addEventListener('keydown', escHandler);
    modal._escHandler = escHandler;

    // Add click outside handler
    const clickOutsideHandler = function(e) {
      if (e.target === modal) {
        closeModal(modal);
      }
    };
    modal.addEventListener('click', clickOutsideHandler);
    modal._clickOutsideHandler = clickOutsideHandler;
  }

  /**
   * Close a modal with animation
   * @param {HTMLElement} modal - The modal element to close
   */
  function closeModal(modal) {
    if (!modal) {
      console.error('Modal element not found');
      return;
    }
    
    console.log('Closing modal:', modal); // Debug log
    
    // Remove show class for animation
    modal.classList.remove('show');
    
    // Remove event listeners
    if (modal._escHandler) {
      document.removeEventListener('keydown', modal._escHandler);
      delete modal._escHandler;
    }
    if (modal._clickOutsideHandler) {
      modal.removeEventListener('click', modal._clickOutsideHandler);
      delete modal._clickOutsideHandler;
    }
    
    // Wait for animation to complete
    setTimeout(() => {
      modal.style.display = 'none';
      // Restore body scrolling
      document.body.style.overflow = '';
    }, 300);
  }

  /**
   * Initialize category filtering buttons
   */
  function initCategoryFiltering() {
    const categoryButtons = document.querySelectorAll('.category-btn');
    const productGridContainer = document.getElementById('productGrid');

    if (!categoryButtons || !productGridContainer) return;

    // Show all product cards by default
    const productCards = productGridContainer.querySelectorAll('.card');
    productCards.forEach(card => {
      card.style.display = 'flex';
    });

    categoryButtons.forEach(btn => {
      btn.addEventListener('click', function () {
        const selectedCategory = this.getAttribute('data-category');
        
        // Filter products by category
        filterProducts(selectedCategory, null);
        
        // Reset scroll position
        resetScrollPosition();
        
        // Highlight active button
        categoryButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
      });
    });
  }

  /**
   * Initialize search functionality
   */
  function initSearch() {
    const searchIcon = document.getElementById('searchIcon');
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const productGrid = document.getElementById('productGrid');
    
    if (!searchIcon || !searchInput || !productGrid) return;

    // Create search results container
    const searchResults = document.createElement('div');
    searchResults.className = 'search-results';
    searchIcon.parentElement.appendChild(searchResults);

    // Toggle search input
    searchIcon.addEventListener('click', () => {
      searchInput.classList.toggle('active');
      if (searchInput.classList.contains('active')) {
        searchInput.style.display = 'inline-block';
        searchInput.focus();
      } else {
        searchInput.style.display = 'none';
        searchInput.value = '';
        searchResults.classList.remove('active');
        clearSearchBtn.classList.remove('visible');
        // Reset search
        resetSearch();
      }
    });

    // Clear search
    clearSearchBtn.addEventListener('click', () => {
      searchInput.value = '';
      searchResults.classList.remove('active');
      clearSearchBtn.classList.remove('visible');
      resetSearch();
    });

    // Handle search input
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
      const query = e.target.value.trim().toLowerCase();
      
      // Show/hide clear button
      clearSearchBtn.classList.toggle('visible', query.length > 0);
      
      // Clear previous timeout
      clearTimeout(searchTimeout);
      
      // Set new timeout for search
      searchTimeout = setTimeout(() => {
        if (query.length > 0) {
          performSearch(query);
        } else {
          resetSearch();
          searchResults.classList.remove('active');
        }
      }, 300); // 300ms delay for better performance
    });

    // Close search results when clicking outside
    document.addEventListener('click', (e) => {
      if (!searchIcon.contains(e.target) && !searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.remove('active');
      }
    });

    // Perform search
    function performSearch(query) {
      const productCards = productGrid.querySelectorAll('.card');
      const results = [];
      
      productCards.forEach(card => {
        const name = card.querySelector('.card-link').textContent.toLowerCase();
        const brand = card.getAttribute('data-brand')?.toLowerCase() || '';
        const model = card.getAttribute('data-model')?.toLowerCase() || '';
        const category = card.getAttribute('data-category')?.toLowerCase() || '';
        const image = card.querySelector('img')?.src || '';
        
        if (name.includes(query) || brand.includes(query) || model.includes(query) || category.includes(query)) {
          results.push({
            name,
            brand,
            model,
            category,
            image
          });
        }
      });

      displayResults(results);
    }

    // Display search results
    function displayResults(results) {
      searchResults.innerHTML = '';
      
      if (results.length === 0) {
        searchResults.innerHTML = '<div class="no-results">No products found</div>';
      } else {
        results.forEach(result => {
          const resultItem = document.createElement('div');
          resultItem.className = 'search-result-item';
          resultItem.innerHTML = `
            <img src="${result.image}" alt="${result.name}">
            <div class="result-info">
              <div class="result-title">${result.name}</div>
              <div class="result-category">${result.category}</div>
            </div>
          `;
          
          // Add click handler to scroll to product
          resultItem.addEventListener('click', () => {
            const productCard = Array.from(productGrid.querySelectorAll('.card')).find(card => 
              card.querySelector('.card-link').textContent.toLowerCase() === result.name
            );
            
            if (productCard) {
              productCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
              productCard.style.animation = 'highlight 1s ease';
              searchResults.classList.remove('active');
              searchInput.value = '';
              clearSearchBtn.classList.remove('visible');
            }
          });
          
          searchResults.appendChild(resultItem);
        });
      }
      
      searchResults.classList.add('active');
    }

    // Reset search
    function resetSearch() {
      const productCards = productGrid.querySelectorAll('.card');
      productCards.forEach(card => {
        card.style.display = 'flex';
        card.style.animation = '';
      });
    }
  }

  /**
   * Initialize carousel navigation
   */
  function initCarousel() {
    const carouselContainer = document.getElementById('carouselContainer');
    const carouselPrev = document.getElementById('carouselPrev');
    const carouselNext = document.getElementById('carouselNext');
    const productGrid = document.getElementById('productGrid');

    if (!carouselContainer || !carouselPrev || !carouselNext || !productGrid) return;

    // --- ENHANCED BUTTON STYLES & ICONS ---
    [carouselPrev, carouselNext].forEach((btn, idx) => {
      btn.innerHTML = `
        <svg width="28" height="28" viewBox="0 0 28 28" aria-hidden="true" focusable="false">
          <circle cx="14" cy="14" r="13" fill="none"/>
          <polyline points="${idx === 0 ? '18,8 10,14 18,20' : '10,8 18,14 10,20'}" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      `;
      btn.style.width = '48px';
      btn.style.height = '48px';
      btn.style.border = '2px solid #eee';
      btn.style.borderRadius = '50%';
      btn.style.background = '#fff';
      btn.style.color = '#222';
      btn.style.boxShadow = '0 2px 8px rgba(0,0,0,0.10)';
      btn.style.display = 'flex';
      btn.style.alignItems = 'center';
      btn.style.justifyContent = 'center';
      btn.style.fontSize = '1.5rem';
      btn.style.cursor = 'pointer';
      btn.style.transition = 'background 0.2s, color 0.2s, border 0.2s, opacity 0.2s';
      btn.style.opacity = '0.85';
      btn.style.margin = '0 8px';
      btn.style.position = 'absolute';
      btn.style.top = '50%';
      btn.style.transform = 'translateY(-50%)';
      btn.style.zIndex = '10';

      if (idx === 0) {
        btn.style.left = '20px';
      } else {
        btn.style.right = '20px';
      }

      // Hover/focus: invert to black bg, white icon
      btn.addEventListener('mouseenter', () => {
        btn.style.background = '#111';
        btn.style.color = '#fff';
        btn.style.border = '2px solid #111';
      });
      btn.addEventListener('mouseleave', () => {
        btn.style.background = '#fff';
        btn.style.color = '#222';
        btn.style.border = '2px solid #eee';
      });
      btn.addEventListener('focus', () => {
        btn.style.background = '#111';
        btn.style.color = '#fff';
        btn.style.border = '2px solid #111';
      });
      btn.addEventListener('blur', () => {
        btn.style.background = '#fff';
        btn.style.color = '#222';
        btn.style.border = '2px solid #eee';
      });
    });

    let isScrolling = false;
    let scrollTimeout;

    const scrollToNext = () => {
      if (isScrolling) return;
      isScrolling = true;

      const cardWidth = getCardWidth();
      const maxScrollLeft = productGrid.scrollWidth - productGrid.clientWidth;
      let newScrollLeft = productGrid.scrollLeft + cardWidth;

      // If we're at the end, loop back to start
      if (newScrollLeft >= maxScrollLeft) {
        newScrollLeft = 0;
      }

      productGrid.scrollTo({
        left: newScrollLeft,
        behavior: 'smooth'
      });

      clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(() => {
        isScrolling = false;
        updateCarouselButtons();
      }, 500);
    };

    const scrollToPrev = () => {
      if (isScrolling) return;
      isScrolling = true;

      const cardWidth = getCardWidth();
      let newScrollLeft = productGrid.scrollLeft - cardWidth;

      // If we're at the start, loop to end
      if (newScrollLeft <= 0) {
        newScrollLeft = productGrid.scrollWidth - productGrid.clientWidth;
      }

      productGrid.scrollTo({
        left: newScrollLeft,
        behavior: 'smooth'
      });

      clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(() => {
        isScrolling = false;
        updateCarouselButtons();
      }, 500);
    };

    // Add click event listeners
    carouselNext.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      scrollToNext();
    });

    carouselPrev.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      scrollToPrev();
    });

    // Add keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') {
        scrollToPrev();
      } else if (e.key === 'ArrowRight') {
        scrollToNext();
      }
    });

    // Monitor for changes and update button states
    const updateCarouselButtons = () => {
      const maxScrollLeft = productGrid.scrollWidth - productGrid.clientWidth;
      
      // Show/hide buttons based on content width
      if (productGrid.scrollWidth <= productGrid.clientWidth) {
        carouselPrev.style.display = 'none';
        carouselNext.style.display = 'none';
      } else {
        carouselPrev.style.display = 'flex';
        carouselNext.style.display = 'flex';
      }

      // Update button states based on scroll position
      carouselPrev.style.opacity = productGrid.scrollLeft <= 0 ? '0.3' : '0.85';
      carouselNext.style.opacity = productGrid.scrollLeft >= maxScrollLeft ? '0.3' : '0.85';
      
      carouselPrev.style.pointerEvents = productGrid.scrollLeft <= 0 ? 'none' : 'auto';
      carouselNext.style.pointerEvents = productGrid.scrollLeft >= maxScrollLeft ? 'none' : 'auto';
    };

    // Add scroll event listener
    productGrid.addEventListener('scroll', () => {
      clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(updateCarouselButtons, 100);
    });

    // Monitor for changes
    window.addEventListener('resize', updateCarouselButtons);
    new MutationObserver(updateCarouselButtons).observe(productGrid, { 
      childList: true, 
      subtree: false 
    });

    // Initial update
    updateCarouselButtons();
  }
  
  // Update resetScrollPosition to call updateCarouselButtons
  function resetScrollPosition() {
    const productGridContainer = document.getElementById('productGrid');
    if (!productGridContainer) return;
    
    setTimeout(() => {
      // Get visible cards
      const productCards = productGridContainer.querySelectorAll('.card');
      const visibleCards = Array.from(productCards).filter(card => card.style.display !== 'none');
      
      // Calculate dimensions
      const containerWidth = productGridContainer.clientWidth;
      const cardWidth = visibleCards.length > 0 ? visibleCards[0].offsetWidth + 10 : 0; // card width + gap
      const totalWidth = cardWidth * visibleCards.length;

      if (totalWidth > containerWidth) {
        // Get carousel container padding
        const carouselContainer = document.getElementById('carouselContainer');
        const carouselPaddingLeft = carouselContainer ? 
          parseInt(window.getComputedStyle(carouselContainer).paddingLeft) : 0;

        // Scroll to first visible card
        const firstVisibleCard = visibleCards[0];
        const scrollLeftValue = firstVisibleCard ? 
          firstVisibleCard.offsetLeft - carouselPaddingLeft : 0;
          
        productGridContainer.scrollLeft = scrollLeftValue > 0 ? scrollLeftValue : 0;
        productGridContainer.style.justifyContent = 'flex-start';
        productGridContainer.style.overflowX = 'auto';
      } else {
        productGridContainer.scrollLeft = 0;
        productGridContainer.style.justifyContent = 'center';
        productGridContainer.style.overflowX = 'hidden';
      }
      
      // Update carousel buttons after scroll reset
      updateCarouselButtons();
    }, 0);
  }
  
  // Update filterProducts to call updateCarouselButtons after filtering
  function filterProducts(category, searchTerm) {
    const productGridContainer = document.getElementById('productGrid');
    if (!productGridContainer) return;
    
    const productCards = productGridContainer.querySelectorAll('.card');
    
    productCards.forEach(card => {
      const name = card.querySelector('.card-link').textContent.toLowerCase();
      const brand = card.getAttribute('data-brand').toLowerCase();
      const model = card.getAttribute('data-model').toLowerCase();
      const cardCategory = card.getAttribute('data-category').toLowerCase();
      
      let visible = true;
      
      // Apply category filter if specified
      if (category && category !== 'all') {
        visible = cardCategory === category.toLowerCase();
      }
      
      // Apply search filter if specified
      if (visible && searchTerm) {
        visible = name.includes(searchTerm) || 
                 brand.includes(searchTerm) || 
                 model.includes(searchTerm) || 
                 cardCategory.includes(searchTerm);
      }
      
      card.style.display = visible ? 'flex' : 'none';
    });
    
    // Update UI based on filtered results
    centerProductsIfFit();
    
    // Update carousel buttons after filtering
    updateCarouselButtons();
  }

  /**
   * Initialize video player controls
   */
  function initVideoControls() {
    const video = document.getElementById('video0');
    if (!video) return;
    
    const playBtn = document.querySelector('.video-control-btn.play');
    const pauseBtn = document.querySelector('.video-control-btn.pause');
    const muteBtn = document.querySelector('.video-control-btn.mute');
    
    if (playBtn) playBtn.addEventListener('click', () => video.play());
    if (pauseBtn) pauseBtn.addEventListener('click', () => video.pause());
    if (muteBtn) muteBtn.addEventListener('click', () => video.muted = !video.muted);
  }

  /**
   * Initialize scroll-to-top button
   */
  function initScrollToTop() {
    // Create scroll-to-top button
    const scrollTopBtn = document.createElement('button');
    scrollTopBtn.innerHTML = `
      <svg width="28" height="28" viewBox="0 0 28 28" aria-hidden="true" focusable="false">
        <circle cx="14" cy="14" r="13" fill="none"/>
        <polyline points="8,16 14,10 20,16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    `;
    scrollTopBtn.style.filter = 'drop-shadow(0 2px 6px rgba(0,0,0,0.25))';
    scrollTopBtn.id = 'scrollTopBtn';
    scrollTopBtn.setAttribute('aria-label', 'Scroll to top');
    scrollTopBtn.style.position = 'fixed';
    scrollTopBtn.style.bottom = '32px';
    scrollTopBtn.style.right = '32px';
    scrollTopBtn.style.width = '48px';
    scrollTopBtn.style.height = '48px';
    scrollTopBtn.style.padding = '0';
    scrollTopBtn.style.fontSize = '2rem';
    scrollTopBtn.style.fontWeight = 'bold';
    scrollTopBtn.style.border = '2px solid #fff';
    scrollTopBtn.style.borderRadius = '50%';
    scrollTopBtn.style.background = '#111';
    scrollTopBtn.style.color = '#fff';
    scrollTopBtn.style.cursor = 'pointer';
    scrollTopBtn.style.display = 'none';
    scrollTopBtn.style.zIndex = '10000';
    scrollTopBtn.style.boxShadow = '0 4px 16px rgba(0,0,0,0.25)';
    scrollTopBtn.style.opacity = '0';
    scrollTopBtn.style.transition = 'opacity 0.4s, background 0.2s, color 0.2s, border 0.2s';

    // Hover/focus effect: invert colors
    scrollTopBtn.addEventListener('mouseenter', () => {
      scrollTopBtn.style.background = '#fff';
      scrollTopBtn.style.color = '#111';
      scrollTopBtn.style.border = '2px solid #111';
    });
    scrollTopBtn.addEventListener('mouseleave', () => {
      scrollTopBtn.style.background = '#111';
      scrollTopBtn.style.color = '#fff';
      scrollTopBtn.style.border = '2px solid #fff';
    });
    scrollTopBtn.addEventListener('focus', () => {
      scrollTopBtn.style.background = '#fff';
      scrollTopBtn.style.color = '#111';
      scrollTopBtn.style.border = '2px solid #111';
    });
    scrollTopBtn.addEventListener('blur', () => {
      scrollTopBtn.style.background = '#111';
      scrollTopBtn.style.color = '#fff';
      scrollTopBtn.style.border = '2px solid #fff';
    });

    document.body.appendChild(scrollTopBtn);

    // Show button when scrolled down
    window.addEventListener('scroll', function () {
      if (window.scrollY > 200) {
        scrollTopBtn.style.display = 'block';
        setTimeout(() => { scrollTopBtn.style.opacity = '1'; }, 10);
      } else {
        scrollTopBtn.style.opacity = '0';
        setTimeout(() => { scrollTopBtn.style.display = 'none'; }, 400);
      }
    });

    // Smooth scroll to top on button click
    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      scrollTopBtn.blur();
    });

    // Keyboard accessibility: Enter/Space triggers scroll
    scrollTopBtn.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        scrollTopBtn.blur();
      }
    });
  }

  /**
   * Initialize product details and reserve modals
   */
  function initProductDetails() {
    console.log('Initializing product details');
    
    const detailsModal = document.getElementById('detailsModal');
    const reserveModal = document.getElementById('reserveModal');
    const successModal = document.getElementById('successModal');
    
    if (!detailsModal || !reserveModal || !successModal) {
      console.error('Required modals not found');
      return;
    }

    // Get modal elements
    const modalImage = detailsModal.querySelector('.modal-product-image');
    const modalName = detailsModal.querySelector('.modal-product-name');
    const modalDescription = detailsModal.querySelector('.modal-product-description');
    const modalPrice = detailsModal.querySelector('.modal-product-price');
    const modalBrand = detailsModal.querySelector('.modal-product-brand');
    const modalModel = detailsModal.querySelector('.modal-product-model');
    const modalStorage = detailsModal.querySelector('.modal-product-storage');
    const closeBtn = detailsModal.querySelector('.close');

    // Close modal when clicking the close button
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        closeModal(detailsModal);
      });
    }

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
      if (e.target === detailsModal) {
        closeModal(detailsModal);
      }
    });

    // Close modal when pressing Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && detailsModal.style.display === 'block') {
        closeModal(detailsModal);
      }
    });

    const productGrid = document.getElementById('productGrid');
    if (!productGrid) {
      console.error('Product grid not found');
      return;
    }

    // Use event delegation for details button clicks
    productGrid.addEventListener('click', function(e) {
      const detailsBtn = e.target.closest('.details-btn');
      if (!detailsBtn) return;

      e.preventDefault();
      e.stopPropagation();

      const card = detailsBtn.closest('.card');
      if (!card) return;

      // Gather product data
      const name = card.querySelector('.card-link')?.textContent || '';
      const description = card.getAttribute('data-description') || '';
      const image = card.querySelector('img')?.src || '';
      const price = card.querySelector('.card-price')?.textContent || '';
      const brand = card.getAttribute('data-brand') || '';
      const model = card.getAttribute('data-model') || '';
      const storage = card.getAttribute('data-storage') || '';
      const imagesJson = card.getAttribute('data-images') || '[]';

      let images = [];
      try { 
        images = JSON.parse(imagesJson); 
      } catch { 
        images = [image]; 
      }
      if (!Array.isArray(images) || images.length === 0) images = [image];

      // Fill modal fields with animation
      if (modalName) {
        modalName.textContent = name;
        modalName.style.opacity = '0';
        setTimeout(() => modalName.style.opacity = '1', 50);
      }
      if (modalDescription) {
        modalDescription.textContent = description;
        modalDescription.style.opacity = '0';
        setTimeout(() => modalDescription.style.opacity = '1', 100);
      }
      if (modalImage) {
        modalImage.style.opacity = '0';
        setTimeout(() => {
          modalImage.src = images[0] || image;
          modalImage.alt = name;
          modalImage.style.opacity = '1';
        }, 150);
      }
      if (modalPrice) {
        modalPrice.textContent = price;
        modalPrice.style.opacity = '0';
        setTimeout(() => modalPrice.style.opacity = '1', 200);
      }
      if (modalBrand) {
        modalBrand.textContent = brand;
        modalBrand.style.opacity = '0';
        setTimeout(() => modalBrand.style.opacity = '1', 250);
      }
      if (modalModel) {
        modalModel.textContent = model;
        modalModel.style.opacity = '0';
        setTimeout(() => modalModel.style.opacity = '1', 300);
      }
      if (modalStorage) {
        modalStorage.textContent = storage;
        modalStorage.style.opacity = '0';
        setTimeout(() => modalStorage.style.opacity = '1', 350);
      }

      // Update thumbnails
      updateThumbnails(images, name, modalImage);

      // Open the details modal with animation
      openModal(detailsModal);
    });

    // Add event listeners for reserve buttons in product cards
    document.querySelectorAll('#productGrid .reserve-btn').forEach(reserveBtn => {
      reserveBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const card = this.closest('.card');
        const productData = {
          product_id: card.getAttribute('data-product-id'),
          name: card.querySelector('.card-link').textContent,
          image: card.querySelector('img').src,
          price: card.querySelector('.card-price')?.textContent || '',
          brand: card.getAttribute('data-brand') || '',
          model: card.getAttribute('data-model') || '',
          category: card.getAttribute('data-category') || ''
        };

        // Update modal content
        const modalProductImage = reserveModal.querySelector('.modal-product-image');
        const modalProductName = reserveModal.querySelector('.modal-product-name');
        const modalProductBrand = reserveModal.querySelector('.modal-product-brand');
        const modalProductModel = reserveModal.querySelector('.modal-product-model');
        const modalProductPrice = reserveModal.querySelector('.modal-product-price');
        const modalProductId = reserveModal.querySelector('.modal-product-id span');

        modalProductImage.src = productData.image;
        modalProductName.textContent = productData.name;
        modalProductBrand.textContent = productData.brand;
        modalProductModel.textContent = productData.model;
        modalProductPrice.textContent = productData.price;
        modalProductId.textContent = productData.product_id;

        // Store product data in sessionStorage
        const productDetails = {
          product_id: productData.product_id,
          name: productData.name,
          image: productData.image,
          price: productData.price,
          brand: productData.brand,
          model: productData.model,
          category: productData.category,
          selected: true,
          timestamp: new Date().getTime()
        };
        sessionStorage.setItem('selectedProduct', JSON.stringify(productDetails));

        // Open the reserve modal
        openModal(reserveModal);
      });
    });

    // Add event listeners for reserve modal buttons
    const confirmBtn = reserveModal.querySelector('.confirm-btn');
    const cancelBtn = reserveModal.querySelector('.cancel-btn');
    const reserveCloseBtn = reserveModal.querySelector('.close');

    if (confirmBtn) {
      confirmBtn.addEventListener('click', () => {
        closeModal(reserveModal);
        openModal(successModal);
      });
    }

    if (cancelBtn) {
      cancelBtn.addEventListener('click', () => {
        closeModal(reserveModal);
      });
    }

    if (reserveCloseBtn) {
      reserveCloseBtn.addEventListener('click', () => {
        closeModal(reserveModal);
      });
    }

    // Add event listeners for success modal
    const successCloseBtn = successModal.querySelector('.close');
    const successContinueBtn = successModal.querySelector('.confirm-btn');

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

    // Close success modal when clicking outside
    window.addEventListener('click', (e) => {
      if (e.target === successModal) {
        closeModal(successModal);
        window.location.href = 'reservations.php';
      }
    });
  }

  /**
   * Update thumbnails in the details modal
   */
  function updateThumbnails(images, productName, mainImage) {
    const thumbnailsContainer = document.getElementById('modalThumbnails');
    if (!thumbnailsContainer) return;

    thumbnailsContainer.innerHTML = '';
    
    images.forEach((src, idx) => {
      const thumbnail = document.createElement('img');
      thumbnail.src = src;
      thumbnail.alt = `${productName} - Image ${idx + 1}`;
      thumbnail.style.cssText = `
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        border: ${idx === 0 ? '2px solid #007dd1' : '2px solid transparent'};
        transition: all 0.3s ease;
        opacity: 0;
      `;

      // Add hover effects
      thumbnail.addEventListener('mouseenter', () => {
        thumbnail.style.transform = 'scale(1.1)';
        thumbnail.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
      });

      thumbnail.addEventListener('mouseleave', () => {
        thumbnail.style.transform = 'scale(1)';
        thumbnail.style.boxShadow = 'none';
      });

      // Add click handler
      thumbnail.addEventListener('click', () => {
        // Update main image with fade effect
        mainImage.style.opacity = '0';
        setTimeout(() => {
          mainImage.src = src;
          mainImage.style.opacity = '1';
        }, 150);

        // Update active thumbnail
        thumbnailsContainer.querySelectorAll('img').forEach(thumb => {
          thumb.style.border = '2px solid transparent';
        });
        thumbnail.style.border = '2px solid #007dd1';
      });

      thumbnailsContainer.appendChild(thumbnail);
      
      // Fade in thumbnail
      setTimeout(() => {
        thumbnail.style.opacity = '1';
      }, 100 * idx);
    });
  }

  /**
   * Initialize product image hover effects
   */
  function initProductImageHover() {
    const productCards = document.querySelectorAll('.card');
    
    productCards.forEach(card => {
      const mainImage = card.querySelector('img');
      if (!mainImage) return;
      
      // Get the second image from data attribute if available
      const secondImage = card.getAttribute('data-second-image');
      if (!secondImage) return;
      
      // Store original image
      const originalSrc = mainImage.src;
      
      // Add hover effects
      card.addEventListener('mouseenter', () => {
        mainImage.style.opacity = '0';
        setTimeout(() => {
          mainImage.src = secondImage;
          mainImage.style.opacity = '1';
        }, 150);
      });
      
      card.addEventListener('mouseleave', () => {
        mainImage.style.opacity = '0';
        setTimeout(() => {
          mainImage.src = originalSrc;
          mainImage.style.opacity = '1';
        }, 150);
      });
    });
  }

  // === UTILITY FUNCTIONS ===

  /**
   * Filter products by category or search term
   * @param {string|null} category - Category to filter by
   * @param {string|null} searchTerm - Search term to filter by
   */
  function filterProducts(category, searchTerm) {
    const productGridContainer = document.getElementById('productGrid');
    if (!productGridContainer) return;
    
    const productCards = productGridContainer.querySelectorAll('.card');
    
    productCards.forEach(card => {
      const name = card.querySelector('.card-link').textContent.toLowerCase();
      const brand = card.getAttribute('data-brand').toLowerCase();
      const model = card.getAttribute('data-model').toLowerCase();
      const cardCategory = card.getAttribute('data-category').toLowerCase();
      
      let visible = true;
      
      // Apply category filter if specified
      if (category && category !== 'all') {
        visible = cardCategory === category.toLowerCase();
      }
      
      // Apply search filter if specified
      if (visible && searchTerm) {
        visible = name.includes(searchTerm) || 
                 brand.includes(searchTerm) || 
                 model.includes(searchTerm) || 
                 cardCategory.includes(searchTerm);
      }
      
      card.style.display = visible ? 'flex' : 'none';
    });
    
    // Update UI based on filtered results
    centerProductsIfFit();
    updateCarouselButtons();
  }

  /**
   * Reset scroll position of product grid
   */
  function resetScrollPosition() {
    const productGridContainer = document.getElementById('productGrid');
    if (!productGridContainer) return;
    
    setTimeout(() => {
      // Get visible cards
      const productCards = productGridContainer.querySelectorAll('.card');
      const visibleCards = Array.from(productCards).filter(card => card.style.display !== 'none');
      
      // Calculate dimensions
      const containerWidth = productGridContainer.clientWidth;
      const cardWidth = visibleCards.length > 0 ? visibleCards[0].offsetWidth + 10 : 0; // card width + gap
      const totalWidth = cardWidth * visibleCards.length;

      if (totalWidth > containerWidth) {
        // Get carousel container padding
        const carouselContainer = document.getElementById('carouselContainer');
        const carouselPaddingLeft = carouselContainer ? 
          parseInt(window.getComputedStyle(carouselContainer).paddingLeft) : 0;

        // Scroll to first visible card
        const firstVisibleCard = visibleCards[0];
        const scrollLeftValue = firstVisibleCard ? 
          firstVisibleCard.offsetLeft - carouselPaddingLeft : 0;
          
        productGridContainer.scrollLeft = scrollLeftValue > 0 ? scrollLeftValue : 0;
        productGridContainer.style.justifyContent = 'flex-start';
        productGridContainer.style.overflowX = 'auto';
      } else {
        productGridContainer.scrollLeft = 0;
        productGridContainer.style.justifyContent = 'center';
        productGridContainer.style.overflowX = 'hidden';
      }
    }, 0);
  }

  /**
   * Get width of a product card including margins
   * @returns {number} Card width in pixels
   */
  function getCardWidth() {
    const productGrid = document.getElementById('productGrid');
    if (!productGrid) return 350;
    
    const card = productGrid.querySelector('.card');
    if (!card) return 350;
    
    const style = window.getComputedStyle(card);
    const margin = parseInt(style.marginLeft) + parseInt(style.marginRight);
    const padding = parseInt(style.paddingLeft) + parseInt(style.paddingRight);
    const border = parseInt(style.borderLeftWidth) + parseInt(style.borderRightWidth);
    return card.offsetWidth + margin + padding + border;
  }

  // Add highlight animation
  const style = document.createElement('style');
  style.textContent = `
    @keyframes highlight {
      0% { background-color: rgba(0, 125, 209, 0.2); }
      100% { background-color: transparent; }
    }
  `;
  document.head.appendChild(style);

});