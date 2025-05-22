<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include __DIR__ . '/../connect_db.php';

$office = null;
$error_message = null;
$office_id = null;

// 1. Get and Validate Office ID from URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $office_id = (int)$_GET['id'];
} else {
    $error_message = "Invalid or missing Office ID.";
}

// 2. Fetch Office Data if ID is valid
if ($office_id !== null) {
    try {
        if (!isset($connect) || !$connect) {
            throw new Exception("Database connection object (\$connect) is not valid.");
        }

        // Use prepared statement to prevent SQL injection
        $stmt = $connect->prepare("SELECT id, name, services FROM offices WHERE id = :id");
        $stmt->bindParam(':id', $office_id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the single office record
        $office = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$office) {
            $error_message = "Office not found.";
        }

    } catch (Exception $e) {
        $error_message = "Error fetching office details: " . $e->getMessage();
        error_log("Error in office_details.php: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Use office name in title if available -->
    <title><?php echo $office ? htmlspecialchars($office['name']) : 'Office Details'; ?> - Services</title>
    <link rel="stylesheet" href="office_details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-back">
            <a href="rooms.php" aria-label="Back to Rooms List"><i class="fas fa-arrow-left"></i></a>
        </div>
        <div class="header-content">
            <!-- Display office name in header -->
            <h2 class="section-title"><?php echo $office ? htmlspecialchars($office['name']) : 'Office Details'; ?></h2>
            <p class="section-subtitle">Services Offered</p>
        </div>
        <div class="header-actions"></div> <!-- Placeholder for potential future actions -->
    </header>

    <main class="content">
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php elseif ($office): ?>
            <div class="office-services">
                <p><?php echo nl2br(htmlspecialchars($office['services'] ?? 'No specific services listed for this office.')); ?></p>
            </div>
        <?php endif; ?>
    </main>

    <!-- No bottom nav here, keeping it simple for detail view -->
</body>
</html>