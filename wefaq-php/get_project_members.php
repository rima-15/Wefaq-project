<?php
header('Content-Type: application/json');
require 'connection.php';
session_start(); // Start session to access current user info
include 'auth_check.php'; // Add centralized authentication check
$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    // Validate project ID
    if (!isset($_GET['project_id']) || !is_numeric($_GET['project_id'])) {
        throw new Exception("Valid project ID is required");
    }

    $projectId = (int)$_GET['project_id'];
    $currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 3;
    
    // Verify project exists
    $projectCheck = mysqli_prepare($conn, "SELECT 1 FROM project WHERE project_id = ?");
    mysqli_stmt_bind_param($projectCheck, "i", $projectId);
    mysqli_stmt_execute($projectCheck);
    mysqli_stmt_store_result($projectCheck);
    
    if (mysqli_stmt_num_rows($projectCheck) === 0) {
        throw new Exception("Project not found");
    }
    mysqli_stmt_close($projectCheck);

    // Get members with additional info (excluding current user)
    $query = "SELECT 
                u.user_id as member_id, 
                u.username as name
              FROM projectTeam pm
              JOIN user u ON pm.user_id = u.user_id
              WHERE pm.project_id = ? 
              AND u.user_id != ?"; // Exclude current user
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Database error: " . mysqli_error($conn));
    }

    // Bind both project ID and current user ID
    mysqli_stmt_bind_param($stmt, "ii", $projectId, $currentUserId);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Query failed: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    $members = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $members[] = [
            'member_id' => (int)$row['member_id'],
            'name' => htmlspecialchars($row['name'])
        ];
    }

    // Changed this to not throw an error if only current user is in project
    $response = [
        'status' => 'success',
        'members' => $members,
        'count' => count($members)
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("[ERROR] get_project_members: " . $e->getMessage());
} finally {
    echo json_encode($response);
    
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($conn)) mysqli_close($conn);
    exit;
}
?>
