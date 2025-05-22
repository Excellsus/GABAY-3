<?php
require_once "connect_db.php";

// Get filter settings
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$ratingFilter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;

// Build the query based on filters
$query = "SELECT * FROM feedback WHERE 1=1";
$params = [];

// Apply rating filter if selected
if ($ratingFilter > 0) {
  $query .= " AND rating = ?";
  $params[] = $ratingFilter;
}

// Apply sorting
if ($sortOrder === 'oldest') {
    $query .= " ORDER BY submitted_at ASC";
} else {
    $query .= " ORDER BY submitted_at DESC"; // Default newest first
}

// Prepare and execute the query
$stmt = $connect->prepare($query);
$stmt->execute($params);
$feedbackList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate feedback stats
$totalFeedback = count($feedbackList);
$averageRating = $totalFeedback > 0 ? array_sum(array_column($feedbackList, 'rating')) / $totalFeedback : 0;
$highestRating = $totalFeedback > 0 ? max(array_column($feedbackList, 'rating')) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>GABAY Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="visitorFeedback.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="./mobileNav.js"></script>
  <link rel="stylesheet" href="mobileNav.css" />
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
      <a href="visitorFeedback.php" class="active">Visitor Feedback</a>
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
          <li><a href="floorPlan.php">Floor Plans</a></li>
          <li><a href="visitorFeedback.php" class="active">Visitor Feedback</a></li>
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


  <main class="main-content">
    <header class="header">
      <div>
        <h2>Feedback Details</h2>
        <p>Review and analyze visitor feedback data.</p>
      </div>
    </header>

    <!-- Stats Cards Row (Similar to dashboard) -->
    <div class="stats-cards">
      <div class="stat-card">
        <h3><?= $totalFeedback ?></h3>
        <p>Total Feedbacks</p>
      </div>
      <div class="stat-card">
        <h3><?= number_format($averageRating, 1) ?></h3>
        <p>Average Rating</p>
      </div>
      <div class="stat-card">
        <h3><?= number_format($highestRating, 1) ?> ★</h3>
        <p>Highest Rating</p>
      </div>
    </div>

    <!-- Feedback Container -->
    <div class="feedback-container">
      <div class="filter-panel">
        <form action="" method="GET" id="filterForm" class="filter-controls">
          <div class="filter-group">
            <label class="filter-label" for="sort">Sort By:</label>
            <select class="filter-select" name="sort" id="sort">
              <option value="newest" <?= $sortOrder === 'newest' ? 'selected' : '' ?>>Newest First</option>
              <option value="oldest" <?= $sortOrder === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="filter-label" for="rating">Min Rating:</label>
            <select class="filter-select" name="rating" id="rating">
              <option value="0" <?= $ratingFilter == 0 ? 'selected' : '' ?>>All Ratings</option>
              <option value="5" <?= $ratingFilter == 5 ? 'selected' : '' ?>>5 Stars</option>
              <option value="4" <?= $ratingFilter == 4 ? 'selected' : '' ?>>4 Stars</option>
              <option value="3" <?= $ratingFilter == 3 ? 'selected' : '' ?>>3 Stars</option>
              <option value="2" <?= $ratingFilter == 2 ? 'selected' : '' ?>>2 Stars</option>
              <option value="1" <?= $ratingFilter == 1 ? 'selected' : '' ?>>1 Star</option>
            </select>
          </div>
          <div class="filter-group">
            <button type="submit" class="filter-button">Apply Filters</button>
            <a href="visitorFeedback.php" class="reset-button">Reset</a>
          </div>
        </form>
      </div>

      <div class="feedback-scroll">
        <?php if ($totalFeedback > 0): ?>
          <?php foreach ($feedbackList as $feedback): ?>
            <div class="feedback-item">
              <div class="feedback-user">
                <div class="user-info">
                  <h4><?= htmlspecialchars($feedback['visitor_name']) ?></h4>
                </div>
              </div>
              <div class="feedback-message">
                <p><?= htmlspecialchars($feedback['comments']) ?></p>
              </div>
              <div class="feedback-meta">
                <div class="rating">
                  <?php
                    $fullStars = floor($feedback['rating']);
                    $emptyStars = floor(5 - $feedback['rating']);
                    echo str_repeat('<span class="star filled">★</span>', $fullStars);
                    echo str_repeat('<span class="star">☆</span>', $emptyStars);
                  ?>
                  <span class="rating-number"><?= number_format($feedback['rating'], 1) ?></span>
                </div>
                <div class="submitted">
                  Submitted: <span class="date"><?= date("F j, Y", strtotime($feedback['submitted_at'])) ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no-feedback">
            <h3>No feedback found</h3>
            <p>No feedback entries match your current filters.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

<script>
document.getElementById('sort').addEventListener('change', () => document.getElementById('filterForm').submit());
document.getElementById('rating').addEventListener('change', () => document.getElementById('filterForm').submit());
</script>

</body>
</html>