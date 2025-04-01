<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirect to login page if not logged in
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        // Store the intended URL to redirect back after login
        if (!empty($_SERVER['REQUEST_URI'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        }
        
        // Redirect to login page
        header("Location: login.php");
        exit();
    }
}

// Call the function to redirect if not logged in
redirectIfNotLoggedIn();
?>
