<?php
include 'connection.php'; // Database connection
include 'auth_check.php'; // Add centralized authentication check
// Fetch search term if provided
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// SQL query to get all users with average ratings and project count
$sql = "
    SELECT u.user_type ,u.user_ID, u.username, u.organization, u.gender,
           COALESCE(AVG(r.rating_value), 0) AS avg_rating,
           COUNT(DISTINCT pt.project_ID) AS project_count
    FROM user u
    LEFT JOIN rate r ON u.user_ID = r.rated_ID
    LEFT JOIN projectteam pt ON u.user_ID = pt.user_ID
";

// Add search filter if applicable
if (!empty($search)) {
    $sql .= " WHERE u.username LIKE ?";
}

$sql .= " GROUP BY u.user_ID ORDER BY avg_rating DESC";

$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bind_param("s", $searchParam);
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
                            <img src="images/avatar<?php echo $row['gender'] == 'F' ? 'F' : 'M'; ?>1.jpeg" alt="User" class="user-avatar">
                                <h3>
                                    <a href="profile.php?user_id=<?php echo $row['user_ID']; ?>"><?php echo htmlspecialchars($row['username']); ?></a>
                                </h3>
                            <p><?php echo htmlspecialchars($row['user_type']); ?>     <span>-<?php echo htmlspecialchars($row['organization']); ?></span></p>
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
