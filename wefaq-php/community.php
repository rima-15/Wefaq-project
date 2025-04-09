<?php
include 'connection.php'; // Database connection
include 'auth_check.php'; // Add centralized authentication check

// Get current user ID from session
$current_user_id = $_SESSION['user_id'] ?? 0;

// Fetch search term if provided
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// SQL query to get all users with average ratings and project count (excluding current user)
$sql = "
    SELECT u.user_type, u.user_ID, u.username, u.organization, u.gender,
           COALESCE(AVG(r.rating_value), 0) AS avg_rating,
           COUNT(DISTINCT pt.project_ID) AS project_count
    FROM user u
    LEFT JOIN rate r ON u.user_ID = r.rated_ID
    LEFT JOIN projectteam pt ON u.user_ID = pt.user_ID
    WHERE u.user_ID != ?
";

// Add search filter if applicable
if (!empty($search)) {
    $sql .= " AND u.username LIKE ?";
}

$sql .= " GROUP BY u.user_ID ORDER BY avg_rating DESC";

$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bind_param("is", $current_user_id, $searchParam);
} else {
    $stmt->bind_param("i", $current_user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community - Project Management Platform</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar/dist/simplebar.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
   <style>
    .user-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 15px;
    border-radius: 12px;
    background-color: #f9f9f9;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.avatar-container {
   
    margin: 5px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.avatar-container svg {
    width: 100%;
    height: 100%;
}

    .user-card h3 {
        margin-top: 10px;
        margin-bottom: 5px;
    }
    
    .user-card p {
        margin: 5px 0;
    }
    
   .avatar.\--community {  /* Escape the double dash */
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 60px;
}


</style>
</head>
<body>
    <div class="dashboard-container">
        <main class="main-content">
            <div class="community-content">
                <div class="community-filters">
                    <div class="search-bar">
                        <form method="GET" style="display: flex; align-items: center; width: 100%;">
                            <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>" style="flex-grow: 1; padding: 10px; border: 1px solid #ecf0f1; border-radius: 8px;">
                            <button class="btn btn-primary" type="submit" style="margin-left: 5px; padding: 10px 12px; border-radius: 8px;">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="users-grid">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="user-card">
                            <div class="avatar-container" id="avatar-<?php echo $row['user_ID']; ?>" style=""></div>
                            <h3>
                                <a href="profile.php?user_id=<?php echo $row['user_ID']; ?>" style="color: black; text-decoration: none;"><?php echo htmlspecialchars($row['username']); ?></a>
                            </h3>
                            <p><?php echo htmlspecialchars($row['user_type']); ?> <span>- <?php echo htmlspecialchars($row['organization']); ?></span></p>
                            <p class="user-rating">⭐️ <strong><?php echo number_format($row['avg_rating'], 1); ?>/5</strong></p>
                            <p class="user-projects"><i class="fas fa-project-diagram"></i> <strong><?php echo $row['project_count']; ?> Projects</strong></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="js/components.js"></script>
    <script src="script.js"></script>
    <script>
    // Generate avatars after page loads
    document.addEventListener('DOMContentLoaded', function() {
        <?php 
        // Reset result pointer to loop through results again
        $result->data_seek(0); 
        while ($row = $result->fetch_assoc()): ?>
            if (typeof generateAvatar === 'function') {
                const avatarHtml = generateAvatar("<?php echo addslashes($row['username']); ?>", '--community');
                document.getElementById('avatar-<?php echo $row['user_ID']; ?>').innerHTML = avatarHtml;
            }
        <?php endwhile; ?>
    });
    </script>
    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <img src="logoNOback.png" alt="Logo">
            </div>
            <div class="footer-contact">
                <p><strong>Contacts:</strong></p>
                <p>0558944669</p>
                <p>wefaq@email.com</p>
            </div>
            <div class="footer-social">
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-whatsapp"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-x-twitter"></i></a>
            </div>
        </div>
    </footer>
</body>
</html>