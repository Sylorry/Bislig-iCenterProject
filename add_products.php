<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
<title>Add Product</title>
<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: { primary: "#1a1a1a", secondary: "#404040" },
        borderRadius: {
          none: "0px",
          sm: "4px",
          DEFAULT: "8px",
          md: "12px",
          lg: "16px",
          xl: "20px",
          "2xl": "24px",
          "3xl": "32px",
          full: "9999px",
          button: "8px",
        },
      },
    },
  };
</script>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
  href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap"
  rel="stylesheet"
/>
<link
  href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
  rel="stylesheet"
/>
<link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css"
/>
<style>
  /* Reset & base */
  * {
    box-sizing: border-box;
  }
  /* Body styling */
  body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
    color: #333;
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
  }
  
  /* Heading style */
  h1 {
    font-family: 'Times New Roman', Times, serif;
    font-weight: 700;
    font-size: 2.5rem;
    margin-bottom: 30px;
    text-align: center;
    color: #000000;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    letter-spacing: 1px;
  }
  /* Form container */
  form {
    display: flex;
    flex-direction: column;
    gap: 24px;
    max-width: 1400px;
    margin: 40px auto;
    padding: 40px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }
  /* Form row container */
  .form-row {
    display: flex;
    gap: 24px;
    align-items: flex-end;
    padding: 16px;
    background: rgba(248, 250, 252, 0.6);
    border-radius: 12px;
    border: 1px solid rgba(226, 232, 240, 0.8);
  }
  
  .form-row:hover {
    background: rgba(248, 250, 252, 0.8);
  }
  
  /* Error state for form rows */
  .form-row.error {
    background: rgba(254, 242, 242, 0.8);
    border-color: #fecaca;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.1);
  }
  
  .form-row.error:hover {
    background: rgba(254, 242, 242, 0.9);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
  }
  /* Form row child div */
  .form-row > div {
    flex: 1;
    display: flex;
    flex-direction: column;
    position: relative;
  }
  /* Label styling */
  label {
    margin-left: 0;
    margin-bottom: 10px;
    font-weight: 700;
    font-size: 1.1rem;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.9rem;
  }
  /* Text, number inputs and select styling */
  input[type="text"],
  input[type="number"],
  select {
    padding: 14px 18px;
    margin: 0;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 1rem;
    background: #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    font-weight: 500;
  }
  /* Focus state for inputs and selects */
  input[type="text"]:focus,
  input[type="number"]:focus,
  select:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1), 0 4px 8px rgba(0,0,0,0.1);
  }
  /* Container for product images */
  .images-container {
    display: grid;
    grid-template-columns: repeat(8, 1fr); /* Changed to 8 columns for 8 images */
    gap: 16px;
    margin: 0;
    padding: 20px;
    background: rgba(248, 250, 252, 0.6);
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
  }
  /* Image preview styling */
  .image-preview {
    width: 110px;    /* Increased from 100% to 110px */
    height: 110px;   /* Already 110px */
    background: #ffffff;
    border-radius: 12px;
    object-fit: contain;
    border: 2px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }
  /* Hide file input */
  input[type="file"] {
    display: none;
  }
  /* Label for image upload */
  .image-label {
    position: relative;
    display: flex;
    background: linear-gradient(145deg, #f8fafc, #e2e8f0);
    padding: 10px 8px;
    border-radius: 12px;
    text-align: center;
    font-size: 0.85rem;
    color: #64748b;
    cursor: pointer;
    user-select: none;
    border: 2px solid #e2e8f0;
    width: 110px; /* Make the image field square */
    height: 110px; /* Make the image field square */
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    font-weight: 600;
  }
  /* Hover effect for image label */
  .image-label:hover {
    background: linear-gradient(145deg, #e2e8f0, #cbd5e1);
    color: #475569;
  }
  
  /* Enhanced thumbnail preview styles */
  .thumbnail-preview {
    cursor: pointer;
  }
  
  /* Image upload overlay */
  .image-label.has-image {
    background: linear-gradient(145deg, #f0f9ff, #e0f2fe);
    border-color: #0ea5e9;
    color: #0369a1;
  }
  

  
  /* Remove image button */
  .remove-image-btn {
    position: absolute;
    top: -1px;
    right: -1px;
    width: 3px;
    height: 3px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 30%;
    cursor: pointer;
    font-size: 8px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 2px rgba(239, 68, 68, 0.3);
    transition: all 0.3s ease;
    z-index: 15;
    line-height: 1;
  }
  
  .remove-image-btn:hover {
    background: #dc2626;
  }
  
  /* Image upload progress indicator */
  .upload-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: rgba(0,0,0,0.1);
    border-radius: 0 0 12px 12px;
    overflow: hidden;
  }
  
  .upload-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #059669);
    width: 0%;
  }
  
  /* Image count indicator */
  .image-count {
    position: absolute;
    top: -5px;
    left: -5px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 10px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 123, 255, 0.4);
  }
  
  /* Enhanced image upload section */
  .image-upload-section {
    margin-top: 10px;
  }
  
  .section-label {
    font-size: 1.1rem;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: block;
  }
  
  .upload-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding: 8px 12px;
    background: rgba(59, 130, 246, 0.05);
    border-radius: 8px;
    border: 1px solid rgba(59, 130, 246, 0.1);
  }
  
  .upload-count {
    font-weight: 600;
    color: #1e40af;
    font-size: 0.9rem;
  }
  
  .upload-hint {
    font-size: 0.8rem;
    color: #6b7280;
    font-style: italic;
  }
  
  .upload-icon {
    font-size: 1.5rem;
    opacity: 0.7;
  }
  
  .upload-text {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 500;
  }
  

  
  .image-label.has-image .upload-icon,
  .image-label.has-image .upload-text {
    display: none;
  }
  

  

  /* Submit button styling - more specific to override Tailwind */
  button[type="submit"] {
    background: linear-gradient(145deg, #000000, #1a1a1a) !important;
    color: white !important;
    border: none !important;
    padding: 12px 24px !important;
    font-size: 0.95rem !important;
    border-radius: 12px !important;
    cursor: pointer !important;
    font-weight: 600 !important;
    margin: 20px 0 0 0 !important;
    width: auto !important;
    max-width: 160px !important;
    font-family: 'Times New Roman', Times, serif !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    position: relative !important;
    overflow: hidden !important;
  }
  /* Hover effect for button */
  button[type="submit"]:hover {
    background: linear-gradient(145deg,rgb(4, 5, 5),rgb(4, 5, 5)) !important;
  }
  
  button:active {
  }
  /* Modal message styling - all notifications now use modals */
  .modal-content h2 {
    margin-bottom: 15px;
    font-size: 1.5rem;
    font-weight: 700;
  }
  
  .modal-content p {
    font-size: 1.1rem;
    line-height: 1.5;
    margin-bottom: 20px;
  }
  /* Scrollbar styles for mobile */
  #app::-webkit-scrollbar {
    width: 10px;
  }
  #app::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.25);
    border-radius: 5px;
  }
  /* Screen reader only */
  .sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0,0,0,0);
    border: 0;
  }
  .back-button {
    display: inline-block;
    margin-bottom: 20px;
    font-weight: 700;
    color: #007bff;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    padding: 8px 12px;
    border-radius: 10px;
    border: 2px solid #007bff;
    background: linear-gradient(145deg, #e9f0ff, #d1e7ff);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
    user-select: none;
    margin-left: 40px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .back-button:hover,
  .back-button:focus {
    color: #fff;
    background: linear-gradient(145deg, rgb(8, 8, 8), #1a1a1a);
    border-color:rgb(19, 20, 21);
    outline: none;
    text-decoration: none;
    box-shadow: 0 8px 20px rgba(6, 6, 6, 0.4);
    transform: translateY(-2px);
  }
  
  .back-dashboard-button {
    display: inline-block;
    margin-bottom: 20px;
    font-weight: 700;
    color: white;
    text-decoration: none;
    font-size: 0.85rem;
    padding: 8px 12px;
    border-radius: 10px;
    border: 2px solidrgb(7, 7, 7);
    background: linear-gradient(145deg,rgb(5, 6, 6),rgb(5, 5, 5));
    user-select: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    align-self: flex-start;
  }
  
  .back-dashboard-button:hover,
  .back-dashboard-button:focus {
    color: #fff;
    background: linear-gradient(145deg, rgb(8, 8, 8), #1a1a1a);
    border-color:rgb(13, 13, 14);
    outline: none;
    text-decoration: none;
  }
</style>
</head>
<body>
<!-- Enhanced Header -->
<header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
  <div class="flex items-center justify-between px-6 py-4">
    <div class="flex items-center space-x-6">
      <div class="ml-2 mr-10 text-sm text-white flex items-center space-x-6">
        <img src="images/iCenter.png" alt="Logo" class="h-20 w-auto border-2 border-white rounded-lg shadow-lg mr-4" />
        <div class="flex flex-col space-y-1">
          <span class="font-semibold text-lg"><?php echo date('l, F j, Y'); ?></span>
          <div class="text-white/80 text-sm">
            <i class="ri-time-line mr-2"></i>
            <span id="currentTime"></span>
          </div>
        </div>
      </div>
    </div>
    <div class="flex items-center space-x-8">
      <div class="flex items-center space-x-4">
        <div class="flex flex-col items-center group cursor-pointer">
          <div
            class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-black font-medium shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-110"
          >
            <i class="ri-user-line text-lg"></i>
          </div>
          <span class="text-white text-xs font-semibold mt-2 group-hover:text-blue-300 transition-colors duration-300">ADMIN</span>
        </div>
      </div>
    </div>
  </div>
</header>

<div id="app" role="main" aria-label="Add product form">

  <form id="product-form" aria-describedby="form-instructions" novalidate>
    <a href="admin.php" class="back-dashboard-button">&larr; Back to Dashboard</a>
    <div id="form-instructions" class="sr-only">
      Fill all required fields and upload images then press Add Product.
    </div>

    <div class="form-row">
      <div>
        <label for="category_name">Category</label>
        <select id="category_name" name="category_name" aria-label="Category">
          <option value="" disabled selected>Select category</option>
          <option value="accessories">Accessories</option>
          <option value="airpods">AirPods</option>
          <option value="android">Android</option>
          <option value="ipad">iPad</option>
          <option value="iphone">iPhone</option>
          <option value="pc set">PC Set</option>
          <option value="printer">Printer</option>
          <option value="laptop">Laptop</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div>
        <label for="category_id">Category ID*</label>
        <input type="number" id="category_id" name="category_id" required autocomplete="off" placeholder="Category ID will be auto-generated" aria-required="true" min="1" readonly style="background-color: #f8f9fa; color: #6c757d;" />
      </div>
    </div>

    <!-- New row for Product Name -->
    <div class="form-row">
      <div>
        <label for="product_name">Product Name*</label>
        <input type="text" id="product_name" name="product_name" required autocomplete="off" placeholder="Enter product name" aria-required="true" />
      </div>
      <div>
        <label for="auto_product_id">Product ID</label>
        <input type="text" id="auto_product_id" name="auto_product_id" readonly placeholder="Product ID" style="background-color: #f8f9fa; color: #6c757d;" />
      </div>
    </div>

    <div class="form-row">
      <div>
        <label for="brand">Brand*</label>
        <input type="text" id="brand" name="brand" required autocomplete="off" placeholder="Enter brand" aria-required="true" />
      </div>
      <div>
        <label for="model">Model*</label>
        <input type="text" id="model" name="model" required autocomplete="off" placeholder="Enter model" aria-required="true" />
      </div>
    </div>

    <div class="form-row">
      <div>
        <label for="storage">Storage*</label>
        <select id="storage" name="storage" required aria-required="true">
          <option value="" disabled selected>Select storage</option>
          <option value="Not Available">Not Available</option>
          <option value="2GB">2GB</option>
          <option value="4GB">4GB</option>
          <option value="8GB">8GB</option>
          <option value="16GB">16GB</option>
          <option value="32GB">32GB</option>
          <option value="64GB">64GB</option>
          <option value="128GB">128GB</option>
          <option value="256GB">256GB</option>
          <option value="512GB">512GB</option>
          <option value="1TB">1TB</option>
          <option value="2TB">2TB</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div>
        <label for="purchase_price">Purchase Price*</label>
        <input type="number" id="purchase_price" name="purchase_price" min="0" step="0.01" required placeholder="Enter purchase price" aria-required="true" />
      </div>
      <div>
        <label for="selling_price">Selling Price*</label>
        <input type="number" id="selling_price" name="selling_price" min="0" step="0.01" required placeholder="Enter selling price" aria-required="true" />
      </div>
    </div>

    <div class="form-row">
      <div>
        <label for="status">Status*</label>
        <select id="status" name="status" required aria-required="true" aria-describedby="status-desc">
          <option value="" disabled selected>Select status</option>
          <option value="OLD">OLD</option>
          <option value="NEW">NEW</option>
          <option value="CURRENT">CURRENT</option>
        </select>
        <div id="status-desc" class="sr-only">Select the current availability status of the product.</div>
      </div>

      <div>
        <label for="stock_quantity">Stock Quantity*</label>
        <input type="number" id="stock_quantity" name="stock_quantity" min="0" required placeholder="Enter stock quantity" aria-required="true" />
      </div>
    </div>

    <!-- Product Images -->
    <div class="image-upload-section">
      <label class="section-label">Product Images</label>
      <div class="upload-info">
        <span class="upload-count">0/8 images uploaded</span>
        <span class="upload-hint">Click or drag images here • Max 5MB each • JPG, PNG, GIF</span>
      </div>
      
      <div class="images-container" aria-label="Product images upload">
        <label class="image-label" for="image1" title="Upload image 1">
          <div class="upload-icon">📷</div>
          <div class="upload-text">Image 1</div>
          <input type="file" id="image1" name="image1" accept="image/*" />
          <input type="hidden" id="image_path1" name="image_path1" />
        </label>
        <label class="image-label" for="image2" title="Upload image 2">
          <div class="upload-icon">📷</div>
          <div class="upload-text">Image 2</div>
          <input type="file" id="image2" name="image2" accept="image/*" />
          <input type="hidden" id="image_path2" name="image_path2" />
        </label>
        <label class="image-label" for="image3" title="Upload image 3">
          <div class="upload-icon">📷</div>
          <div class="upload-text">Image 3</div>
          <input type="file" id="image3" name="image3" accept="image/*" />
          <input type="hidden" id="image_path3" name="image_path3" />
        </label>
        <label class="image-label" for="image4" title="Upload image 4">
          <div class="upload-icon">📷</div>
          <div class="upload-text">Image 4</div>
          <input type="file" id="image4" name="image4" accept="image/*" />
          <input type="hidden" id="image_path4" name="image_path4" />
        </label>
        <label class="image-label" for="image5" title="Upload image 5">
          <div class="upload-icon">📷</div>
          <div class="upload-text">Image 5</div>
          <input type="file" id="image5" name="image5" accept="image/*" />
          <input type="hidden" id="image_path5" name="image_path5" />
        </label>
        <label class="image-label" for="image6" title="Upload image 6">
          <div class="upload-icon">📷</div>
          <div class="upload-text">Image 6</div>
          <input type="file" id="image6" name="image6" accept="image/*" />
          <input type="hidden" id="image_path6" name="image_path6" />
        </label>
        <label class="image-label" for="image7" title="Upload image 7">
          <div class="upload-icon">📷</div>
          <div class="upload-text">Image 7</div>
          <input type="file" id="image7" name="image7" accept="image/*" />
          <input type="hidden" id="image_path7" name="image_path7" />
        </label>
        <label class="image-label" for="image8" title="Upload image 8">
          <div class="upload-icon">📷</div>
          <div class="upload-text">Image 8</div>
          <input type="file" id="image8" name="image8" accept="image/*" />
          <input type="hidden" id="image_path8" name="image_path8" />
        </label>
      </div>
      

    </div>

    <button type="submit" aria-label="Add product">Add Product</button>

  <div id="message" role="alert" aria-live="polite"></div>
  </form>
</div>

<!-- Modal for success message -->
<div id="successModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalDesc">
  <div class="modal-content">
    <div class="success-icon">
      <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
        <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
      </svg>
    </div>
    <h2 id="modalTitle">Success!</h2>
    <p id="modalDesc">Product has been successfully added to the inventory!</p>
    <button id="closeModalBtnNew" aria-label="Close success message">Close</button>
  </div>
</div>

<!-- Add this confirmation modal before the success modal -->
<div id="confirmModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle" aria-describedby="confirmModalDesc">
  <div class="modal-content">
    <h2 id="confirmModalTitle">Confirm Add Product</h2>
    <p id="confirmModalDesc">Are you sure you want to add this product?</p>
    <div class="modal-buttons">
      <button id="confirmAddBtn">Yes, Add Product</button>
      <button id="cancelAddBtn">Cancel</button>
    </div>
  </div>
</div>

<style>
  /* Modal overlay */
  .modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0s 0.3s;
  }
  /* Modal content box */
  .modal-content {
    background: linear-gradient(145deg, #ffffff, #f8fafc);
    padding: 40px 50px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    max-width: 450px;
    width: 90%;
    text-align: center;
    transform: translateY(-50px) scale(0.95);
    transition: transform 0.3s ease-out;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
  }
  /* Close button */
  #closeModalBtnNew {
    margin-top: 25px;
    background: linear-gradient(145deg, rgb(7, 7, 7), #1a1a1a);
    color: white;
    border: none;
    padding: 14px 28px;
    font-size: 1.1rem;
    border-radius: 16px;
    cursor: pointer;
    font-weight: 700;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  #closeModalBtnNew:hover {
    background: linear-gradient(145deg,rgb(0, 4, 7),rgb(0, 5, 10));
  }
  
  /* Show the modal when needed */
  .modal.show {
    opacity: 1;
    visibility: visible;
    transition: opacity 0.3s ease;
  }
  .modal.show .modal-content {
    transform: translateY(0) scale(1);
  }

  .modal-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 25px;
  }

  .modal-buttons button {
    margin: 0; /* Override generic button margin */
    border: none;
    padding: 14px 28px;
    font-size: 1.1rem;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 700;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  #confirmAddBtn {
    background: linear-gradient(145deg, #28a745, #20c997);
    color: white;
  }
  #confirmAddBtn:hover {
    background: linear-gradient(145deg, #218838, #1e7e34);
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
    transform: translateY(-2px);
  }

  #cancelAddBtn {
    background: linear-gradient(145deg, #6c757d, #495057);
    color: white;
  }
  #cancelAddBtn:hover {
    background: linear-gradient(145deg, #5a6268, #343a40);
    box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
    transform: translateY(-2px);
  }

  .success-icon {
    width: 80px;
    height: 80px;
    position: relative;
    margin: 0 auto 20px;
  }
  .success-icon .checkmark {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    display: block;
    stroke-width: 3;
    stroke: #4CAF50;
    stroke-miterlimit: 10;
    animation: scale .3s ease-in-out .9s both;
  }
  .success-icon .checkmark__circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 3;
    stroke-miterlimit: 10;
    stroke: #4CAF50;
    fill: #F8F8F8;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
  }
  .success-icon .checkmark__check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
  }
  @keyframes stroke {
    100% { stroke-dashoffset: 0; }
  }
  @keyframes scale {
    0%, 100% { transform: none; }
    50% { transform: scale3d(1.1, 1.1, 1); }
  }

</style>

<script>
  (function() {
    const form = document.getElementById('product-form');
    const message = document.getElementById('message');
    const modal = document.getElementById('successModal');
    const closeModalBtn = document.getElementById('closeModalBtnNew');
    const confirmModal = document.getElementById('confirmModal');
    const confirmAddBtn = document.getElementById('confirmAddBtn');
    const cancelAddBtn = document.getElementById('cancelAddBtn');

    let pendingSubmitEvent = null; // Store event for later submission

    /**
     * Convert a File object to a base64 string asynchronously.
     * @param {File} file - The file to convert.
     * @returns {Promise<string>} - A promise that resolves to the base64 string.
     */
    function fileToBase64(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(new Error('File reading error'));
        reader.readAsDataURL(file);
      });
    }

    /**
     * Display a message to the user in modal format.
     * @param {string} text - The message text.
     * @param {string} type - The message type: 'success' or 'error'.
     */
    function displayMessage(text, type) {
      const modalTitle = modal.querySelector('#modalTitle');
      const modalDesc = modal.querySelector('#modalDesc');
      const closeBtn = modal.querySelector('#closeModalBtnNew');
      const successIcon = modal.querySelector('.success-icon');
      
      if (modalTitle && modalDesc) {
        if (type === 'success') {
          modalTitle.textContent = 'Success!';
          modalTitle.style.color = '#155724';
          modalDesc.textContent = text;
          if (successIcon) successIcon.style.display = 'block';
          modal.classList.add('show');
        } else {
          modalTitle.textContent = 'Error!';
          modalTitle.style.color = '#721c24';
          modalDesc.textContent = text;
          if (successIcon) successIcon.style.display = 'none';
          modal.classList.add('show');
        }
      }
      
      // Clear any inline messages
      message.textContent = '';
      message.className = '';
    }

    // Close modal on button click
    closeModalBtn.addEventListener('click', function() {
      modal.classList.remove('show');
    });

    // Also close modal when clicking outside the modal content
    modal.addEventListener('click', function(event) {
      if (event.target === modal) {
        modal.classList.remove('show');
      }
    });

    // Close confirmation modal on cancel button click
    cancelAddBtn.addEventListener('click', function() {
      confirmModal.classList.remove('show');
    });

    // Close confirmation modal when clicking outside the modal content
    confirmModal.addEventListener('click', function(event) {
      if (event.target === confirmModal) {
        confirmModal.classList.remove('show');
      }
    });

    /**
     * Validate the stock quantity input.
     * @param {number} quantity - The stock quantity.
     * @returns {boolean} - True if valid, false otherwise.
     */
    function validateStockQuantity(quantity) {
      return !isNaN(quantity) && quantity >= 0;
    }

    /**
     * Check if a product ID already exists in the products array.
     * @param {Array} products - The array of existing products.
     * @param {string} productId - The product ID to check.
     * @returns {boolean} - True if duplicate exists, false otherwise.
     */
    function isDuplicateProductId(products, productId) {
      return products.some(p => p.product_id === productId);
    }

    const categorySelect = document.getElementById('category_name');
    const categoryIdInput = document.getElementById('category_id');
    const productNameInput = document.getElementById('product_name');
    const brandInput = document.getElementById('brand');
    const modelInput = document.getElementById('model');
    const storageInput = document.getElementById('storage');
    const autoProductIdInput = document.getElementById('auto_product_id');

    // Category ID mapping
    const categoryIdMap = {
      'accessories': 1,
      'airpods': 2,
      'android': 3,
      'ipad': 4,
      'iphone': 5,
      'pc set': 6,
      'printer': 7,
      'laptop': 8
    };

    // Category prefix mapping for product ID
    const categoryPrefixMap = {
      'accessories': 'ACC',
      'airpods': 'AIR',
      'android': 'AND',
      'ipad': 'IPD',
      'iphone': 'IPH',
      'pc set': 'PCS',
      'printer': 'PRT',
      'laptop': 'LAP'
    };

    // Function to generate product ID
    function generateProductId() {
      const category = categorySelect.value;
      const productName = productNameInput.value.trim();
      const brand = brandInput.value.trim();
      const model = modelInput.value.trim();
      const storage = storageInput.value;

      if (!category || !productName || !brand || !model) {
        autoProductIdInput.value = '';
        return;
      }

      // Get category prefix
      const prefix = categoryPrefixMap[category] || 'PRD';
      
      // Get storage code
      const storageCode = storage === 'Not Available' ? 'NA' : storage;
      
      // Combine parts: prefix + product + model + storage
      const productId = `${prefix}-${productName.replace(/\s+/g, '_')}-${model.replace(/\s+/g, '_')}-${storageCode}`;
      
      autoProductIdInput.value = productId;
    }

    // Event listeners for auto-generating product ID
    categorySelect.addEventListener('change', () => {
      const selectedCategory = categorySelect.value;
      
      // Auto-fill category ID
      if (selectedCategory && categoryIdMap[selectedCategory]) {
        categoryIdInput.value = categoryIdMap[selectedCategory];
      } else {
        categoryIdInput.value = '';
      }
      
      // Clear other fields
      brandInput.value = '';
      modelInput.value = '';
      storageInput.value = '';
      autoProductIdInput.value = '';
    });

    productNameInput.addEventListener('input', generateProductId);
    brandInput.addEventListener('input', generateProductId);
    modelInput.addEventListener('input', generateProductId);
    storageInput.addEventListener('change', generateProductId);

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      displayMessage('', '');

      // Clear previous error states
      document.querySelectorAll('.form-row').forEach(row => {
        row.classList.remove('error');
      });

      // Client-side validation for required fields
      const requiredFields = [
        'category_name', 'category_id', 'product_name', 'brand', 'model', 'storage', 'purchase_price', 'selling_price', 'status', 'stock_quantity'
      ];
      
      let hasErrors = false;
      for (const fieldId of requiredFields) {
        const field = document.getElementById(fieldId);
        if (!field || !field.value || field.value.trim() === '') {
          // Find the parent form-row and add error class
          const formRow = field.closest('.form-row');
          if (formRow) {
            formRow.classList.add('error');
          }
          hasErrors = true;
        }
      }
      
      if (hasErrors) {
        const fieldLabel = 'required fields';
        displayMessage(`Please fill all required fields marked in red`, 'error');
        return;
      }

      // Show confirmation modal before actually submitting
      confirmModal.classList.add('show');
      pendingSubmitEvent = e; // Save event for later
    });

    // Handle confirmation modal buttons
    confirmAddBtn.addEventListener('click', async function() {
      confirmModal.classList.remove('show');

      // --- The rest of your submit logic goes here ---
      // Gather form data
      const formData = new FormData(form);

      // Check for unique images with better validation and user feedback
      const selectedFiles = [];
      for (let i = 1; i <= 8; i++) { // <-- changed 7 to 8
        const file = formData.get('image' + i);
        if (file && file.size > 0) {
          if (selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
            displayMessage('Please select unique images. Duplicate image found: ' + file.name, 'error');
            return;
          }
          selectedFiles.push(file);
        }
      }

      const productData = {
        category_id: formData.get('category_id'),
        product_id: formData.get('auto_product_id').trim(),
        category_name: formData.get('category_name'),
        brand: formData.get('brand').trim(),
        model: formData.get('model').trim(),
        storage: formData.get('storage').trim(),
        purchase_price: Number(formData.get('purchase_price')),
        selling_price: Number(formData.get('selling_price')),
        status: formData.get('status'),
        stock_quantity: Number(formData.get('stock_quantity')),
        images: {},
        image_paths: {
          image_path1: 'images/products/' + formData.get('auto_product_id').replace(/[^a-zA-Z0-9]/g, '_') + '_1.jpg',
          image_path2: 'images/products/' + formData.get('auto_product_id').replace(/[^a-zA-Z0-9]/g, '_') + '_2.jpg',
          image_path3: 'images/products/' + formData.get('auto_product_id').replace(/[^a-zA-Z0-9]/g, '_') + '_3.jpg',
          image_path4: 'images/products/' + formData.get('auto_product_id').replace(/[^a-zA-Z0-9]/g, '_') + '_4.jpg',
          image_path5: 'images/products/' + formData.get('auto_product_id').replace(/[^a-zA-Z0-9]/g, '_') + '_5.jpg',
          image_path6: 'images/products/' + formData.get('auto_product_id').replace(/[^a-zA-Z0-9]/g, '_') + '_6.jpg',
          image_path7: 'images/products/' + formData.get('auto_product_id').replace(/[^a-zA-Z0-9]/g, '_') + '_7.jpg',
          image_path8: 'images/products/' + formData.get('auto_product_id').replace(/[^a-zA-Z0-9]/g, '_') + '_8.jpg'
        }
      };

      if (!validateStockQuantity(productData.stock_quantity)) {
        displayMessage('Please enter a valid non-negative stock quantity.', 'error');
        return;
      }

      try {
        for (let i = 1; i <= 8; i++) {
          const file = formData.get('image' + i);
          if (file && file.size > 0) {
            productData.images['image' + i] = await fileToBase64(file);
            const imagePath = formData.get('image_path' + i) || `images/products/${productData.product_id}_${i}.jpg`;
            productData.image_paths['image_path' + i] = imagePath;
          } else {
            productData.images['image' + i] = null;
          }
        }
      } catch (err) {
        displayMessage('Error reading image files.', 'error');
        return;
      }

      try {
        const response = await fetch('save_product.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(productData)
        });

        let result;
        try {
          result = await response.json();
        } catch (jsonError) {
          displayMessage('Invalid JSON response from server.', 'error');
          return;
        }

        if (response.ok && result.success) {
          displayMessage(result.message || 'Product added successfully!', 'success');
          form.reset();
          // Clear image previews on form reset
          imageInputs.forEach(input => {
            const label = input.closest('label');
            const existingImg = label.querySelector('img.thumbnail-preview');
            if (existingImg) {
              existingImg.remove();
            }
          });
          window.scrollTo({ top: 0, behavior: 'smooth' });

          // Show success modal - the HTML is now static, so we just show it.
          const modal = document.getElementById('successModal');
          if (modal) {
            const modalDesc = modal.querySelector('#modalDesc');
            if (modalDesc) {
                modalDesc.textContent = result.message || "Product has been successfully added to the inventory!";
            }
            modal.classList.add('show');
          }
        } else {
          displayMessage(result.error || 'Failed to add product.', 'error');
        }
      } catch (error) {
        displayMessage('Network error: ' + error.message, 'error');
      }
    });

    cancelAddBtn.addEventListener('click', function() {
      confirmModal.classList.remove('show');
      pendingSubmitEvent = null;
    });

    // Add thumbnail preview for image inputs
    const imageInputs = document.querySelectorAll('.images-container input[type="file"]');
    imageInputs.forEach(input => {
      input.addEventListener('change', (event) => {
        const file = event.target.files[0];
        const label = event.target.closest('label');
        if (!label) return;

        // Extract the input number from the ID (e.g., "image1" -> "1")
        const inputNumber = input.id.replace('image', '');

        // Get the corresponding hidden image path input
        const imagePathInput = document.getElementById(`image_path${inputNumber}`);

        // Get the auto-generated product ID to construct the image path
        const autoProductId = document.getElementById('auto_product_id').value;

        // Remove existing elements
        clearExistingElements(label);

        // Check if the selected file is already chosen in another input
        for (const otherInput of imageInputs) {
          if (otherInput === input) continue;
          const otherFile = otherInput.files[0];
          if (otherFile && file && otherFile.name === file.name && otherFile.size === file.size) {
            displayMessage('This image is already selected in another field. Please choose a different image.', 'error');
            input.value = ''; // Clear the current input
            return;
          }
        }

        if (file) {
          // Validate file size
          if (!validateFileSize(file)) {
            showFileSizeError(label);
            input.value = '';
            return;
          }
          
          // Add visual feedback that image is uploaded
          label.classList.add('has-image');
          
          // Hide upload icon and text
          const uploadIcon = label.querySelector('.upload-icon');
          const uploadText = label.querySelector('.upload-text');
          if (uploadIcon) uploadIcon.style.display = 'none';
          if (uploadText) uploadText.style.display = 'none';
          
          // Update the image path hidden input
          if (imagePathInput && autoProductId) {
            imagePathInput.value = `images/products/${autoProductId.replace(/[^a-zA-Z0-9]/g, '_')}_${inputNumber}.jpg`;
          }

          // Create thumbnail preview
          const img = document.createElement('img');
          img.classList.add('thumbnail-preview');
          img.style.position = 'absolute';
          img.style.top = '50%';
          img.style.left = '50%';
          img.style.transform = 'translate(-50%, -50%)';
          img.style.width = '90px';
          img.style.height = '90px';
          img.style.objectFit = 'cover';
          img.style.borderRadius = '8px';
          img.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
          img.style.border = '2px solid #ffffff';

          // Create remove button
          const removeBtn = document.createElement('button');
          removeBtn.type = 'button';
          removeBtn.className = 'remove-image-btn';
          removeBtn.innerHTML = '×';
          removeBtn.title = 'Remove image';
          
          removeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            input.value = '';
            label.classList.remove('has-image');
            img.remove();
            removeBtn.remove();
            if (imagePathInput) imagePathInput.value = '';
            updateImageCount();
          });

          // Create upload progress indicator
          const progressContainer = document.createElement('div');
          progressContainer.className = 'upload-progress';
          const progressBar = document.createElement('div');
          progressBar.className = 'upload-progress-bar';
          progressContainer.appendChild(progressBar);

          const reader = new FileReader();
          reader.onload = function(e) {
            img.src = e.target.result;
            label.appendChild(img);
            label.appendChild(removeBtn);
            label.appendChild(progressContainer);
            
            // Simulate upload progress
            let progress = 0;
            const progressInterval = setInterval(() => {
              progress += 10;
              progressBar.style.width = progress + '%';
              if (progress >= 100) {
                clearInterval(progressInterval);
                setTimeout(() => {
                  progressContainer.remove();
                  updateImageCount();
                }, 500);
              }
            }, 50);
          };
          reader.readAsDataURL(file);
        }
      });
    });

    // Handle clicks on remove buttons and thumbnails to prevent label click
    imageInputs.forEach(input => {
      const label = input.closest('label');
      if (label) {
        // Add event listeners to prevent label click when clicking on remove button or thumbnail
        label.addEventListener('click', (e) => {
          if (e.target.classList.contains('remove-image-btn') || 
              e.target.classList.contains('thumbnail-preview')) {
            e.preventDefault();
            e.stopPropagation();
          }
        });
      }
    });

    // Gradient constants for drag and drop
    const GRADIENTS = {
      dragover: 'linear-gradient(145deg, #dbeafe, #bfdbfe)',
      hasImage: 'linear-gradient(145deg, #f0f9ff, #e0f2fe)',
      default: 'linear-gradient(145deg, #f8fafc, #e2e8f0)'
    };
    
    const BORDER_COLORS = {
      dragover: '#3b82f6',
      hasImage: '#0ea5e9',
      default: '#e2e8f0'
    };
    
    // Add drag and drop functionality
    imageInputs.forEach(input => {
      const label = input.closest('label');
      if (!label) return;

      label.addEventListener('dragover', (e) => {
        e.preventDefault();
        label.style.background = GRADIENTS.dragover;
        label.style.borderColor = BORDER_COLORS.dragover;
      });

      label.addEventListener('dragleave', (e) => {
        e.preventDefault();
        if (label.classList.contains('has-image')) {
          label.style.background = GRADIENTS.hasImage;
          label.style.borderColor = BORDER_COLORS.hasImage;
        } else {
          label.style.background = GRADIENTS.default;
          label.style.borderColor = BORDER_COLORS.default;
        }
      });

      label.addEventListener('drop', (e) => {
        e.preventDefault();
        const files = e.dataTransfer.files;
        if (files.length > 0) {
          input.files = files;
          input.dispatchEvent(new Event('change'));
        }
        
        if (label.classList.contains('has-image')) {
          label.style.background = GRADIENTS.hasImage;
          label.style.borderColor = BORDER_COLORS.hasImage;
        } else {
          label.style.background = GRADIENTS.default;
          label.style.borderColor = BORDER_COLORS.default;
        }
      });
    });

    // Function to update image count
    function updateImageCount() {
      const uploadedImages = document.querySelectorAll('.image-label.has-image').length;
      const totalImages = imageInputs.length;
      
      // Update the upload count display
      const uploadCountElement = document.querySelector('.upload-count');
      if (uploadCountElement) {
        uploadCountElement.textContent = `${uploadedImages}/${totalImages} images uploaded`;
      }
    }
    
    // Function to validate file size (5MB limit)
    function validateFileSize(file) {
      const maxSize = 5 * 1024 * 1024; // 5MB
      if (file.size > maxSize) {
        return false;
      }
      return true;
    }
    
    // Function to show file size error
    function showFileSizeError() {
      displayMessage('File too large. Maximum file size is 5MB per image.', 'error');
    }
    
    // Helper function to clear existing elements from a label
    function clearExistingElements(label) {
      const existingImg = label.querySelector('img.thumbnail-preview');
      const existingRemoveBtn = label.querySelector('.remove-image-btn');
      const existingProgress = label.querySelector('.upload-progress');
      
      if (existingImg) existingImg.remove();
      if (existingRemoveBtn) existingRemoveBtn.remove();
      if (existingProgress) existingProgress.remove();
    }
    
    // Helper function to show upload icon and text
    function showUploadElements(label) {
      const uploadIcon = label.querySelector('.upload-icon');
      const uploadText = label.querySelector('.upload-text');
      if (uploadIcon) uploadIcon.style.display = 'block';
      if (uploadText) uploadText.style.display = 'block';
    }
    


    // Update count when images are added/removed
    const observer = new MutationObserver(updateImageCount);
    observer.observe(document.querySelector('.images-container'), {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['class']
    });

    // Initial count update
    updateImageCount();
    
    // Real-time validation for required fields
    const requiredFields = [
      'category_name', 'category_id', 'product_name', 'brand', 'model', 'storage', 'purchase_price', 'selling_price', 'status', 'stock_quantity'
    ];
    
    requiredFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) {
        // Validate on blur (when user leaves the field)
        field.addEventListener('blur', function() {
          const formRow = this.closest('.form-row');
          if (formRow) {
            if (!this.value || this.value.trim() === '') {
              formRow.classList.add('error');
            } else {
              formRow.classList.remove('error');
            }
          }
        });
        
        // Validate on input (as user types)
        field.addEventListener('input', function() {
          const formRow = this.closest('.form-row');
          if (formRow) {
            if (this.value && this.value.trim() !== '') {
              formRow.classList.remove('error');
            }
          }
        });
      }
    });
    
    // Bulk upload functionality
    const bulkUploadBtn = document.getElementById('bulk-upload-btn');
    if (bulkUploadBtn) {
      bulkUploadBtn.addEventListener('click', () => {
        // Create a temporary file input for multiple files
        const tempInput = document.createElement('input');
        tempInput.type = 'file';
        tempInput.multiple = true;
        tempInput.accept = 'image/*';
        
        tempInput.addEventListener('change', (e) => {
          const files = Array.from(e.target.files);
          let fileIndex = 0;
          
          files.forEach((file, index) => {
            if (fileIndex < imageInputs.length) {
              // Find the next empty input
              while (fileIndex < imageInputs.length && imageInputs[fileIndex].files.length > 0) {
                fileIndex++;
              }
              
              if (fileIndex < imageInputs.length) {
                // Set the file and trigger change event
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                imageInputs[fileIndex].files = dataTransfer.files;
                imageInputs[fileIndex].dispatchEvent(new Event('change'));
                fileIndex++;
              }
            }
          });
        });
        
        tempInput.click();
      });
    }
    


  })();

  // Update current time in header
  function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { 
      hour12: true, 
      hour: '2-digit', 
      minute: '2-digit', 
      second: '2-digit' 
    });
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
      timeElement.textContent = timeString;
    }
  }

  // Update time every second
  setInterval(updateCurrentTime, 1000);
  updateCurrentTime(); // Initial call
</script>
</body>
</html>