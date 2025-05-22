<?php
include 'connect_db.php';

// Handle edit selection
$editData = ['id' => '', 'name' => '', 'details' => '', 'contact' => '', 'location' => '', 'services' => '']; // Added services
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['office_id']) && !isset($_POST['ajax'])) {
  $stmt = $connect->prepare("SELECT id, name, details, contact, location, services FROM offices WHERE id = ?"); // Added services
  $stmt->execute([$_POST['office_id']]); // Fetch services
  $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['location']) && !isset($_POST['ajax'])) {
  $office_id = $_POST['office_id'] ?? '';
  $name = $_POST['name']; // Correct column name
  $details = $_POST['details'];
  $contact = $_POST['contact'];
  $location = $_POST['location'];
  $services = $_POST['services']; // Added services

  if ($office_id) {
    // Update existing office
    $stmt = $connect->prepare("UPDATE offices SET name=?, details=?, contact=?, location=?, services=? WHERE id=?"); // Added services
    $stmt->execute([$name, $details, $contact, $location, $services, $office_id]); // Added services
  } else {
    // Insert new office
    $stmt = $connect->prepare("INSERT INTO offices (name, details, contact, location, services) VALUES (?, ?, ?, ?, ?)"); // Added services
    $stmt->execute([$name, $details, $contact, $location, $services]); // Added services
  }

  header("Location: officeManagement.php");
  exit;
}

// AJAX endpoint for getting office details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === 'getOffice') {
  $stmt = $connect->prepare("SELECT id, name, details, contact, location, services FROM offices WHERE id = ?"); // Added services
  $stmt->execute([$_POST['office_id']]);
  $officeData = $stmt->fetch(PDO::FETCH_ASSOC);
  echo json_encode($officeData);
  exit;
}

// AJAX endpoint for saving office data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === 'saveOffice') {
  $office_id = $_POST['office_id'] ?? '';
  $name = $_POST['name'];
  $details = $_POST['details'];
  $contact = $_POST['contact'];
  $location = $_POST['location'];
  $services = $_POST['services']; // Added services
  $result = ['success' => false];

  try {
    if ($office_id) {
      // Update existing office
      $stmt = $connect->prepare("UPDATE offices SET name=?, details=?, contact=?, location=?, services=? WHERE id=?"); // Added services
      $stmt->execute([$name, $details, $contact, $location, $services, $office_id]); // Added services
    } else {
      // Insert new office
      $stmt = $connect->prepare("INSERT INTO offices (name, details, contact, location, services) VALUES (?, ?, ?, ?, ?)"); // Added services
      $stmt->execute([$name, $details, $contact, $location, $services]); // Added services
      $office_id = $connect->lastInsertId();
    }
    $result = ['success' => true, 'message' => 'Office saved successfully', 'office_id' => $office_id];
  } catch (Exception $e) {
    $result = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
  }
  
  echo json_encode($result);
  exit;
}
?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>GABAY Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="ofiiceManagement.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
    <!-- Updated Font Awesome to version 6.5.2 for modern icons and styling -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="./mobileNav.js"></script>
    <link rel="stylesheet" href="mobileNav.css" />
      
  <style>
   /* Modal Dialog Styles */
.modal-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s, visibility 0.3s;
}

.modal-overlay.active {
  opacity: 1;
  visibility: visible;
  display: flex;
}

.modal-dialog {
  background-color: white;
  padding: 30px;
  border-radius: 10px;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  transform: translateY(-20px);
  transition: transform 0.3s;
}

.modal-overlay.active .modal-dialog {
  transform: translateY(0);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 15px;
  color: #333;
}

.modal-close {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #666;
}

