<?php
// Enable error reporting for debugging (remove or adjust for production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'connect_db.php'; // Include database connection

$offices = []; // Initialize as empty array

try {
    // Check if $connect is a valid PDO object
    if (!isset($connect) || !$connect) {
        throw new Exception("Database connection object (\$connect) is not valid. Check connect_db.php.");
    }

    // Fetch all office data
    $stmt = $connect->query("SELECT id, name, details, contact, location, status FROM offices");

    // Check if query execution was successful
    if ($stmt === false) {
        // Query failed, get error info
        $errorInfo = $connect->errorInfo();
        throw new PDOException("Query failed: " . ($errorInfo[2] ?? 'Unknown error - Check table/column names and permissions.'));
    }

    // Fetch the data
    $offices = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) { // Catches PDOException and general Exception
    error_log("Error in floorPlan.php: " . $e->getMessage()); // Log error to PHP error log
    // You could output a message here, but it might break the page structure. Logging is safer.
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GABAY Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="floorPlan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="./mobileNav.js"></script>
  <link rel="stylesheet" href="mobileNav.css" />
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              "negros-green": "#1A5632",
              "negros-light": "#E8F5E9",
              "negros-dark": "#0D3018",
              "negros-gold": "#FFD700",
            },
          },
        },
      };
    </script>
  </head>
  <body>
    <div  class="container">
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
      <a href="floorPlan.php"  class="active">Floor Plans</a>
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
          <li><a href="officeManagement.php">Office Management</a></li>
          <li><a href="floorPlan.php" class="active">Floor Plans</a></li>
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
      <div class="main-content">
      <header class="header">
        <div>
          <h2 class="font-bold">Floor Plan</h2>
          <p class="text-gray-500">View and manage floor plans</p>
        </div>
      </header>

      <!-- Tooltip Element -->
      <div id="floorplan-tooltip" class="absolute bg-black text-white text-xs px-2 py-1 rounded shadow-lg pointer-events-none hidden z-50"></div>


        <!-- SVG Container -->
        <div class="flex-grow bg-white rounded-xl p-6 card-shadow overflow-hidden flex relative h-auto"> <!-- Added relative class -->
          <div class="floor-plan-container flex-grow relative"> <!-- Added relative class -->

            <button id="edit-floorplan-btn" class="absolute top-4 right-4 z-10 bg-negros-green text-white px-3 py-1 rounded-md text-sm hover:bg-negros-dark transition-colors cursor-pointer">
              Edit
          </button>
        
          <?php
          // Load the SVG file from the svgMaps directory
          $svgFile = 'svgMaps/Capitol_1st_floor_layout_15.svg';
          if (file_exists($svgFile)) {
              echo file_get_contents($svgFile);
          } else {
              echo '<div class="text-center p-10 text-red-500">SVG file not found. Please place floor1.svg in the svgMaps directory.</div>';
          }
          ?>

          </div>
        </div>
      </div>
    </div>

    <!-- Right Side Panel for Office Details -->
    <div id="office-details-panel" class="office-details-panel">
        <button id="close-panel-btn" class="close-panel-btn">&times;</button>
        <h3 id="panel-office-name">Office Name</h3>
        <div class="panel-section">
            <h4>Status</h4>
            <label class="switch">
                <input type="checkbox" id="office-active-toggle">
                <span class="slider round"></span>
            </label>
            <span id="office-status-text">Active</span>
        </div>
        
    </div>

    <!-- Load libraries and scripts in the correct order -->
     <script src="https://cdn.jsdelivr.net/npm/svg-pan-zoom@3.6.1/dist/svg-pan-zoom.min.js"></script>
     <script>
        // Pass PHP office data to JavaScript
        const officesData = <?php echo json_encode($offices); ?>;
        console.log("Offices Data Loaded:", officesData); // For debugging
        
        // Force immediate cache refresh with unique version
        const scriptVersion = '<?php echo date("YmdHis"); ?>';
        console.log("Loading scripts with version:", scriptVersion);
     </script>
    
    <!-- First, load the pan-zoom setup -->
    <script src="floorjs/panZoomSetup.js?v=<?php echo time(); ?>"></script>
    
    <!-- Then, load the label setup -->
    <script src="./floorjs/labelSetup.js?v=<?php echo time(); ?>"></script>
    
    <!-- Finally, load the drag-drop setup that depends on both -->
    <script src="./floorjs/dragDropSetup.js?v=<?php echo time(); ?>"></script>
    
    <!-- Add the new patch script to ensure all rooms have interactive-room class -->
    <script src="./floorjs/roomClassFix.js?v=<?php echo time(); ?>"></script>
   </body>
 </html>
