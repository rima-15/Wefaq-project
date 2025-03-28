<?php
include 'connection.php';

// Initialize variables for form values
$username = $email = $phone = $gender = $role = $organization = $bio = '';
$error = '';
$success = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    // Convert gender to single character for database (char(1))
    $genderChar = ($gender === 'male') ? 'M' : (($gender === 'female') ? 'F' : '');
    $role = trim($_POST['role']); // user_type in DB
    $organization = trim($_POST['organization']);
    $bio = trim($_POST['bio']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validate data
    $errors = [];
    
    // Check for empty fields
    if (empty($username) || empty($email) || empty($phone) || empty($gender) || 
        empty($role) || empty($organization) || empty($bio) || empty($password)) {
        $errors[] = "All fields are required";
    }
    
    // Username validation - 4-20 chars, only letters, numbers, underscores, periods
    if (!empty($username)) {
        if (strlen($username) < 4 || strlen($username) > 20) {
            $errors[] = "Username must be between 4 and 20 characters long";
        }
        if (!preg_match('/^[A-Za-z0-9_.]+$/', $username)) {
            $errors[] = "Username can only contain letters, numbers, underscores, and periods";
        }
    }
    
    // Password validation - min 8 chars, one uppercase, one lowercase, one number, one special char
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must include at least one uppercase letter";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must include at least one lowercase letter";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must include at least one number";
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must include at least one special character";
        }
    }
    
    // Password confirmation
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    // If no validation errors, proceed with registration
    if (empty($errors)) {
        // Check if username or email already exists
        $checkQuery = "SELECT * FROM user WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username or email already exists";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user - updated to match actual DB structure
            $insertQuery = "INSERT INTO user (username,gender, user_type,organization, bio, email, phone_num, password) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ssssssss", $username, $genderChar, $role, $organization, $bio, $email, $phone, $hashedPassword);
            
            if ($stmt->execute()) {
                // Start session to store user information
                session_start();
                
                // Get the user_id of the newly created user
                $userId = $stmt->insert_id;
                
                // Store user data in session
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['user_type'] = $role;
                
                $success = "Account created successfully!";
                // Redirect to dashboard after 2 seconds
                header("refresh:2;url=dashboard.php");
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
    } else {
        // Join all errors with line breaks
        $error = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Wefaq</title>
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
                <h1 class="branding-title">Join Wefaq Today</h1>
                <p class="branding-description">Create an account and start collaborating on projects, connecting with communities, and making an impact.</p>
                
                <div class="branding-features">
                    <div class="branding-feature">
                        <i class="fas fa-project-diagram"></i>
                        <span>Create and join projects</span>
                    </div>
                    <div class="branding-feature">
                        <i class="fas fa-users"></i>
                        <span>Build your professional network</span>
                    </div>
                    <div class="branding-feature">
                        <i class="fas fa-lightbulb"></i>
                        <span>Unlock new opportunities</span>
                    </div>
                </div>
            </div>
            <div class="auth-branding-footer">
                &copy; 2025 Wefaq. All rights reserved.
            </div>
        </div>
        
        <div class="auth-form-container">
            <div class="auth-card">
                <h2>Create Your Account</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form id="signupForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="auth-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Choose a username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Gender</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="gender" value="male" <?php if ($gender === 'male') echo 'checked'; ?> required>
                                <span>Male</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="gender" value="female" <?php if ($gender === 'female') echo 'checked'; ?> required>
                                <span>Female</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="" disabled <?php if (empty($role)) echo 'selected'; ?>>Select your role</option>
                            <option value="student" <?php if ($role === 'student') echo 'selected'; ?>>Student</option>
                            <option value="professional" <?php if ($role === 'professional') echo 'selected'; ?>>Professional</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="organization">Organization</label>
                        <input type="text" id="organization" name="organization" placeholder="Enter your organization" value="<?php echo htmlspecialchars($organization); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="bio">About Yourself</label>
                        <input type="text" id="bio" name="bio" placeholder="Enter your bio" value="<?php echo htmlspecialchars($bio); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="wefaq-password-container">
                            <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                            <button type="button" class="wefaq-toggle-btn" aria-label="Toggle password visibility">
                                <i class="fas fa-eye" id="password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <div class="wefaq-password-container">
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                            <button type="button" class="wefaq-toggle-btn" aria-label="Toggle password visibility">
                                <i class="fas fa-eye" id="confirm-password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Account</button>
                </form>
                <p class="auth-footer">
                    Already have an account? <a href="login.php">Log In</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Setup password toggles with the new class names
            setupPasswordToggle('password', 'password-toggle-icon');
            setupPasswordToggle('confirmPassword', 'confirm-password-toggle-icon');
            
            function setupPasswordToggle(inputId, iconId) {
                const toggleBtn = document.getElementById(iconId).closest('.wefaq-toggle-btn');
                const passwordInput = document.getElementById(inputId);
                const toggleIcon = document.getElementById(iconId);
                
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
