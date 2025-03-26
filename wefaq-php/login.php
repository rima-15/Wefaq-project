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
                <form id="loginForm" onsubmit="return redirectToDashboard(event)" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input">
                            <input type="password" id="password" placeholder="Enter your password" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
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
    <script src="script.js"></script>
    <script>
        function redirectToDashboard(event) {
            event.preventDefault();
            window.location.href = 'dashboard.php';
            return false;
        }

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = event.currentTarget;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
