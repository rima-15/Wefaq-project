<?php
// Ensure no output before headers
ob_start();

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable displaying errors to prevent HTML output

include 'connection.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input === null) {
        throw new Exception("Invalid JSON input");
    }

    // Validate required fields
    $requiredFields = ['action', 'notification_ID', 'invitee_ID'];
    if (!isset($input['action']) || !in_array($input['action'], ['accept', 'decline'])) {
        throw new Exception("Invalid or missing action");
    }

    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Start transaction
    $conn->begin_transaction();
    
    // Get project_ID if not provided
    if (!isset($input['project_ID'])) {
        $stmt = $conn->prepare("SELECT related_ID FROM Notification WHERE notification_ID = ?");
        $stmt->bind_param("i", $input['notification_ID']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Notification not found");
        }
        $row = $result->fetch_assoc();
        $input['project_ID'] = $row['related_ID'];
    }

    // Process the invitation
    if ($input['action'] === 'accept') {
        // Add user to project
        $stmt = $conn->prepare("INSERT INTO projectteam (project_ID, user_ID) VALUES (?, ?)");
        $stmt->bind_param("ii", $input['project_ID'], $input['invitee_ID']);
        $stmt->execute();
        
        // Update invite status
        $stmt = $conn->prepare("UPDATE Invite SET status = 'accepted' 
                               WHERE invitee_ID = ? AND project_ID = ?");
        $stmt->bind_param("ii", $input['invitee_ID'], $input['project_ID']);
        $stmt->execute();
    } else {
        // Update invite status
        $stmt = $conn->prepare("UPDATE Invite SET status = 'declined' 
                               WHERE invitee_ID = ? AND project_ID = ?");
        $stmt->bind_param("ii", $input['invitee_ID'], $input['project_ID']);
        $stmt->execute();
    }
    
    // Update notification status
    $stmt = $conn->prepare("UPDATE Notification SET status = 'read' WHERE notification_ID = ?");
    $stmt->bind_param("i", $input['notification_ID']);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    
    $response = [
        'success' => true,
        'message' => "Invitation {$input['action']}ed successfully"
    ];
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && method_exists($conn, 'rollback')) {
        $conn->rollback();
    }
    
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Clean any output buffer and send JSON
ob_end_clean();
echo json_encode($response);
exit;