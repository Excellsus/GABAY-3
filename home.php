<?php
include 'connect_db.php';

// Total Visitors (QR Scans Count)
$visitorStmt = $connect->prepare("SELECT COUNT(*) FROM qr_scan_logs");
$visitorStmt->execute();
$totalVisitors = $visitorStmt->fetchColumn();

// Most Visited Offices (Top 5 based on QR scans)
$topOfficesStmt = $connect->prepare("
    SELECT o.name, COUNT(l.id) as scan_count
    FROM qr_scan_logs l
    JOIN offices o ON o.id = l.office_id
    GROUP BY o.id
    ORDER BY scan_count DESC
    LIMIT 5
");
$topOfficesStmt->execute();
$topOffices = $topOfficesStmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly Visitor Log (Daily, Weekly, Monthly counts)
$dailyStmt = $connect->prepare("SELECT COUNT(*) FROM qr_scan_logs WHERE DATE(check_in_time) = CURDATE()");
$weeklyStmt = $connect->prepare("SELECT COUNT(*) FROM qr_scan_logs WHERE WEEK(check_in_time) = WEEK(CURDATE()) AND YEAR(check_in_time) = YEAR(CURDATE())");
$monthlyStmt = $connect->prepare("SELECT COUNT(*) FROM qr_scan_logs WHERE MONTH(check_in_time) = MONTH(CURDATE()) AND YEAR(check_in_time) = YEAR(CURDATE())");

$dailyStmt->execute();
$weeklyStmt->execute();
$monthlyStmt->execute();

$dailyCount = $dailyStmt->fetchColumn();
$weeklyCount = $weeklyStmt->fetchColumn();
$monthlyCount = $monthlyStmt->fetchColumn();

// Feedback Rating (Average and Total)
$ratingStmt = $connect->prepare("SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS total_reviews FROM feedback WHERE rating IS NOT NULL");
$ratingStmt->execute();
$feedbackData = $ratingStmt->fetch(PDO::FETCH_ASSOC);
$avgRating = $feedbackData['avg_rating'] ?? '0.0';
$totalReviews = $feedbackData['total_reviews'] ?? 0;
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>GABAY Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="home.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      <a href="home.php" class="active">Dashboard</a>
      <a href="officeManagement.php">Office Management</a>
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
          <li><a href="home.php" class="active">Dashboard</a></li>
          <li><a href="officeManagement.php">Office Management</a></li>
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
          <h2>Dashboard Overview</h2>
          <p>Welcome back, Admin. Here's what's happening today.</p>
        </div>
      </header>
      <section class="cards">
  <div class="card green">
    <div class="card-left">
      <p>Total Visitors</p>
      <h3><?php echo $monthlyCount; ?></h3>
      <span class="growth">
        <svg class="growth-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
        This Month
      </span>
    </div>
    <div class="card-right">
      <svg class="icon large" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>
    </div>
  </div>

  <div class="card yellow">
    <div class="card-left">
      <p>Active Offices</p>
      <h3>
        <?php
          $officeCountStmt = $connect->query("SELECT COUNT(*) FROM offices WHERE status = 'active'");
          echo $officeCountStmt->fetchColumn();
        ?>
      </h3>
      <span class="growth">
        <svg class="growth-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
        Updated live
      </span>
    </div>
    <div class="card-right">
      <svg class="icon large" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
      </svg>
    </div>
  </div>

  <div class="card purple">
    <div class="card-left">
      <p>Feedback Rating</p>
      <h3><?php echo $avgRating; ?></h3>
      <span class="rating">
        <svg class="star-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z" />
        </svg>
        from <?php echo $totalReviews; ?> reviews
      </span>
    </div>
    <div class="card-right">
      <svg class="icon large" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </div>
  </div>
</section>

<section class="content-area">
  <!-- Most Visited Offices Chart -->
  <div class="activity-panel">
    <div class="panel-header">
      <h3>Most Visited Offices</h3>
    </div>
    <canvas id="topOfficesChart" height="250"></canvas>
  </div>

  <!-- Monthly Visitor Log Chart -->
  <div class="actions-panel">
    <h3>Monthly Visitor Log</h3>
    <canvas id="visitorLogChart" height="250"></canvas>
  </div>
</section>

    </main>

  </div>
  <script>
  const topOffices = <?php echo json_encode($topOffices); ?>;
  const dailyCount = <?php echo $dailyCount; ?>;
  const weeklyCount = <?php echo $weeklyCount; ?>;
  const monthlyCount = <?php echo $monthlyCount; ?>;
</script>

<script>
  // Top Offices Chart
  const officeNames = topOffices.map(office => office.name);
  const scanCounts = topOffices.map(office => office.scan_count);

  const topOfficesChart = new Chart(document.getElementById('topOfficesChart'), {
    type: 'bar',
    data: {
      labels: officeNames,
      datasets: [{
        label: 'QR Scans',
        data: scanCounts,
        backgroundColor: [
        'red', 'green', 'blue', 'purple', 'orange'
      ],
        borderRadius: 8
      }]
    },
    options: {
      responsive: true,
      indexAxis: 'y',
      plugins: {
        legend: { display: false },
        title: {
          display: false
        }
      },
      scales: {
        x: {
          beginAtZero: true
        }
      }
    }
  });

  // Monthly Visitor Log Chart
  const visitorLogChart = new Chart(document.getElementById('visitorLogChart'), {
    type: 'bar',
    data: {
      labels: ['Today', 'This Week', 'This Month'],
      datasets: [{
        label: 'Visitors',
        data: [dailyCount, weeklyCount, monthlyCount],
        backgroundColor: ['#4CAF50', '#FFC107', '#673AB7'],
        borderRadius: 8
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        title: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
</script>
</body>
</html>
