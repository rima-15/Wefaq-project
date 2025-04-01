<?php
require 'connection.php';
include 'auth_check.php'; // Add centralized authentication check
session_start();

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

if (isset($_GET['file_id'])) {
    $file_id = (int)$_GET['file_id'];
    $user_id = $_SESSION['user_id'];
    
    // Verify user has access to this file
    $stmt = $conn->prepare("
        SELECT f.* 
        FROM file f
        JOIN projectteam pt ON f.project_ID = pt.project_ID
        WHERE f.file_ID = ? AND pt.user_ID = ?
    ");
    $stmt->bind_param("ii", $file_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $file = $result->fetch_assoc();
        
        // Check if file exists in uploads folder
        $file_path = 'uploads/' . $file_id . '_' . $file['file_name'];
        if (file_exists($file_path)) {
            // Output file
            header('Content-Type: ' . $file['file_type']);
            header('Content-Length: ' . $file['file_size']);
            header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
            readfile($file_path);
            exit;
        } else {
            // File not found in uploads, maybe it's only in DB
            // You would need to implement this if you store files in DB
            header('HTTP/1.0 404 Not Found');
            echo "File not found";
        }
    } else {
        header('HTTP/1.0 403 Forbidden');
        echo "Access denied";
    }
} else {
    header('HTTP/1.0 400 Bad Request');
    echo "Invalid request";
}
?>

