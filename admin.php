<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bislig iCenter - Admin Dashboard</title>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <style>
      :where([class^="ri-"])::before { content: "\f3c2"; }
      body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
      }
      
      /* Enhanced Card Hover Effects */
      .card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      }
      
      /* Enhanced Product Card */
      .product-card {
        min-height: 280px;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        background: white;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        border: 2px solid #e5e7eb;
      }
      .product-card p,
      .product-card a {
        margin-bottom: 0.25rem;
      }
      
      /* Enhanced Sidebar */
      #sidebar {
        background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
        box-shadow: 8px 0 25px rgba(0, 0, 0, 0.15);
        border-right: 1px solid rgba(255, 255, 255, 0.1);
      }
      #sidebar a {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px;
        margin: 6px 0;
        position: relative;
        overflow: hidden;
      }
      #sidebar a::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transition: left 0.5s ease;
      }
      #sidebar a:hover::before {
        left: 100%;
      }
      #sidebar a:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateX(8px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      }
      #sidebar a.active {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.3), rgba(99, 102, 241, 0.2));
        color: white;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
      }
      
      /* Enhanced Header */
      header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
      }
      
      /* Enhanced Stats Cards */
      .bg-\[\#1a1a1a\] {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
        overflow: hidden;
      }
      .bg-\[\#1a1a1a\]:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      }
      
      /* Enhanced Button Styles */
      .rounded-button {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px;
        position: relative;
        overflow: hidden;
      }
      .rounded-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
      }
      .rounded-button:hover::before {
        left: 100%;
      }
      .rounded-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
      }
      
      /* Enhanced Status Badge Styles */
      .status-badge {
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-block;
        margin: 8px 0;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
      }
      .status-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
      }
      .status-badge:hover::before {
        transform: translateX(100%);
      }
      .status-badge.in-stock {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(34, 197, 94, 0.1));
        color: #22c55e;
        font-size: 1.0rem;
        font-weight: 700;
        border: 2px solid rgba(34, 197, 94, 0.3);
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.2);
      }
      .status-badge.low-stock {
        background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(234, 179, 8, 0.1));
        color: #eab308;
        border: 2px solid rgba(234, 179, 8, 0.3);
        box-shadow: 0 4px 15px rgba(234, 179, 8, 0.2);
      }
      .status-badge.out-of-stock {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(239, 68, 68, 0.1));
        color: #ef4444;
        font-size: 1.0rem;
        font-weight: 700;
        border: 2px solid rgba(239, 68, 68, 0.3);
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
      }
      
      /* Enhanced Pagination Styles */
      #paginationControls a {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px;
        position: relative;
        overflow: hidden;
      }
      #paginationControls a::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1), transparent);
        transition: left 0.5s ease;
      }
      #paginationControls a:hover::before {
        left: 100%;
      }
      #paginationControls a:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      }
      
      /* Enhanced Category Buttons */
      #categoryButtons a {
        border: 2px solid transparent;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        -webkit-tap-highlight-color: transparent;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }
      #categoryButtons a::before {
        display: none;
      }
      #categoryButtons a:hover {
        border-color:rgb(2, 1, 17);
      }
      #categoryButtons a.bg-blue-600 {
        background: linear-gradient(135deg,rgb(1, 1, 15) 0%,rgb(1, 1, 21) 100%) !important;
        border-color:rgb(2, 1, 16) !important;
        color: #fff !important;
      }
      
      /* Enhanced Form Elements */
      input[type="number"]::-webkit-inner-spin-button,
      input[type="number"]::-webkit-outer-spin-button {
      -webkit-appearance: none;
      margin: 0;
      }
      
      /* Enhanced Custom Checkbox */
      .custom-checkbox {
      position: relative;
      display: inline-block;
        width: 22px;
        height: 22px;
      cursor: pointer;
      }
      .custom-checkbox input {
      opacity: 0;
      width: 0;
      height: 0;
      }
      .checkmark {
      position: absolute;
      top: 0;
      left: 0;
        width: 22px;
        height: 22px;
      background-color: #fff;
      border: 2px solid #d1d5db;
        border-radius: 6px;
        transition: all 0.3s ease;
      }
      .custom-checkbox input:checked ~ .checkmark {
        background: linear-gradient(135deg,rgb(12, 12, 12),rgb(12, 12, 12));
      border-color:rgb(12, 12, 13);
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
      }
      .checkmark:after {
      content: "";
      position: absolute;
      display: none;
      }
      .custom-checkbox input:checked ~ .checkmark:after {
      display: block;
      }
      .custom-checkbox .checkmark:after {
        left: 8px;
        top: 4px;
      width: 6px;
      height: 10px;
      border: solid white;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
      }
      
      /* Enhanced Custom Switch */
      .custom-switch {
      position: relative;
      display: inline-block;
        width: 48px;
        height: 26px;
      }
      .custom-switch input {
      opacity: 0;
      width: 0;
      height: 0;
      }
      .switch-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #e5e7eb;
      transition: .4s;
      border-radius: 34px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .switch-slider:before {
      position: absolute;
      content: "";
        height: 20px;
        width: 20px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
      }
      .custom-switch input:checked + .switch-slider {
        background: linear-gradient(135deg,rgb(0, 0, 0),rgb(10, 10, 11));
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .custom-switch input:checked + .switch-slider:before {
        transform: translateX(22px);
      }
      
      /* Enhanced Custom Range */
      .custom-range {
      width: 100%;
        height: 8px;
        border-radius: 6px;
        background: linear-gradient(90deg, #e5e7eb 0%, #d1d5db 100%);
      outline: none;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .custom-range::-webkit-slider-thumb {
      appearance: none;
        width: 22px;
        height: 22px;
      border-radius: 50%;
        background: linear-gradient(135deg,rgb(9, 9, 9),rgb(8, 8, 8));
      cursor: pointer;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        border: 2px solid white;
      }
      .custom-range::-moz-range-thumb {
        width: 22px;
        height: 22px;
      border-radius: 50%;
        background: linear-gradient(135deg,rgb(13, 13, 13),rgb(12, 12, 13));
      cursor: pointer;
      border: none;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
      }
      
      /* Enhanced Dropdown */
      .dropdown-content {
      display: none;
      position: absolute;
      background-color: white;
        min-width: 180px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      z-index: 1;
        border-radius: 12px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
      }
      .dropdown-content a {
      color: black;
        padding: 14px 18px;
      text-decoration: none;
      display: block;
        transition: all 0.3s ease;
        border-radius: 8px;
        margin: 2px;
      }
      .dropdown-content a:hover {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        transform: translateX(4px);
      }
      .show {
      display: block;
        animation: fadeInUp 0.3s ease;
      }
      
      /* Enhanced Product Card Images */
      .product-card img {
        border: none !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      }
      
      /* Enhanced Animations */
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
        }
        to {
          opacity: 1;
        }
      }

      @keyframes slideInRight {
        from {
          opacity: 0;
          transform: translateX(30px);
        }
        to {
          opacity: 1;
          transform: translateX(0);
        }
      }

      @keyframes pulse {
        0%, 100% {
          opacity: 1;
        }
        50% {
          opacity: 0.5;
        }
      }

      .animate-fadeIn {
        animation: fadeIn 0.3s ease-in-out;
      }

      .animate-slideInRight {
        animation: slideInRight 0.4s ease-out;
      }

      .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
      }
      
      /* Enhanced Scrollbar */
      ::-webkit-scrollbar {
        width: 8px;
      }
      ::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
      }
      ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg,rgba(128, 128, 130, 0.93),rgb(127, 127, 130));
        border-radius: 4px;
      }
      ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg,rgb(135, 135, 137),rgb(129, 129, 129));
      }
      
      /* Enhanced Focus States */
      *:focus {
        outline: 2px solidrgb(2, 1, 20);
        outline-offset: 2px;
      }
      
      /* Enhanced Text Selection */
      ::selection {
        background: rgba(79, 70, 229, 0.2);
        color: #1a1a1a;
      }
      
      #categoryButtons a:hover:not(.bg-blue-600) {
        background: #000 !important;
        color: #fff !important;
      }
      
      #categoryButtons a.bg-blue-600:hover {
        background: linear-gradient(135deg,rgb(1, 1, 15) 0%,rgb(1, 1, 21) 100%) !important;
        color: #fff !important;
      }
      
      #categoryButtons a:active {
        /* No animation effects */
      }
      
      #categoryButtons a.bg-blue-600:active {
        background: linear-gradient(135deg,rgb(1, 1, 15) 0%,rgb(1, 1, 21) 100%) !important;
        color: #fff !important;
      }
      
      /* Add transition for sidebar toggle button */
      #sidebarToggle {
        background: #f3f4f6; /* light gray */
        border: 2px solid #e5e7eb;
        color: #1a1a1a;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        top: 3rem; /* Move the button a little higher */
      }
      #sidebarToggle.sidebar-closed {
        background: #f3f4f6;
        border: 2px solid #e5e7eb;
        color: #1a1a1a;
      }
    </style>
  </head>
      <body class="min-h-screen">
        <div class="flex">
      <!-- Enhanced Sidebar -->
          <div
            id="sidebar"
            class="w-64 h-screen fixed shadow-md flex flex-col z-10 transition-all duration-300"
        style="background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);"
          >
        <div class="p-6 flex items-center justify-center relative">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent opacity-0 hover:opacity-100 transition-opacity duration-300"></div>
