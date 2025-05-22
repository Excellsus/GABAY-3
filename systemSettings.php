<?php
include("connect_db.php");
$stmt = $connect->prepare("SELECT * FROM admin WHERE username = 'admin_user' LIMIT 1");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// AJAX endpoint for updating account settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === 'updateAccount') {
  $result = ['success' => false];
  
  try {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($email)) {
      throw new Exception("Email is required");
    }
    
    if (!empty($password) && $password !== $confirm_password) {
      throw new Exception("Passwords do not match");
    }
    
    // Update the admin record
    if (!empty($password)) {
      // Update email and password
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $connect->prepare("UPDATE admin SET email = ?, password = ? WHERE username = 'admin_user'");
      $stmt->execute([$email, $hashedPassword]);
    } else {
      // Update email only
      $stmt = $connect->prepare("UPDATE admin SET email = ? WHERE username = 'admin_user'");
      $stmt->execute([$email]);
    }
    
    $result = ['success' => true, 'message' => 'Account updated successfully'];
  } catch (Exception $e) {
    $result = ['success' => false, 'message' => $e->getMessage()];
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
  <link rel="stylesheet" href="systemSetting.css" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="./mobileNav.js"></script>
  <link rel="stylesheet" href="mobileNav.css" />
  <style>
    /* Custom modal styles */
    .modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s, visibility 0.3s;
    }
    
    .modal-overlay.active {
      opacity: 1;
      visibility: visible;
    }
    
    .modal-container {
      background-color: white;
      padding: 30px;
      border-radius: 10px;
      max-width: 400px;
      width: 100%;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      transform: translateY(-20px);
      transition: transform 0.3s;
    }
    
    .modal-overlay.active .modal-container {
      transform: translateY(0);
    }
    
    .modal-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 15px;
      color: #333;
    }
    
    .modal-content {
      margin-bottom: 20px;
      color: #555;
      line-height: 1.5;
    }
    
    .modal-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
    
    .modal-btn {
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 500;
      transition: background-color 0.2s;
    }
    
    .modal-btn-cancel {
      background-color: #e2e8f0;
      color: #4a5568;
    }
    
    .modal-btn-cancel:hover {
      background-color: #cbd5e0;
    }
    
    .modal-btn-confirm {
      background-color: #e53e3e;
      color: white;
    }
    
    .modal-btn-confirm:hover {
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
      <a href="officeManagement.php">Office Management</a>
      <a href="floorPlan.php">Floor Plans</a>
      <a href="visitorFeedback.php">Visitor Feedback</a>
      <a href="systemSettings.php" class="active">System Settings</a>
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
          <li><a href="officeManagement.php">Office Management</a></li>
          <li><a href="floorPlan.php">Floor Plans</a></li>
          <li><a href="visitorFeedback.php">Visitor Feedback</a></li>
          <li><a href="systemSettings.php" class="active">System Settings</a></li>
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
      <!-- Top Bar -->
      <header class="header">
        <div>
          <h2>Admin Settings</h2>
          <p>Manage your account and system preferences.</p>
        </div>
      </header>

      <div class="gabay-main-content">
        <!-- Content Grid -->
        <div class="gabay-grid">

          <!-- Account Settings -->
          <form id="accountSettingsForm" class="gabay-card">
            <h3 class="gabay-card-title">Account Settings</h3>
            <div class="gabay-form-group">
              <label>Username</label>
              <input type="text" value="<?= htmlspecialchars($admin['username']) ?>" disabled />
            </div>
            <div class="gabay-form-group">
              <label>Email</label>
              <input type="email" name="email" id="email" value="<?= htmlspecialchars($admin['email']) ?>" required />
            </div>
            <div class="gabay-form-group">
              <label>Change Password</label>
              <input type="password" name="password" id="password" placeholder="Enter new password" />
            </div>
            <div class="gabay-form-group">
              <label>Confirm Password</label>
              <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" />
            </div>
            <div id="updateMessage" style="margin-bottom: 10px;"></div>
            <div class="gabay-button-wrapper">
              <button type="submit" class="gabay-btn gabay-btn-green">Update Account</button>
            </div>
          </form>

          <!-- System Preferences -->
          <div class="gabay-card">
            <h3 class="gabay-card-title">System Preferences</h3>

            <div class="gabay-toggle-group">
              <div>
                <h4>Dark Mode</h4>
                <p>Switch between light and dark theme</p>
              </div>
              <label class="gabay-switch">
                <input type="checkbox" id="darkModeSwitch" />
                <span class="gabay-slider"></span>
              </label>
            </div>

            <div class="gabay-form-group">
              <label>Language</label>
              <select>
                <option>English</option>
                <option>Filipino</option>
                <option>Hiligaynon</option>
              </select>
            </div>

            <div class="gabay-button-wrapper">
              <button type="button" class="gabay-btn gabay-btn-red" id="logoutBtn">Logout</button>
            </div>
          </div>

        </div>
      </div>
    </main>
  </div>

  <!-- Custom Logout Modal -->
  <div class="modal-overlay" id="logoutModal">
    <div class="modal-container">
      <div class="modal-title">Confirm Logout</div>
      <div class="modal-content">
        Are you sure you want to log out of your account?
      </div>
      <div class="modal-buttons">
        <button class="modal-btn modal-btn-cancel" id="cancelLogout">Cancel</button>
        <button class="modal-btn modal-btn-confirm" id="confirmLogout">Logout</button>
      </div>
    </div>
  </div>

  <script src="darkMode.js"></script>
  
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Account settings form submission
    $('#accountSettingsForm').on('submit', function(e) {
      e.preventDefault();
      
      const email = $('#email').val();
      const password = $('#password').val();
      const confirm_password = $('#confirm_password').val();
      
      // Basic validation
      if (!email) {
        $('#updateMessage').html('<div style="color: red;">Email is required</div>');
        return;
      }
      
      if (password !== confirm_password) {
        $('#updateMessage').html('<div style="color: red;">Passwords do not match</div>');
        return;
      }
      
      // AJAX request
      $.ajax({
        type: 'POST',
        url: 'systemSettings.php',
        data: {
          ajax: 'updateAccount',
          email: email,
          password: password,
          confirm_password: confirm_password
        },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            $('#updateMessage').html('<div style="color: green;">' + response.message + '</div>');
            // Clear password fields
            $('#password').val('');
            $('#confirm_password').val('');
          } else {
            $('#updateMessage').html('<div style="color: red;">' + response.message + '</div>');
          }
          
          // Clear message after 3 seconds
          setTimeout(function() {
            $('#updateMessage').html('');
          }, 3000);
        },
        error: function() {
          $('#updateMessage').html('<div style="color: red;">An error occurred. Please try again.</div>');
        }
      });
    });
    
    // Custom modal logout functionality
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogout = document.getElementById('cancelLogout');
    const confirmLogout = document.getElementById('confirmLogout');
    
    // Show modal when logout button is clicked
    logoutBtn.addEventListener('click', function() {
      logoutModal.classList.add('active');
    });
    
    // Hide modal when cancel button is clicked
    cancelLogout.addEventListener('click', function() {
      logoutModal.classList.remove('active');
    });
    
    // Redirect to login page when confirm button is clicked
    confirmLogout.addEventListener('click', function() {
      window.location.href = "login.php";
    });
    
    // Close modal when clicking outside
    logoutModal.addEventListener('click', function(e) {
      if (e.target === logoutModal) {
        logoutModal.classList.remove('active');
      }
    });
  });
  </script>

</body>
</html>