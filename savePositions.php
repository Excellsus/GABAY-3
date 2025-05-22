<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); // Set response type to JSON

try {
    include 'connect_db.php'; // Include database connection
    
    // Get the raw POST data
    $jsonData = file_get_contents('php://input');
    
    // Decode the JSON data
    $data = json_decode($jsonData, true);
    
    if (!$data || !isset($data['assignments'])) {
        throw new Exception("Invalid data format");
    }
    
    $assignments = $data['assignments'];
    
    // Log the received data (remove in production)
    error_log("Received assignments: " . print_r($assignments, true));
    
    // Start a transaction
    $connect->beginTransaction();
    
    // Prepare the update statement
    $updateStmt = $connect->prepare("UPDATE offices SET position_id = ? WHERE id = ?");
    
    $successCount = 0;
    
    foreach ($assignments as $assignment) {
        if (!isset($assignment['roomId']) || !isset($assignment['officeId'])) {
            continue; // Skip invalid entries
        }
        
        // Extract the room number from the roomId
        $roomId = $assignment['roomId'];
        $positionId = preg_replace('/[^0-9]/', '', $roomId); // Strip non-numeric characters
        $officeId = $assignment['officeId'];
        
        // Update the position_id for this office
        $updateStmt->execute([$positionId, $officeId]);
        $successCount++;
    }
    
    // Commit the transaction
    $connect->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => "$successCount office positions updated successfully"
    ]);
    
} catch (Exception $e) {
    // If there was an error, rollback any changes
    if (isset($connect) && $connect->inTransaction()) {
        $connect->rollBack();
    }
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Log the error
    error_log("Error in savePositions.php: " . $e->getMessage());
} 