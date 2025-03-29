<?php
include 'connection.php';

// Initialize variables
$login_id = $password = '';
$error = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $login_id = trim($_POST['login_id']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($login_id) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check if the login ID is an email or username, and verify password
        $query = "SELECT user_ID, username, password, user_type FROM user WHERE email = ? OR username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $login_id, $login_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Start session and store user info
                session_start();
                $_SESSION['user_id'] = $user['user_ID'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect to dashboard
                header("Location: chatroom.php");
                exit();
            } else {
                $error = "Invalid username/email or password.";
            }
        } else {
            $error = "Invalid username/email or password.";
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Wefaq</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="auth-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-branding">
            <div class="auth-branding-content">
                <div class="branding-logo">
                    <img src="logoNOback.png" alt="Wefaq Logo">
                </div>
                <h1 class="branding-title">Welcome to Wefaq</h1>
                <p class="branding-description">Your platform for collaborative project management and community engagement.</p>
                
                <div class="branding-features">
                    <div class="branding-feature">
                        <i class="fas fa-project-diagram"></i>
                        <span>Manage projects efficiently</span>
                    </div>
                    <div class="branding-feature">
                        <i class="fas fa-users"></i>
                        <span>Connect with communities</span>
                    </div>
                    <div class="branding-feature">
                        <i class="fas fa-comments"></i>
                        <span>Seamless communication</span>
                    </div>
                </div>
            </div>
            <div class="auth-branding-footer">
                &copy; 2025 Wefaq. All rights reserved.
            </div>
        </div>
        
        <div class="auth-form-container">
            <div class="auth-card">
                <h2>Welcome Back</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="auth-form">
                    <div class="form-group">
                        <label for="login_id">Username or Email</label>
                        <input type="text" id="login_id" name="login_id" placeholder="Enter your username or email" value="<?php echo htmlspecialchars($login_id); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="wefaq-password-container">
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            <button type="button" class="wefaq-toggle-btn" aria-label="Toggle password visibility">
                                <i class="fas fa-eye" id="password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Sign In</button>
                </form>
                <p class="auth-footer">
                    Don't have an account? <a href="signup.php">Sign Up</a>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Setup password toggle
            setupPasswordToggle('password', 'password-toggle-icon');
            
            function setupPasswordToggle(inputId, iconId) {
                const toggleIcon = document.getElementById(iconId);
                const toggleBtn = toggleIcon.closest('.wefaq-toggle-btn');
                const passwordInput = document.getElementById(inputId);
                
                toggleBtn.addEventListener('click', function() {
                    // Toggle type
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle icon
                    if (type === 'password') {
                        toggleIcon.classList.remove('fa-eye-slash');
                        toggleIcon.classList.add('fa-eye');
                    } else {
                        toggleIcon.classList.remove('fa-eye');
                        toggleIcon.classList.add('fa-eye-slash');
                    }
                });
            }
        });
    </script>
</body>
</html>
