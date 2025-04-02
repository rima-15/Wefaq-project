<?php
session_start();

// Security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Validate session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

require_once 'connection.php'; // Adjust path as needed

try {
    $stmt = $conn->prepare("SELECT user_ID, username, email FROM User WHERE user_ID = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc() ?: ['error' => 'User not found']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>