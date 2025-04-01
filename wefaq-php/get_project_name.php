<?php
include 'connection.php';
include 'auth_check.php'; // Add centralized authentication check
if (isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];
    $query = "SELECT project_name FROM project WHERE project_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $project_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $project_name);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    echo json_encode(["project_name" => $project_name]);
}
?>
