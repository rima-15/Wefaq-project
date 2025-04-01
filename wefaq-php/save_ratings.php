<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'connection.php';
include 'auth_check.php'; // Add centralized authentication check
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if (!$data || !isset($data['rater_ID'], $data['related_ID'], $data['ratings'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

$rater_ID = (int)$data['rater_ID'];
$related_ID = (int)$data['related_ID'];
$ratings = $data['ratings'];

try {
    mysqli_begin_transaction($conn);

    // 1. Save all ratings
    foreach ($ratings as $rated_ID => $skills) {
        $rated_ID = (int)$rated_ID;
        
        foreach ($skills as $skill_ID => $rating_value) {
            $skill_ID = (int)$skill_ID;
            $rating_value = (int)$rating_value;
            
            // UPSERT operation
            $query = "INSERT INTO Rate (rater_ID, rated_ID, skill_ID, related_ID, rating_value)
                      VALUES (?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE rating_value = ?";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "iiiidi", 
                $rater_ID, $rated_ID, $skill_ID, $related_ID, $rating_value,
                $rating_value
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to save rating: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
        }
    }

    // 2. Update notification status to 'read'
    $updateQuery = "UPDATE Notification 
                   SET status = 'read' 
                   WHERE user_ID = ? 
                   AND related_ID = ? 
                   AND type = 'rate'";
    
    $updateStmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, "ii", $rater_ID, $related_ID);
    
    if (!mysqli_stmt_execute($updateStmt)) {
        throw new Exception("Failed to update notification status: " . mysqli_error($conn));
    }
    
    // Check if any rows were affected
    $rowsAffected = mysqli_stmt_affected_rows($updateStmt);
    mysqli_stmt_close($updateStmt);

    mysqli_commit($conn);
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Ratings saved and notification updated',
        'notification_updated' => $rowsAffected > 0
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
