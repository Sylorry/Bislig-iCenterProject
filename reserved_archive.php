<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Archived Reservations - Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com/3.4.16"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .status-badge {
      @apply px-3 py-1.5 rounded-full text-sm font-semibold transition-all duration-200;
    }
    .status-completed {
      background: #d1fae5;
      color: #065f46;
      border: 1px solid #34d399;
    }
    .status-not_completed {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #f87171;
    }
    .row-highlight {
      animation: highlightRow 1s;
    }
    @keyframes highlightRow {
      0% { background-color: #fef9c3; }
      100% { background-color: transparent; }
    }
    .modal-bg {
      background: rgba(0,0,0,0.6);
      backdrop-filter: blur(4px);
    }
    .table-container {
      max-height: calc(100vh - 250px);
      overflow-y: auto;
    }
    .table-container table {
      border-collapse: collapse;
    }
    .table-container th,
    .table-container td {
      padding: 6px 8px;
      border: 1px solid #e5e7eb;
    }
    .table-container th {
      background-color: #f3f4f6;
      font-weight: 600;
      text-align: left;
    }
    .table-container::-webkit-scrollbar {
      width: 8px;
    }
    .table-container::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }
    .table-container::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 4px;
    }
    .table-container::-webkit-scrollbar-thumb:hover {
      background: #555;
    }
  </style>