</div>
            <div class="flex-1 overflow-y-auto">
              <nav class="px-4 py-2">
            <div class="space-y-2">
                  <a
                    href="add_products.php"
                class="flex items-center px-4 py-4 text-base font-medium rounded-xl text-gray-300 hover:bg-white/10 hover:border hover:border-white/20 transition-all duration-300 group"
                  >
                <div class="w-8 h-8 flex items-center justify-center mr-4 bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-lg group-hover:from-blue-500/30 group-hover:to-purple-500/30 transition-all duration-300">
                  <i class="ri-add-line text-lg"></i>
                    </div>
                <span class="group-hover:translate-x-1 transition-transform duration-300">Add Product</span>
                  </a>
                  <a
                    href="view_products.php"
                class="flex items-center px-4 py-4 text-base font-medium rounded-xl text-gray-300 hover:bg-white/10 hover:border hover:border-white/20 transition-all duration-300 group">
                <div class="w-8 h-8 flex items-center justify-center mr-4 bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-lg group-hover:from-green-500/30 group-hover:to-emerald-500/30 transition-all duration-300">
                  <i class="ri-grid-line text-lg"></i>
                    </div>
                <span class="group-hover:translate-x-1 transition-transform duration-300">View Products</span>
                  </a>
                  <a
                    href="inventory_stocks.php"
                class="flex items-center px-4 py-4 text-base font-medium rounded-xl text-gray-300 hover:bg-white/10 hover:border hover:border-white/20 transition-all duration-300 group">
                <div class="w-8 h-8 flex items-center justify-center mr-4 bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-lg group-hover:from-orange-500/30 group-hover:to-red-500/30 transition-all duration-300">
                  <i class="ri-stack-line text-lg"></i>
                    </div>
                <span class="group-hover:translate-x-1 transition-transform duration-300">Inventory Stocks</span>
                  </a>
                  <a
                    href="reserved.php"
                class="flex items-center px-4 py-4 text-base font-medium rounded-xl text-gray-300 hover:bg-white/10 hover:border hover:border-white/20 transition-all duration-300 group">
                <div class="w-8 h-8 flex items-center justify-center mr-4 bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-lg group-hover:from-purple-500/30 group-hover:to-pink-500/30 transition-all duration-300">
                  <i class="ri-calendar-line text-lg"></i>
                    </div>
                <span class="group-hover:translate-x-1 transition-transform duration-300">Reservations</span>
                  </a>
            </div>
              </nav>
            </div>
        <div class="p-4 border-t border-white/10">
              <button id="logoutButton" type="button"
            class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-red-600 rounded-xl hover:from-red-600 hover:to-red-700 whitespace-nowrap transition-all duration-300 group shadow-lg hover:shadow-xl">
            <div class="w-6 h-6 flex items-center justify-center mr-3 bg-white/20 rounded-lg group-hover:bg-white/30 transition-all duration-300">
              <i class="ri-logout-box-line text-lg"></i>
                </div>
            <span class="group-hover:translate-x-1 transition-transform duration-300">Log Out</span>
              </button>

                        <!-- Enhanced Logout Confirmation Modal -->
              <div id="logoutModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center hidden z-50">
                <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
                  <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                      <i class="ri-logout-box-line text-2xl text-white"></i>
                  </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Confirm Logout</h2>
                    <p class="text-gray-600 mb-8">Are you sure you want to log out of your account?</p>
                    <div class="flex space-x-4">
                      <button id="cancelLogout" class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-300 font-semibold">Cancel</button>
                      <a href="logout.php" id="confirmLogout" class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-300 font-semibold shadow-lg hover:shadow-xl">Logout</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Sidebar Toggle Button -->
          <button id="sidebarToggle" class="fixed left-6 z-50 text-white bg-[#1a1a1a] border-2 border-white p-1 rounded-md shadow-md focus:outline-none">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M4 6h16M4 12h16M4 18h16"
              />
            </svg>
          </button>
      
          <!-- Main content -->
          <div id="mainContent" class="flex-1 ml-64 transition-all duration-300">
        <!-- Enhanced Header -->
        <header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
          <div class="flex justify-between items-center px-8 py-6 space-x-4">
            <div class="flex items-center space-x-6">
              <div class="ml-12 mr-10 text-sm text-white flex items-center space-x-6">
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
         </header>
        
        <!-- Dashboard content -->
        <div class="p-8">
          
          
          <!-- Enhanced Product inventory section -->
          <div class="mb-10">
            <div class="flex flex-wrap gap-3 mb-8 justify-center" id="categoryButtons">
            <?php
            try {
                $stmt = $conn->query("SELECT DISTINCT product FROM products ORDER BY product ASC");
                $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                $categories = [];
            }
            $currentCategory = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
            $currentModelBrand = isset($_GET['model_brand']) ? strtolower(trim($_GET['model_brand'])) : 'all models';

            // Add "All Products" button
            $activeClass = (empty($currentCategory))
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-800 hover:bg-black hover:text-white';
            $urlParams = [];
            if (isset($_GET['search']) && $_GET['search'] !== '') {
                $urlParams['search'] = $_GET['search'];
            }
            if ($currentModelBrand !== 'all models') {
                $urlParams['model_brand'] = $currentModelBrand;
            }
            $urlParams['page'] = 1;
            $queryString = http_build_query($urlParams);
            echo '<a href="?' . $queryString . '" class="px-4 py-2 rounded-xl border border-transparent cursor-pointer ' . $activeClass . '" onclick="handleCategoryClick(event)">All Products</a>';

            foreach ($categories as $cat) {
                $catLower = strtolower($cat);
                $activeClass = ($catLower === $currentCategory)
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200 text-gray-800 hover:bg-black hover:text-white';
                $urlParams = [];
                $urlParams['category'] = $catLower;
                if (isset($_GET['search']) && $_GET['search'] !== '') {
                    $urlParams['search'] = $_GET['search'];
                }
                if ($currentModelBrand !== 'all models') {
                    $urlParams['model_brand'] = $currentModelBrand;
                }
                $urlParams['page'] = 1;
                $queryString = http_build_query($urlParams);
                echo '<a href="?' . $queryString . '" class="px-4 py-2 rounded-xl border border-transparent cursor-pointer ' . $activeClass . '" onclick="handleCategoryClick(event)">' . htmlspecialchars($cat) . '</a>';
            }
            ?>
          </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" id="productGrid">
              <?php
              // Helper function to get stock status
              function getStockStatus($stockQuantity) {
                  $stockQuantity = (int)$stockQuantity;
                  if ($stockQuantity == 0) {
                      return ['status' => 'out-of-stock', 'text' => 'Out of Stock'];
                  } elseif ($stockQuantity > 0 && $stockQuantity <= 5) {
                      return ['status' => 'low-stock', 'text' => 'Low Stock'];
                  } else {
                      return ['status' => 'in-stock', 'text' => 'In Stock'];
                  }
              }

              // Enhanced helper function to render product card
              function renderProductCard($product) {
                  $stockInfo = getStockStatus($product['stock_quantity']);
                  $categoryLower = strtolower($product['brand']);
                  $mainImage = $product['image1'] ?? '';
                  $storageDisplay = !empty($product['storage']) ? htmlspecialchars($product['storage']) : 'Not Available';
                  
                  echo '<div class="product-card text-center border-2 border-gray-200 rounded-2xl relative" data-category="' . htmlspecialchars($categoryLower) . '">';
                  
                  // Enhanced image container - now on the left side
                  if (!empty($mainImage)) {
                      echo '<div class="relative w-2/5 overflow-hidden bg-gray-100">';
                      echo '<img src="' . htmlspecialchars($mainImage) . '" alt="Main Product Image" class="w-full h-full object-cover" />';
                      echo '<div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>';
                      echo '</div>';
                  }
                  
                  echo '<div class="flex-1 p-6 flex flex-col justify-between">';
                  echo '<div class="mt-8">';
                  echo '<h3 class="font-bold text-2xl mb-3 text-gray-800 group-hover:text-blue-600 transition-colors duration-300">' . htmlspecialchars($product['brand'] . ' ' . $product['model']) . '</h3>';
                  echo '<p class="font-semibold text-blue-600 text-xl mb-4">' . htmlspecialchars($product['brand']) . '</p>';
                  
                  echo '<div class="mt-4 mb-4 text-gray-700 text-base flex justify-center gap-x-6">';
                  echo '<span><span class="font-medium">Model: </span>' . htmlspecialchars($product['model']) . '</span>';
                  echo '<span><span class="font-medium">Storage: </span>' . $storageDisplay . '</span>';
                  echo '</div>';
                  
                  echo '<div class="grid grid-cols-2 gap-3 text-gray-600 mb-6">';
                  echo '<div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">';
                  echo '<span class="font-medium">Purchase:</span>';
                  echo '<span class="text-gray-800">₱' . number_format($product['purchase_price'], 2) . '</span>';
                  echo '</div>';
                  echo '<div class="flex justify-between items-center py-2 px-3 bg-red-50 rounded-lg border border-red-200">';
                  echo '<span class="font-medium text-red-700">Selling:</span>';
                  echo '<span class="text-red-700 font-semibold">₱' . number_format($product['selling_price'], 2) . '</span>';
                  echo '</div>';
                  echo '</div>';
                  echo '</div>';
                  
                  echo '<div class="space-y-4">';
                  echo '<p class="status-badge ' . $stockInfo['status'] . ' w-full block text-center px-6 py-3 rounded-xl font-semibold">' . $stockInfo['text'] . ' (' . $product['stock_quantity'] . ')</p>';
                  echo '<a href="edit_products.php?product_id=' . urlencode($product['product_id']) . '" class="inline-block w-full px-6 py-3 bg-black text-white rounded-xl hover:bg-gray-900 transition-all duration-300 transform hover:scale-105 font-semibold shadow-lg hover:shadow-xl">Edit Product</a>';
                  echo '</div>';
                  echo '</div>';
                  echo '</div>';
              }

              // Helper function to render pagination
              function renderPagination($totalPages, $page, $search, $category) {
                  if ($totalPages <= 1) return;
                  
                  echo '<div class="col-span-full flex justify-center mt-6 space-x-2" id="paginationControls">';
                  $urlParams = [];
                  if ($search !== '') {
                      $urlParams['search'] = $search;
                  }
                  if ($category !== '') {
                      $urlParams['category'] = $category;
                  }
                  
                  // Previous page link
                  if ($page > 1) {
                      $urlParams['page'] = $page - 1;
                      echo '<a href="?' . http_build_query($urlParams) . '" class="px-4 py-2 rounded-full border border-black bg-white text-black font-semibold transition-colors duration-200 hover:bg-gray-200">Previous</a>';
                  } else {
                      echo '<span class="px-4 py-2 rounded-full border border-gray-300 bg-gray-100 text-gray-400 font-semibold cursor-not-allowed">Previous</span>';
                  }
                  
                  // Page number links
                  for ($i = 1; $i <= $totalPages; $i++) {
                      if ($i == $page) {
                          echo '<span class="px-4 py-2 rounded-full bg-black text-white font-semibold border border-black">' . $i . '</span>';
              } else {
                          $urlParams['page'] = $i;
                          echo '<a href="?' . http_build_query($urlParams) . '" class="px-4 py-2 rounded-full border border-black bg-white text-black font-semibold transition-colors duration-200 hover:bg-gray-200">' . $i . '</a>';
                      }
                  }
                  
                  // Next page link
                  if ($page < $totalPages) {
                      $urlParams['page'] = $page + 1;
                      echo '<a href="?' . http_build_query($urlParams) . '" class="px-4 py-2 rounded-full border border-black bg-white text-black font-semibold transition-colors duration-200 hover:bg-gray-200">Next</a>';
                  } else {
                      echo '<span class="px-4 py-2 rounded-full border border-gray-300 bg-gray-100 text-gray-400 font-semibold cursor-not-allowed">Next</span>';
                  }
                  echo '</div>';
              }

              $limit = 12;
                $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                try {
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                $category = isset($_GET['category']) ? trim($_GET['category']) : '';
                $ajax = isset($_GET['ajax']) ? true : false;

                $whereClauses = [];
                $params = [];

                // Exclude archived products
                $whereClauses[] = "(archived IS NULL OR archived = 0)";

                if ($search !== '') {
                    $whereClauses[] = "(model LIKE :search OR brand LIKE :search OR storage LIKE :search)";
                    $params[':search'] = '%' . $search . '%';
                }

                                  if ($category !== '') {
                      $whereClauses[] = "TRIM(LOWER(product)) = :category";
                      $params[':category'] = strtolower($category);
                  }

                $whereSQL = '';
                if (count($whereClauses) > 0) {
                    $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
                }

                // Count total products for pagination
                $countSQL = "SELECT COUNT(*) FROM products $whereSQL";
                $countStmt = $conn->prepare($countSQL);
                foreach ($params as $key => $val) {
                    $countStmt->bindValue($key, $val, PDO::PARAM_STR);
                }
                $countStmt->execute();
                $totalProducts = $countStmt->fetchColumn();

                // Fetch products with limit and offset
                $sql = "SELECT * FROM products $whereSQL ORDER BY product_id ASC LIMIT :limit OFFSET :offset";
                $stmt = $conn->prepare($sql);
                foreach ($params as $key => $val) {
                    $stmt->bindValue($key, $val, PDO::PARAM_STR);
                }
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();

                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($ajax) {
                        // If AJAX request, return only product grid and pagination HTML
                        ob_start();
                        if (count($products) === 0) {
                            echo '<div class="col-span-full text-center py-8">
                                    <p class="text-gray-500">No products found in the database.</p>
                                  </div>';
                      } else {
                        foreach ($products as $product) {
                              renderProductCard($product);
                          }
                      }
                      
                      renderPagination(ceil($totalProducts / $limit), $page, $search, $category);
                      
                        $output = ob_get_clean();
                        echo $output;
                        exit;
                    }

                    if (count($products) === 0) {
                        echo '<div class="col-span-full text-center py-8">
                                <p class="text-gray-500">No products found in the database.</p>
                              </div>';
                  } else {
                    foreach ($products as $product) {
                          renderProductCard($product);
                      }
                  }

                  renderPagination(ceil($totalProducts / $limit), $page, $search, $category);
                  
                } catch (PDOException $e) {
                    echo '<div class="col-span-full text-center py-8">
                            <p class="text-red-500">Error loading products: ' . htmlspecialchars($e->getMessage()) . '</p>
                          </div>';
                }
                ?>
          </div>
          </div>
        </div>
      </div>
    </div>

          <script>
      // Enhanced logout modal functionality
      const logoutButton = document.getElementById('logoutButton');
      const logoutModal = document.getElementById('logoutModal');
      const cancelLogout = document.getElementById('cancelLogout');

      logoutButton.addEventListener('click', () => {
        logoutModal.classList.remove('hidden');
        setTimeout(() => {
          const modalContent = document.getElementById('modalContent');
          modalContent.style.transform = 'scale(1)';
          modalContent.style.opacity = '1';
        }, 10);
      });

      function hideModal() {
        const modalContent = document.getElementById('modalContent');
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0';
        setTimeout(() => {
          logoutModal.classList.add('hidden');
        }, 300);
      }

      cancelLogout.addEventListener('click', hideModal);

      logoutModal.addEventListener('click', (e) => {
        if (e.target === logoutModal) {
          hideModal();
        }
      });

      // Current time display
      function updateTime() {
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
      setInterval(updateTime, 1000);
      updateTime(); // Initial call

          // Sidebar toggle functionality
          const sidebarToggle = document.getElementById("sidebarToggle");
          const sidebar = document.getElementById("sidebar");
          const mainContent = document.getElementById("mainContent");

          sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("-ml-64");
            const closed = sidebar.classList.contains("-ml-64");
            if (closed) {
              mainContent.classList.remove("ml-64");
              mainContent.classList.add("ml-0");
              sidebarToggle.style.left = "1.5rem";
              sidebarToggle.classList.add("sidebar-closed");
            } else {
              mainContent.classList.remove("ml-0");
              mainContent.classList.add("ml-64");
              sidebarToggle.style.left = "16rem";
              sidebarToggle.classList.remove("sidebar-closed");
            }
          });

      // Category click handler
      function handleCategoryClick(event) {
        event.preventDefault();
        const url = event.currentTarget.href;
        const categoryButtons = document.getElementById('categoryButtons');
        const productGridContainer = document.getElementById('productGrid');

        categoryButtons.style.transition = 'opacity 0.3s ease';
        categoryButtons.style.opacity = '0.5';
        productGridContainer.style.transition = 'opacity 0.3s ease';
        productGridContainer.style.opacity = '0.5';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(response => response.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newProductGrid = doc.getElementById('productGrid');
            if (newProductGrid && productGridContainer) {
              productGridContainer.innerHTML = newProductGrid.innerHTML;
              productGridContainer.style.opacity = '1';
            }

            const oldPagination = document.querySelector('#paginationControls');
            const newPagination = doc.querySelector('#paginationControls');
            if (oldPagination && newPagination) {
              oldPagination.innerHTML = newPagination.innerHTML;
            }

            const newCategoryButtons = doc.getElementById('categoryButtons');
            if (newCategoryButtons && categoryButtons) {
              categoryButtons.innerHTML = newCategoryButtons.innerHTML;
            }

            attachPaginationHandlers();
            attachCategoryButtonHandlers();
            categoryButtons.style.opacity = '1';
          })
          .catch(err => {
            console.error('Error loading category:', err);
            categoryButtons.style.opacity = '1';
            productGridContainer.style.opacity = '1';
          });
      }

      // Search handler
      function handleSearch(event) {
        event.preventDefault();
        const searchInput = document.getElementById('searchInput');
        const searchTerm = searchInput.value.trim();
        const urlParams = new URLSearchParams(window.location.search);

        if (searchTerm) {
          urlParams.set('search', searchTerm);
        } else {
          urlParams.delete('search');
        }
        urlParams.set('page', 1);

        window.location.href = window.location.pathname + '?' + urlParams.toString();
        return false;
      }

      // AJAX pagination functionality
          document.addEventListener('DOMContentLoaded', () => {
            const productGridContainer = document.getElementById('productGrid');
            const categoryButtons = document.getElementById('categoryButtons');

function fetchPage(url, pushState = true) {
  if (!productGridContainer) return;
  productGridContainer.style.transition = 'opacity 0.1s ease';
  productGridContainer.style.opacity = '0';

  fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(response => response.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');

      const newProductGrid = doc.getElementById('productGrid');
              const newPagination = doc.querySelector('#paginationControls');

      if (newProductGrid && productGridContainer.parentNode) {
        setTimeout(() => {
          productGridContainer.innerHTML = newProductGrid.innerHTML;
          productGridContainer.style.transition = 'opacity 0.1s ease';
          productGridContainer.style.opacity = '1';
        }, 100);
      }

              const oldPagination = document.querySelector('#paginationControls');
      if (oldPagination && newPagination) {
        oldPagination.innerHTML = newPagination.innerHTML;
      }

      attachPaginationHandlers();
      if (pushState) {
        history.pushState(null, '', url);
      }
    })
    .catch(err => {
      console.error('Error loading page:', err);
      productGridContainer.style.opacity = '1';
    });
}

            function attachPaginationHandlers() {
              const paginationLinks = document.querySelectorAll('#paginationControls a');
              paginationLinks.forEach(link => {
                link.addEventListener('click', e => {
                  e.preventDefault();
              fetchPage(link.href);
                });
              });
            }

            function attachCategoryButtonHandlers() {
              if (!categoryButtons) return;
              const categoryLinks = categoryButtons.querySelectorAll('a');
              categoryLinks.forEach(link => {
                link.removeEventListener('click', categoryButtonClickHandler);
                link.addEventListener('click', categoryButtonClickHandler);
              });
            }

            function categoryButtonClickHandler(e) {
              e.preventDefault();
          fetchPage(e.currentTarget.href);
            }

            attachCategoryButtonHandlers();
attachPaginationHandlers();

            // Handle browser back/forward buttons
            window.addEventListener('popstate', () => {
              fetchPage(location.href, false);
            });
          });
          </script>
      </body>
    </html>