.modal-body {
  margin-bottom: 20px;
  color: #555;
  line-height: 1.5;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.btn {
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s;
}

.btn-secondary {
  background-color: #e2e8f0;
  color: #4a5568;
}

.btn-secondary:hover {
  background-color: #cbd5e0;
}

.btn-primary {
  background-color: #e53e3e;
  color: white;
}

.btn-primary:hover {
  background-color: #c53030;
}
  </style>
</head>
<body>
  <div class="container">
      <!-- Mobile Navigation -->
<div class="mobile-nav">
  <div class="mobile-nav-header">
    <div class="mobile-logo-container">
      <img src="./srcImage/images-removebg-preview.png" alt="GABAY Logo">
      <div>
        <h1>GABAY</h1>
        <p>Admin Portal</p>
      </div>
    </div>
    <div class="hamburger-icon" onclick="toggleMobileMenu()">
      <i class="fa fa-bars"></i>
    </div>
  </div>
  
  <div class="mobile-menu" id="mobileMenu">
    <a href="home.php">Dashboard</a>
    <a href="officeManagement.php" class="active">Office Management</a>
    <a href="floorPlan.php">Floor Plans</a>
    <a href="visitorFeedback.php">Visitor Feedback</a>
    <a href="systemSettings.php">System Settings</a>
  </div>
</div>
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="logo">
          <img src="./srcImage/images-removebg-preview.png" alt="Logo" class="icon" />
        </div>
        <div>
          <h1>GABAY</h1>
          <p>Admin Portal</p>
        </div>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li><a href="home.php">Dashboard</a></li>
          <li><a href="officeManagement.php" class="active">Office Management</a></li>
          <li><a href="floorPlan.php">Floor Plans</a></li>
          <li><a href="visitorFeedback.php">Visitor Feedback</a></li>
          <li><a href="systemSettings.php">System Settings</a></li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <div class="profile">
          <div class="avatar">AD</div>
          <div>
            <p>Admin User</p>
            <span>Super Admin</span>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="header">
        <div>
          <h2>Office Management</h2>
          <p>Manage office rooms and their details.</p>
        </div>
      </header>

      <!-- Content Grid -->
      <div class="content-grid">
        <!-- Form Card -->
        <div class="form-card">
          <form id="officeForm" class="form-content">
            <input type="hidden" name="office_id" id="office_id" value="<?= htmlspecialchars($editData['id'] ?? '') ?>">
            <div class="form-group">
              <label for="office-name" class="form-label">Office Name</label>
              <input type="text" name="name" id="office-name" class="form-input"
                     value="<?= htmlspecialchars($editData['name'] ?? '') ?>" required />
            </div>
            <div class="form-group">
              <label for="details" class="form-label">Details</label>
              <textarea name="details" id="details" class="form-input" rows="1"
                        required><?= htmlspecialchars($editData['details'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
              <label for="contact" class="form-label">Contact Information</label>
              <input type="text" name="contact" id="contact" class="form-input"
                     value="<?= htmlspecialchars($editData['contact'] ?? '') ?>" required />
            </div>
            <div class="form-group">
              <label for="location" class="form-label">Location</label>
              <input type="text" name="location" id="location" class="form-input"
                     value="<?= htmlspecialchars($editData['location'] ?? '') ?>" required />
            </div>
            <div class="form-group">
              <label for="services" class="form-label">Services Offered</label>
              <textarea name="services" id="services" class="form-input" rows="1"
                        placeholder="List services offered, one per line..."><?= htmlspecialchars($editData['services'] ?? '') ?></textarea>
            </div>
            <div class="form-footer">
              <button type="submit" class="save-button">Save Changes</button>
              <div id="formMessage" style="margin-top: 10px;"></div>
            </div>
          </form>
        </div>

        <div class="office-list-card">
          <div class="office-list-header">
              <h3 class="office-list-title">Office List</h3>
          </div>
          <div class="office-list-content">
          <?php 
              if (!isset($connect)) { die('Database connection not established.'); }
              
              $stmt = $connect->query("SELECT * FROM offices ORDER BY name ASC");
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  $officeId = $row['id'];
                  $officeName = $row['name'];
                  $qrImagePath = "qrcodes/office_$officeId.png";

                  echo '<div class="office-item" style="display: flex; justify-content: space-between; align-items: center; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 8px; padding: 8px 12px;">';
                      
                      echo '<div class="office-btn" data-office-id="' . $row['id'] . '" style="flex: 1; cursor: pointer; display: flex; align-items: center;">';
                                                   // Use Font Awesome building icon (available in FA 4.7)
                                                   // Changed icon to door-open (FA6) and kept custom styles
                                                   echo '<i class="fas fa-door-open" style="margin-right: 15px; color: #2e7d32; font-size: 28px;"></i>'; 
                          echo '<div class="office-info">';
                              echo '<p class="office-name" style="font-weight: bold; margin: 0;">' . htmlspecialchars($row['name']) . '</p>';
                              echo '<p class="office-location" style="margin: 0; font-size: 13px; color: #666;">' . htmlspecialchars($row['location']) . '</p>';
                          echo '</div>';
                      echo '</div>';

                      // Fixed QR download button
                      echo '<form method="POST" action="download_qr.php" class="qr-download-form" style="margin-left: 10px;">';
                          echo '<input type="hidden" name="office_id" value="' . $officeId . '">';
                          echo '<button type="button" class="download-qr-btn" style="background: none; border: none; padding: 0; cursor: pointer;">';
                              echo '<img src="./srcImage/qr-code.png" alt="Download" class="download-icon" style="width: 20px; height: 20px;">';
                          echo '</button>';
                      echo '</form>';

                  echo '</div>';
              }
          ?>
          </div>
        </div>
      </div>
    </main>
  </div>
  
<!-- Save Changes Confirmation Modal -->
<div id="saveModal" class="modal-overlay">
  <div class="modal-dialog">
    <div class="modal-header">
      <h4 class="modal-title">Save Changes</h4>
    </div>
    <div class="modal-body">
      <p>Are you sure you want to save these changes?</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" id="cancelSave">Cancel</button>
      <button class="btn btn-primary" id="confirmSave">Save</button>
    </div>
  </div>
</div>

<!-- Download QR Confirmation Modal -->
<div id="downloadModal" class="modal-overlay">
  <div class="modal-dialog">
    <div class="modal-header">
      <h4 class="modal-title">Download QR Code</h4>
    </div>
    <div class="modal-body">
      <p>Do you want to download this QR code?</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" id="cancelDownload">Cancel</button>
      <button class="btn btn-primary" id="confirmDownload">Download</button>
    </div>
  </div>
</div>

  <script>
    $(document).ready(function() {
  // Store the original form data
  let formData = null;
  let downloadQrForm = null;
  
  // Handle form submission using modal
  $('#officeForm').on('submit', function(e) {
    e.preventDefault();
    formData = $(this).serialize() + '&ajax=saveOffice';
    $('#saveModal').addClass('active');
  });
  
  // Close save modal when clicking X or Cancel
  $('#closeSaveModal, #cancelSave').on('click', function() {
    $('#saveModal').removeClass('active');
  });
  
  // Handle save confirmation
  $('#confirmSave').on('click', function() {
    $('#saveModal').removeClass('active');
    
    $.ajax({
      type: 'POST',
      url: 'officeManagement.php',
      data: formData,
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          $('#formMessage').html('<div style="color: green;">' + response.message + '</div>');
          setTimeout(function() {
            $('#formMessage').html('');
            // Optional: reload the page to show updated data
            location.reload();
          }, 3000);
        } else {
          $('#formMessage').html('<div style="color: red;">' + response.message + '</div>');
        }
      },
      error: function() {
        $('#formMessage').html('<div style="color: red;">An error occurred. Please try again.</div>');
      }
    });
  });
  
  // Handle QR download button click
  $(document).on('click', '.download-qr-btn', function(e) {
    e.preventDefault();
    downloadQrForm = $(this).closest('form');
    $('#downloadModal').addClass('active');
  });
  
  // Close download modal when clicking X or Cancel
  $('#closeDownloadModal, #cancelDownload').on('click', function() {
    $('#downloadModal').removeClass('active');
  });
  
  // Handle download confirmation
  $('#confirmDownload').on('click', function() {
    $('#downloadModal').removeClass('active');
    if (downloadQrForm) {
      downloadQrForm.submit();
    }
  });
  
  // Handle office list item click
  $(document).on('click', '.office-btn', function() {
    const officeId = $(this).data('office-id');
    
    $.ajax({
      type: 'POST',
      url: 'officeManagement.php',
      data: {
        ajax: 'getOffice',
        office_id: officeId
      },
      dataType: 'json',
      success: function(data) {
        $('#office_id').val(data.id);
        $('#office-name').val(data.name);
        $('#details').val(data.details);
        $('#contact').val(data.contact);
        $('#location').val(data.location);
        $('#services').val(data.services); // Populate services field
      },
      error: function() {
        alert('Failed to load office details');
      }
    });
  });
});
  </script>
</body>
</html>