</head>
<body class="bg-gray-50 font-sans min-h-screen p-6">
  <!-- Toast -->
  <div id="toast" class="fixed top-6 right-6 z-50 hidden bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transition-all duration-300 flex items-center">
    <i class="fas fa-check-circle mr-2"></i>
    <span id="toast-message"></span>
  </div>

  <!-- View Modal -->
  <div id="viewModal" class="fixed inset-0 z-40 hidden items-center justify-center modal-bg transition-opacity duration-300 p-4">
    <div class="bg-white rounded-xl shadow-2xl p-6 relative max-w-4xl w-full max-h-[85vh] overflow-y-auto transform transition-all duration-300 scale-95 opacity-0" id="viewModalContent">
      <button onclick="closeViewModal()" class="sticky top-0 right-0 float-right text-gray-400 hover:text-black text-2xl transition-colors duration-200 hover:scale-110 bg-white rounded-full p-1 shadow-sm">
        <i class="fas fa-times"></i>
      </button>
      <div class="flex items-center mb-4 pb-4 border-b border-gray-200">
        <i class="fas fa-file-alt text-2xl text-blue-500 mr-3"></i>
        <h3 class="text-2xl font-bold text-gray-800">Archived Reservation Details</h3>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 rounded-t-lg border-b border-gray-200">
              <h4 class="font-semibold text-gray-800 flex items-center">
                <i class="fas fa-user-circle text-blue-500 mr-2"></i>
                Customer Information
              </h4>
            </div>
            <div class="p-4 space-y-2">
              <p class="flex items-center text-gray-700">
                <span class="font-medium w-24">Name:</span>
                <span id="viewName" class="text-gray-900"></span>
              </p>
              <p class="flex items-center text-gray-700">
                <span class="font-medium w-24">Contact:</span>
                <span id="viewContact" class="text-gray-900"></span>
              </p>
              <p class="flex items-center text-gray-700">
                <span class="font-medium w-24">Email:</span>
                <span id="viewEmail" class="text-gray-900"></span>
              </p>
              <p class="flex items-center text-gray-700">
                <span class="font-medium w-24">Address:</span>
                <span id="viewAddress" class="text-gray-900"></span>
              </p>
            </div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 rounded-t-lg border-b border-gray-200">
              <h4 class="font-semibold text-gray-800 flex items-center">
                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                Reservation Details
              </h4>
            </div>
            <div class="p-4 space-y-2">
              <p class="flex items-center text-gray-700">
                <span class="font-medium w-32">Reservation ID:</span>
                <span id="viewReservationId" class="text-gray-900 font-mono"></span>
              </p>
              <p class="flex items-center text-gray-700">
                <span class="font-medium w-32">Date:</span>
                <span id="viewDate" class="text-gray-900"></span>
              </p>
              <p class="flex items-center text-gray-700">
                <span class="font-medium w-32">Time:</span>
                <span id="viewTime" class="text-gray-900"></span>
              </p>
              <p class="flex items-center text-gray-700">
                <span class="font-medium w-32">Status:</span>
                <span id="viewStatus" class="text-gray-900"></span>
              </p>
            </div>
          </div>
        </div>
        <div class="space-y-4">
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 rounded-t-lg border-b border-gray-200">
              <h4 class="font-semibold text-gray-800 flex items-center">
                <i class="fas fa-box text-blue-500 mr-2"></i>
                Product Information
              </h4>
            </div>
            <div class="p-4 space-y-2">
              <p class="flex items-center text-gray-700">
                <span class="font-medium w-32">Product Name:</span>
                <span id="viewProductName" class="text-gray-900"></span>
              </p>
            </div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 rounded-t-lg border-b border-gray-200">
              <h4 class="font-semibold text-gray-800 flex items-center">
                <i class="fas fa-money-bill-wave text-blue-500 mr-2"></i>
                Payment Information
              </h4>
            </div>
            <div class="p-4 space-y-2">
              <p class="flex items-center text-gray-700">
                <span class="font-medium w-32">Down Payment:</span>
                <span id="viewDownPayment" class="text-gray-900 font-mono"></span>
              </p>
              <p class="flex items-center text-gray-700">
                <span class="font-medium w-32">Balance:</span>
                <span id="viewBalance" class="text-gray-900 font-mono"></span>
              </p>
              <div class="mt-3 pt-3 border-t border-gray-200">
                <p class="font-medium text-gray-700 mb-2">Proof of Payment:</p>
                <div class="relative group">
                  <img id="viewProof" src="" alt="Proof of Payment" 
                       class="max-w-full h-auto rounded-lg border border-gray-200 transition-transform duration-200 group-hover:scale-[1.02]">
                  <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-200 rounded-lg"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Unarchive Confirmation Modal -->
  <div id="unarchiveModal" class="fixed inset-0 z-40 hidden items-center justify-center modal-bg">
    <div class="bg-white rounded-lg shadow-xl p-6 relative max-w-md w-full">
      <button onclick="closeUnarchiveModal()" class="absolute top-4 right-4 text-gray-500 hover:text-black text-2xl transition-colors">
        <i class="fas fa-times"></i>
      </button>
      <div class="text-center mb-6">
        <i class="fas fa-undo text-3xl text-green-500 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Unarchive Reservation</h3>
        <p class="text-gray-600">Are you sure you want to unarchive this reservation? It will be moved back to active reservations.</p>
      </div>
      <div class="flex justify-center space-x-4">
        <button onclick="closeUnarchiveModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
          Cancel
        </button>
        <button onclick="confirmUnarchive()" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
          Unarchive
        </button>
      </div>
    </div>
  </div>

  <!-- Header Section -->
  <div class="bg-white rounded-xl shadow-md p-6 mb-6">
    <div class="flex justify-between items-center">
      <a href="reserved.php" class="inline-flex items-center bg-black text-white hover:bg-gray-800 px-4 py-2 rounded-md shadow-md transition-colors duration-200">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to Active Reservations
      </a>
      <img src="images/iCenter.png" alt="Logo" class="h-24" />
    </div>
    <div class="mt-4">
      <h1 class="text-3xl font-bold text-gray-800">Archived Reservations</h1>
      <p class="text-gray-600 mt-2">View and manage archived reservations</p>
    </div>
  </div>

  <!-- Stats Section -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-gray-500">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-gray-500 text-sm">Total Archived</p>
          <h3 class="text-2xl font-bold text-gray-800" id="totalArchived">0</h3>
        </div>
        <i class="fas fa-archive text-3xl text-gray-500"></i>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-gray-500 text-sm">Completed</p>
          <h3 class="text-2xl font-bold text-gray-800" id="completedArchived">0</h3>
        </div>
        <i class="fas fa-check-circle text-3xl text-green-500"></i>
      </div>
    </div>
  </div>

  <!-- Table Section -->
  <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
    <div class="flex justify-between items-center mb-4">
      <div class="flex items-center space-x-4">
        <div class="relative">
          <input type="text" id="searchInput" placeholder="Search archived reservations..." 
                 class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
        <select id="statusFilter" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="all">All Status</option>
          <option value="completed">Completed</option>
          <option value="not_completed">Not Completed</option>
        </select>
      </div>
      <div class="flex items-center space-x-2">
        <button onclick="exportToCSV()" class="border-2 border-blue-500 bg-white text-blue-500 px-4 py-2 rounded-lg hover:bg-black hover:text-white hover:border-black transition-colors">
          <i class="fas fa-file-export mr-2"></i>Export
        </button>
        <button onclick="printTable()" class="border-2 border-blue-500 bg-white text-blue-500 px-4 py-2 rounded-lg hover:bg-black hover:text-white hover:border-black transition-colors">
          <i class="fas fa-print mr-2"></i>Print
        </button>
      </div>
    </div>

    <div class="table-container">
      <table class="min-w-full table-auto border-collapse">
        <thead class="bg-gray-50 sticky top-0">
          <tr class="text-gray-900 font-semibold text-lg">
            <th class="border-b border-gray-300 px-6 py-4 text-left">Reservation ID</th>
            <th class="border-b border-gray-300 px-6 py-4 text-left">Name</th>
            <th class="border-b border-gray-300 px-6 py-4 text-left">Contact</th>
            <th class="border-b border-gray-300 px-6 py-4 text-left">Address</th>
            <th class="border-b border-gray-300 px-6 py-4 text-left">Status</th>
            <th class="border-b border-gray-300 px-6 py-4 text-left">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php
          require_once 'db.php';
          try {
            $stmt = $conn->query("SELECT * FROM reservations WHERE archived = 1 ORDER BY reservation_date DESC, reservation_time DESC");
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($reservations) === 0) {
              echo '<tr><td colspan="6" class="text-center p-8 text-gray-500">
                      <i class="fas fa-archive text-4xl mb-2"></i>
                      <p>No archived reservations found.</p>
                    </td></tr>';
            } else {
              foreach ($reservations as $res) {
                $rowId = 'row_' . htmlspecialchars($res['reservation_id']);
                echo '<tr id="' . $rowId . '" class="hover:bg-gray-50 transition-colors">';
                echo '<td class="border border-gray-300 px-6 py-3">' . htmlspecialchars($res['reservation_id']) . '</td>';
                echo '<td class="border border-gray-300 px-6 py-3">' . htmlspecialchars($res['name']) . '</td>';
                echo '<td class="border border-gray-300 px-6 py-3">
                        <div class="tooltip">
                          <i class="fas fa-phone-alt text-blue-500 mr-2"></i>' . htmlspecialchars($res['contact_number']) . '
                          <span class="tooltiptext">Click to copy</span>
                        </div>
                      </td>';
                echo '<td class="border border-gray-300 px-6 py-3">' . htmlspecialchars($res['address']) . '</td>';
                $status = htmlspecialchars($res['status'] ?? 'not_completed');
                $badgeClass = $status === 'completed' ? 'status-badge status-completed' : 'status-badge status-not_completed';
                echo '<td class="border border-gray-300 px-6 py-3">
                        <span class="' . $badgeClass . '" id="badge_' . $res['reservation_id'] . '">
                          <i class="fas ' . ($status === 'completed' ? 'fa-check-circle' : 'fa-clock') . ' mr-2"></i>' . 
                          ucfirst(str_replace('_', ' ', $status)) . 
                        '</span>
                      </td>';
                echo '<td class="border border-gray-300 px-6 py-3">
                        <div class="flex space-x-2">
                          <button onclick="viewReservation(' . htmlspecialchars(json_encode($res)) . ')" 
                             class="text-white bg-black hover:bg-gray-800 px-3 py-1 rounded-md shadow transition-colors duration-200">
                            <i class="fas fa-eye mr-1"></i>View
                          </button>
                          <button onclick="unarchiveReservation(' . htmlspecialchars($res['reservation_id']) . ')" 
                                  class="text-white bg-green-500 hover:bg-green-600 px-3 py-1 rounded-md shadow transition-colors duration-200">
                            <i class="fas fa-undo mr-1"></i>Unarchive
                          </button>
                        </div>
                      </td>';
                echo '</tr>';
              }
            }
          } catch (PDOException $e) {
            echo '<tr><td colspan="6" class="text-center p-4 text-red-500">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <p>Error loading archived reservations: ' . htmlspecialchars($e->getMessage()) . '</p>
                  </td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Show toast with icon
    function showToast(msg) {
      const toast = document.getElementById('toast');
      const toastMessage = document.getElementById('toast-message');
      toastMessage.textContent = msg;
      toast.classList.remove('hidden');
      setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    // View modal functions
    function viewReservation(reservation) {
      // Populate the modal with reservation data
      document.getElementById('viewName').textContent = reservation.name || 'N/A';
      document.getElementById('viewContact').textContent = reservation.contact_number || 'N/A';
      document.getElementById('viewEmail').textContent = reservation.email || 'N/A';
      document.getElementById('viewAddress').textContent = reservation.address || 'N/A';
      document.getElementById('viewReservationId').textContent = reservation.reservation_id || 'N/A';
      document.getElementById('viewDate').textContent = reservation.reservation_date || 'N/A';
      document.getElementById('viewTime').textContent = reservation.reservation_time || 'N/A';
      document.getElementById('viewProductName').textContent = reservation.product_name || 'N/A';
      document.getElementById('viewDownPayment').textContent = '₱' + (parseFloat(reservation.reservation_fee) || 0).toFixed(2);
      document.getElementById('viewBalance').textContent = '₱' + (parseFloat(reservation.remaining_reservation_fee) || 0).toFixed(2);
      
      // Set status with appropriate styling
      const statusElement = document.getElementById('viewStatus');
      const status = reservation.status || 'not_completed';
      statusElement.textContent = status === 'completed' ? 'Completed' : 'Not Completed';
      statusElement.className = 'status-badge ' + (status === 'completed' ? 'status-completed' : 'status-not_completed');
      
      // Set proof of payment image
      const proofImg = document.getElementById('viewProof');
      if (reservation.proof_of_payment && reservation.proof_of_payment.trim() !== '') {
        // Check if the data is base64 (old format) or filename (new format)
        const isBase64 = reservation.proof_of_payment.includes('data:image') || reservation.proof_of_payment.length > 100;
        
        if (isBase64) {
          // Handle old base64 format
          proofImg.src = reservation.proof_of_payment;
          proofImg.style.display = 'block';
        } else {
          // Handle new file-based format
          const imagePath = 'uploads/proof_of_payment/' + reservation.proof_of_payment;
          proofImg.src = imagePath;
          proofImg.style.display = 'block';
          
          // Add error handling
          proofImg.onerror = function() {
            this.style.display = 'none';
            const parentDiv = this.parentElement;
            if (parentDiv) {
              parentDiv.innerHTML = `
                <div class="bg-gray-100 p-4 rounded-lg border border-gray-200 text-center">
                  <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mb-2"></i>
                  <p class="text-gray-700 text-sm font-medium">Proof of payment not found</p>
                  <p class="text-gray-500 text-xs mt-1">Filename: ${reservation.proof_of_payment}</p>
                </div>
              `;
            }
          };
          
          // Add success handling and click handler
          proofImg.onload = function() {
            this.style.display = 'block';
            this.onclick = function() {
              centerAndShowImage(this.src);
            };
          };
        }
      } else {
        proofImg.style.display = 'none';
        // Show a placeholder
        const parentDiv = proofImg.parentElement;
        if (parentDiv) {
          parentDiv.innerHTML = `
            <div class="bg-gray-100 p-4 rounded-lg border border-gray-200 text-center">
              <i class="fas fa-image text-gray-400 text-2xl mb-2"></i>
              <p class="text-gray-500 text-sm">No proof of payment uploaded</p>
              <p class="text-gray-400 text-xs">This reservation does not require payment</p>
            </div>
          `;
        }
      }
      
      // Show the modal with animation
      const modal = document.getElementById('viewModal');
      const modalContent = document.getElementById('viewModalContent');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      
      // Trigger animation
      setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
      }, 10);
      
      // Prevent body scrolling when modal is open
      document.body.style.overflow = 'hidden';
    }

    function closeViewModal() {
      const modal = document.getElementById('viewModal');
      const modalContent = document.getElementById('viewModalContent');
      
      // Trigger closing animation
      modalContent.classList.remove('scale-100', 'opacity-100');
      modalContent.classList.add('scale-95', 'opacity-0');
      
      // Wait for animation to complete before hiding
      setTimeout(() => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        
        // Restore body scrolling
        document.body.style.overflow = 'auto';
        
        // Clear the modal content
        document.getElementById('viewProof').src = '';
      }, 300);
    }

    // Add these variables at the top of your script section
    let pendingUnarchive = {
      reservationId: null
    };

    // Update the unarchive functionality
    function unarchiveReservation(reservationId) {
      pendingUnarchive.reservationId = reservationId;
      
      // Show the modal
      const modal = document.getElementById('unarchiveModal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeUnarchiveModal() {
      const modal = document.getElementById('unarchiveModal');
      modal.classList.remove('flex');
      modal.classList.add('hidden');
      pendingUnarchive.reservationId = null;
    }

    function confirmUnarchive() {
      if (!pendingUnarchive.reservationId) {
        return;
      }

      fetch("unarchive_reservation.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "reservation_id=" + encodeURIComponent(pendingUnarchive.reservationId)
      })
      .then(response => response.text())
      .then(data => {
        const row = document.getElementById('row_' + pendingUnarchive.reservationId);
        if (row) {
          row.remove();
          showToast('Reservation unarchived successfully!');
          updateStats();
        }
        closeUnarchiveModal();
      })
      .catch(error => {
        showToast('Error unarchiving reservation!');
        console.error("Error unarchiving reservation:", error);
        closeUnarchiveModal();
      });
    }

    // Add click outside to close modal
    document.getElementById('unarchiveModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeUnarchiveModal();
      }
    });

    // Add ESC key to close modal
    document.addEventListener('keydown', function(e) {
      if (e.key === "Escape") {
        closeUnarchiveModal();
      }
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
      const searchText = e.target.value.toLowerCase();
      const rows = document.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
      });
      
      updateStats();
    });

    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function(e) {
      const status = e.target.value;
      const rows = document.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        if (status === 'all') {
          row.style.display = '';
        } else {
          const rowStatus = row.querySelector('.status-badge').textContent.toLowerCase();
          row.style.display = rowStatus.includes(status) ? '' : 'none';
        }
      });
      
      updateStats();
    });

    // Update statistics
    function updateStats() {
      const rows = document.querySelectorAll('tbody tr:not([style*="display: none"])');
      const total = rows.length;
      const completed = document.querySelectorAll('.status-completed').length;

      document.getElementById('totalArchived').textContent = total;
      document.getElementById('completedArchived').textContent = completed;
    }

    // Initialize stats
    document.addEventListener('DOMContentLoaded', function() {
      updateStats();
    });

    // Copy contact number
    document.querySelectorAll('.tooltip').forEach(tooltip => {
      tooltip.addEventListener('click', function() {
        const text = this.querySelector('i').nextSibling.textContent.trim();
        navigator.clipboard.writeText(text).then(() => {
          showToast('Contact number copied to clipboard!');
        });
      });
    });

    // Export to CSV
    function exportToCSV() {
      const rows = document.querySelectorAll('tbody tr');
      let csv = [];
      
      // Add headers
      const headers = Array.from(document.querySelectorAll('thead th')).map(th => th.textContent.trim());
      csv.push(headers.join(','));
      
      // Add data rows
      rows.forEach(row => {
        const rowData = Array.from(row.querySelectorAll('td')).map(td => {
          const text = td.textContent.trim().replace(/\s+/g, ' ');
          return `"${text}"`;
        });
        csv.push(rowData.join(','));
      });
      
      const csvContent = csv.join('\n');
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'archived_reservations.csv';
      link.click();
    }

    // Print table
    function printTable() {
      const printWindow = window.open('', '_blank');
      const tableContent = document.querySelector('.table-container').innerHTML;
      
      printWindow.document.write(`
        <html>
          <head>
            <title>Archived Reservations Report</title>
            <style>
              @page {
                size: landscape;
                margin: 10mm;
              }
              body { 
                font-family: Arial, sans-serif;
                margin: 20px;
                padding: 0;
              }
              table { 
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
              }
              th, td { 
                border: 1px solid #000;
                padding: 4px 6px;
                text-align: left;
                font-size: 11px;
                line-height: 1.2;
              }
              th { 
                background-color: #f3f4f6;
                font-weight: bold;
                text-transform: uppercase;
              }
              tr:nth-child(even) {
                background-color: #f9fafb;
              }
              .status-badge { 
                padding: 2px 6px;
                border-radius: 4px;
                display: inline-block;
                font-weight: bold;
                font-size: 10px;
              }
              .status-completed { 
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #065f46;
              }
              .status-not_completed { 
                background: #fee2e2;
                color: #991b1b;
                border: 1px solid #991b1b;
              }
              @media print {
                body { 
                  margin: 0;
                  padding: 10px;
                }
                table { 
                  page-break-inside: auto;
                }
                tr { 
                  page-break-inside: avoid;
                  page-break-after: auto;
                }
                th, td {
                  -webkit-print-color-adjust: exact;
                  print-color-adjust: exact;
                }
              }
            </style>
          </head>
          <body>
            <h2 style="text-align: center; margin-bottom: 15px; font-size: 16px;">Archived Reservations Report</h2>
            ${tableContent}
          </body>
        </html>
      `);
      
      printWindow.document.close();
      printWindow.focus();
      setTimeout(() => {
        printWindow.print();
        printWindow.close();
      }, 250);
    }

    // Add click outside to close modal
    document.getElementById('viewModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeViewModal();
      }
    });

    // Add ESC key to close modal
    document.addEventListener('keydown', function(e) {
      if (e.key === "Escape") {
        closeViewModal();
      }
    });
  </script>
</body>
</html>