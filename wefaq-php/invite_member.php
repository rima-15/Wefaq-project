<?php
header('Content-Type: application/json');
session_start();
require 'connection.php';

// Enable detailed error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log the raw input
file_put_contents('invite_log.txt', date('Y-m-d H:i:s') . " - Input: " . file_get_contents('php://input') . "\n", FILE_APPEND);

$invitation_messages = [
    "You've been invited to collaborate on project: %s",
    "%s wants you to join their project team!",
    "New project invitation: %s",
    "Join %s's project and start collaborating!",
    "Exciting opportunity! You're invited to project: %s",
    "Team up on project: %s - invitation waiting!",
    "%s is requesting your skills on their project"
];

try {
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    file_put_contents('invite_log.txt', "Decoded: " . print_r($data, true) . "\n", FILE_APPEND);

    // Validate session
    if (!isset($_SESSION['user_id'])) {
        file_put_contents('invite_log.txt', "Session validation failed\n", FILE_APPEND);
        throw new Exception('Session expired. Please login again.');
    }

    // Validate input
    if (empty($data['project_id']) || empty($data['username'])) {
        file_put_contents('invite_log.txt', "Missing fields: " . print_r($data, true) . "\n", FILE_APPEND);
        throw new Exception('Missing required fields');
    }

    $project_id = (int)$data['project_id'];
    $username = trim($data['username']);

    // Check if user exists
    $stmt = $conn->prepare("SELECT user_ID FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        file_put_contents('invite_log.txt', "User not found: $username\n", FILE_APPEND);
        throw new Exception('User not found');
    }

    // Check if already in project
    $stmt = $conn->prepare("SELECT 1 FROM projectteam WHERE project_ID = ? AND user_ID = ?");
    $stmt->bind_param("ii", $project_id, $user['user_ID']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        file_put_contents('invite_log.txt', "User already in project\n", FILE_APPEND);
        throw new Exception('User is already in project');
    }

    // Insert invitation
    $stmt = $conn->prepare("INSERT INTO invite SET 
        project_ID = ?,
        inviter_ID = ?,
        invitee_ID = ?,
        status = 'pending',
        timestamp = NOW()");
    
    $stmt->bind_param("iii", $project_id, $_SESSION['user_id'], $user['user_ID']);
    
    if (!$stmt->execute()) {
        file_put_contents('invite_log.txt', "SQL Error: " . $stmt->error . "\n", FILE_APPEND);
        throw new Exception('Database error');
    }
    
    // After successful invitation insertion, replace the notification code with:
    $project_stmt = $conn->prepare("SELECT project_name FROM project WHERE project_ID = ?");    
    $project_stmt->bind_param("i", $project_id);
    $project_stmt->execute();
    $project = $project_stmt->get_result()->fetch_assoc();

    $random_message = $invitation_messages[array_rand($invitation_messages)];
    $notification_msg = sprintf($random_message, $project['project_name']);

    $stmt = $conn->prepare("INSERT INTO notification SET 
        user_id = ?,
        related_id = ?,
        type = 'invite',
        status = 'unread',
        created_at = NOW(),
        message = ?");
    $stmt->bind_param("iis", $user['user_ID'], $project_id, $notification_msg);
    $stmt->execute();

    file_put_contents('invite_log.txt', "Invite successful\n", FILE_APPEND);
    echo json_encode([
        'status' => 'success',
        'message' => 'Invitation sent successfully'
    ]);

} catch (Exception $e) {
    file_put_contents('invite_log.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}