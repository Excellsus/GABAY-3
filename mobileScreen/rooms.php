<?php
// Enable error reporting for debugging (remove or adjust for production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Go up one directory to include connect_db.php from the parent folder
include __DIR__ . '/../connect_db.php'; // Include database connection

$offices = []; // Initialize as empty array
$error_message = null; // Variable to hold potential error messages

try {
    // Check if $connect is a valid PDO object
    if (!isset($connect) || !$connect) {
        throw new Exception("Database connection object (\$connect) is not valid. Check connect_db.php.");
    }

    // Fetch all office data, including the 'services' column
    $stmt = $connect->query("SELECT id, name, services FROM offices ORDER BY name ASC"); // Only fetch needed columns
    // Removed the duplicate query line above this one

    if ($stmt === false) {
        $errorInfo = $connect->errorInfo();
        throw new PDOException("Query failed: " . ($errorInfo[2] ?? 'Unknown error'));
    }
    $offices = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_message = "Error fetching offices: " . $e->getMessage();
    error_log("Error in rooms.php: " . $e->getMessage()); // Log error
}
// require_once '/var/www/html/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation View</title>
    <!-- Link to your CSS files -->
    <link rel="stylesheet" href="rooms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h2 class="section-title">Rooms</h2>
            <p class="section-subtitle">Explore the rooms available in the building.</p>
        </div>
        <!-- Example: Adding a search icon to the right -->
        <div class="header-actions"><i class="fas fa-search"></i></div>
    </header>

    <!-- Main content area -->
    <main class="content">
        <div class="rooms-grid">
            <?php if ($error_message): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php elseif (empty($offices)): ?>
                <p>No offices found in the database.</p>
            <?php else: ?>
                <?php foreach ($offices as $office): ?>
                    <a href="office_details.php?id=<?php echo htmlspecialchars($office['id']); ?>" class="room-card-link room-card"> <!-- Make the link the card -->
                        <i class="fas fa-door-open room-icon"></i> <!-- Or use a more specific icon if available -->
                        <div class="room-info">
                            <h3 class="room-name"><?php echo htmlspecialchars($office['name'] ?? 'N/A'); ?></h3>
                            <p class="room-desc"><?php echo htmlspecialchars($office['services'] ?? 'No services listed.'); ?></p> <!-- Display services -->
                            <!-- You could add location here too if desired -->
                         <!-- <p class="room-location">Location: <?php echo htmlspecialchars($office['location'] ?? 'N/A'); ?></p> -->
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>



      <!-- Bottom Navigation Section -->
      <nav class="bottom-nav" role="navigation" aria-label="Visitor navigation">
        <!-- Explore Link -->
        <a href="explore.php" aria-label="Explore">
          <i class="fas fa-map-marker-alt"></i>
          <span>Explore</span>
        </a>

        <!-- Rooms Link -->
        <a href="rooms.php" class="active" aria-label="Rooms">
          <i class="fas fa-building"></i>
          <span>Rooms</span>
        </a>

        <!-- About Link -->
        <a href="about.php" aria-label="About">
          <i class="fas fa-bars"></i>
          <span>About</span>
        </a>
      </nav>

</body>
</html>