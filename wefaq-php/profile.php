<?php
include 'connection.php'; 
include 'auth_check.php'; // Add centralized authentication check


session_start();
$user_ID = $_SESSION['user_id'];
$fixed_skills = [
    'Design' => 'fa-paint-brush',
    'Programming' => 'fa-code',
    'Communication Skills' => 'fa-users',
    'Work Quality' => 'fa-thumbs-up',
    'Adaptability to feedback' => 'fa-people-arrows',
    'Time Management' => 'fa-clock'
];

$user = [];
$skill_ratings = []; 
$custom_styles = "";

try {
    $query = "SELECT username, gender, user_type, organization, bio, email, phone_num FROM User WHERE user_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_ID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc() ?? [];

    $sql_skills = "
        SELECT s.skill_name, ROUND(AVG(r.rating_value), 2) AS avg_rating
        FROM Rate r
        JOIN Skill s ON r.skill_ID = s.skill_ID
        WHERE r.rated_ID = ?
        GROUP BY s.skill_ID
    ";
    
    $stmt = $conn->prepare($sql_skills);
    $stmt->bind_param("i", $user_ID);
    
    if ($stmt->execute()) {
        $skills_result = $stmt->get_result();
        
        if ($skills_result && $skills_result->num_rows > 0) {
            while ($skill = $skills_result->fetch_assoc()) {
                $skill_ratings[$skill['skill_name']] = $skill['avg_rating'];
            }
        }
    }
    
    // Generate custom styles for the skills that exist in both sets
    $skill_index = 1;
    foreach ($fixed_skills as $skill_name => $icon) {
        $percentage = 0; // Default to 0 if no rating exists
        
        // Check if this skill exists in ratings
        if (isset($skill_ratings[$skill_name])) {
            // Convert 1-5 rating to percentage (1=20%, 5=100%)
            $percentage = min(100, max(0, $skill_ratings[$skill_name] * 20));
        }
        
        $skill_class = "skill" . $skill_index;
        $custom_styles .= ".$skill_class { width: {$percentage}% !important; }\n";
        $skill_index++;
    }
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Project Hub</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar/dist/simplebar.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        <?php echo $custom_styles ?>
        .avatar {
            --avatar-size: 110px;
            margin-right: 0px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar will be loaded here -->
        <main class="main-content">
            <!-- Header will be loaded here -->
            <div class="profilePage-content">
                <div class="profilePage-info">
                    
                    <div class="profilePage-header Profilecard">
                        <div class="Profilecard__img"><svg xmlns="http://www.w3.org/2000/svg" width="100%"><rect fill="#ffffff" width="540" height="450"></rect><defs><linearGradient id="a" gradientUnits="userSpaceOnUse" x1="0" x2="0" y1="0" y2="100%" gradientTransform="rotate(222,648,379)"><stop offset="0" stop-color="#ffffff"></stop><stop offset="1" stop-color="#9096DE"></stop></linearGradient><pattern patternUnits="userSpaceOnUse" id="b" width="300" height="250" x="0" y="0" viewBox="0 0 1080 900"><g fill-opacity="0.5"><polygon fill="#444" points="90 150 0 300 180 300"></polygon><polygon points="90 150 180 0 0 0"></polygon><polygon fill="#AAA" points="270 150 360 0 180 0"></polygon><polygon fill="#DDD" points="450 150 360 300 540 300"></polygon><polygon fill="#999" points="450 150 540 0 360 0"></polygon><polygon points="630 150 540 300 720 300"></polygon><polygon fill="#DDD" points="630 150 720 0 540 0"></polygon><polygon fill="#444" points="810 150 720 300 900 300"></polygon><polygon fill="#FFF" points="810 150 900 0 720 0"></polygon><polygon fill="#DDD" points="990 150 900 300 1080 300"></polygon><polygon fill="#444" points="990 150 1080 0 900 0"></polygon><polygon fill="#DDD" points="90 450 0 600 180 600"></polygon><polygon points="90 450 180 300 0 300"></polygon><polygon fill="#666" points="270 450 180 600 360 600"></polygon><polygon fill="#AAA" points="270 450 360 300 180 300"></polygon><polygon fill="#DDD" points="450 450 360 600 540 600"></polygon><polygon fill="#999" points="450 450 540 300 360 300"></polygon><polygon fill="#999" points="630 450 540 600 720 600"></polygon><polygon fill="#FFF" points="630 450 720 300 540 300"></polygon><polygon points="810 450 720 600 900 600"></polygon><polygon fill="#DDD" points="810 450 900 300 720 300"></polygon><polygon fill="#AAA" points="990 450 900 600 1080 600"></polygon><polygon fill="#444" points="990 450 1080 300 900 300"></polygon><polygon fill="#222" points="90 750 0 900 180 900"></polygon><polygon points="270 750 180 900 360 900"></polygon><polygon fill="#DDD" points="270 750 360 600 180 600"></polygon><polygon points="450 750 540 600 360 600"></polygon><polygon points="630 750 540 900 720 900"></polygon><polygon fill="#444" points="630 750 720 600 540 600"></polygon><polygon fill="#AAA" points="810 750 720 900 900 900"></polygon><polygon fill="#666" points="810 750 900 600 720 600"></polygon><polygon fill="#999" points="990 750 900 900 1080 900"></polygon><polygon fill="#999" points="180 0 90 150 270 150"></polygon><polygon fill="#444" points="360 0 270 150 450 150"></polygon><polygon fill="#FFF" points="540 0 450 150 630 150"></polygon><polygon points="900 0 810 150 990 150"></polygon><polygon fill="#222" points="0 300 -90 450 90 450"></polygon><polygon fill="#FFF" points="0 300 90 150 -90 150"></polygon><polygon fill="#FFF" points="180 300 90 450 270 450"></polygon><polygon fill="#666" points="180 300 270 150 90 150"></polygon><polygon fill="#222" points="360 300 270 450 450 450"></polygon><polygon fill="#FFF" points="360 300 450 150 270 150"></polygon><polygon fill="#444" points="540 300 450 450 630 450"></polygon><polygon fill="#222" points="540 300 630 150 450 150"></polygon><polygon fill="#AAA" points="720 300 630 450 810 450"></polygon><polygon fill="#666" points="720 300 810 150 630 150"></polygon><polygon fill="#FFF" points="900 300 810 450 990 450"></polygon><polygon fill="#999" points="900 300 990 150 810 150"></polygon><polygon points="0 600 -90 750 90 750"></polygon><polygon fill="#666" points="0 600 90 450 -90 450"></polygon><polygon fill="#AAA" points="180 600 90 750 270 750"></polygon><polygon fill="#444" points="180 600 270 450 90 450"></polygon><polygon fill="#444" points="360 600 270 750 450 750"></polygon><polygon fill="#999" points="360 600 450 450 270 450"></polygon><polygon fill="#666" points="540 600 630 450 450 450"></polygon><polygon fill="#222" points="720 600 630 750 810 750"></polygon><polygon fill="#FFF" points="900 600 810 750 990 750"></polygon><polygon fill="#222" points="900 600 990 450 810 450"></polygon><polygon fill="#DDD" points="0 900 90 750 -90 750"></polygon><polygon fill="#444" points="180 900 270 750 90 750"></polygon><polygon fill="#FFF" points="360 900 450 750 270 750"></polygon><polygon fill="#AAA" points="540 900 630 750 450 750"></polygon><polygon fill="#FFF" points="720 900 810 750 630 750"></polygon><polygon fill="#222" points="900 900 990 750 810 750"></polygon><polygon fill="#222" points="1080 300 990 450 1170 450"></polygon><polygon fill="#FFF" points="1080 300 1170 150 990 150"></polygon><polygon points="1080 600 990 750 1170 750"></polygon><polygon fill="#666" points="1080 600 1170 450 990 450"></polygon><polygon fill="#DDD" points="1080 900 1170 750 990 750"></polygon></g></pattern></defs><rect x="0" y="0" fill="url(#a)" width="100%" height="100%"></rect><rect x="0" y="0" fill="url(#b)" width="100%" height="100%"></rect></img></div>
                        <div id="dynamic-avatar" class="profilePage-image-container Profilecard__avatar">
                            <!--<img  src="images/avatarF1.jpeg">--><circle cx="64" cy="64" fill="#F3E07A" r="60"></circle><circle cx="64" cy="64" fill="#f85565" opacity=".4" r="48"></circle><path d="m64 14a32 32 0 0 1 32 32v41a6 6 0 0 1 -6 6h-52a6 6 0 0 1 -6-6v-41a32 32 0 0 1 32-32z" fill="#7f3838"></path><path d="m62.73 22h2.54a23.73 23.73 0 0 1 23.73 23.73v42.82a4.45 4.45 0 0 1 -4.45 4.45h-41.1a4.45 4.45 0 0 1 -4.45-4.45v-42.82a23.73 23.73 0 0 1 23.73-23.73z" fill="#393c54" opacity=".4"></path><circle cx="89" cy="65" fill="#fbc0aa" r="7"></circle><path d="m64 124a59.67 59.67 0 0 0 34.69-11.06l-3.32-9.3a10 10 0 0 0 -9.37-6.64h-43.95a10 10 0 0 0 -9.42 6.64l-3.32 9.3a59.67 59.67 0 0 0 34.69 11.06z" fill="#4bc190"></path><path d="m45 110 5.55 2.92-2.55 8.92a60.14 60.14 0 0 0 9 1.74v-27.08l-12.38 10.25a2 2 0 0 0 .38 3.25z" fill="#356cb6" opacity=".3"></path><path d="m71 96.5v27.09a60.14 60.14 0 0 0 9-1.74l-2.54-8.93 5.54-2.92a2 2 0 0 0 .41-3.25z" fill="#356cb6" opacity=".3"></path><path d="m57 123.68a58.54 58.54 0 0 0 14 0v-25.68h-14z" fill="#fff"></path><path d="m64 88.75v9.75" fill="none" stroke="#fbc0aa" stroke-linecap="round" stroke-linejoin="round" stroke-width="14"></path><circle cx="39" cy="65" fill="#fbc0aa" r="7"></circle><path d="m64 91a25 25 0 0 1 -25-25v-16.48a25 25 0 1 1 50 0v16.48a25 25 0 0 1 -25 25z" fill="#ffd8c9"></path><path d="m91.49 51.12v-4.72c0-14.95-11.71-27.61-26.66-28a27.51 27.51 0 0 0 -28.32 27.42v5.33a2 2 0 0 0 2 2h6.81a8 8 0 0 0 6.5-3.33l4.94-6.88a18.45 18.45 0 0 1 1.37 1.63 22.84 22.84 0 0 0 17.87 8.58h13.45a2 2 0 0 0 2.04-2.03z" fill="#bc5b57"></path><path d="m62.76 36.94c4.24 8.74 10.71 10.21 16.09 10.21h5" style="fill:none;stroke-linecap:round;stroke:#fff;stroke-miterlimit:10;stroke-width:2;opacity:.1"></path><path d="m71 35c2.52 5.22 6.39 6.09 9.6 6.09h3" style="fill:none;stroke-linecap:round;stroke:#fff;stroke-miterlimit:10;stroke-width:2;opacity:.1"></path><circle cx="76" cy="62.28" fill="#515570" r="3"></circle><circle cx="52" cy="62.28" fill="#515570" r="3"></circle><ellipse cx="50.42" cy="69.67" fill="#f85565" opacity=".1" rx="4.58" ry="2.98"></ellipse><ellipse cx="77.58" cy="69.67" fill="#f85565" opacity=".1" rx="4.58" ry="2.98"></ellipse><g fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="m64 67v4" stroke="#fbc0aa" stroke-width="4"></path><path d="m55 56h-9.25" opacity=".2" stroke="#515570" stroke-width="2"></path><path d="m82 56h-9.25" opacity=".2" stroke="#515570" stroke-width="2"></path></g><path d="m64 84c5 0 7-3 7-3h-14s2 3 7 3z" fill="#f85565" opacity=".4"></path><path d="m65.07 78.93-.55.55a.73.73 0 0 1 -1 0l-.55-.55c-1.14-1.14-2.93-.93-4.27.47l-1.7 1.6h14l-1.66-1.6c-1.34-1.4-3.13-1.61-4.27-.47z" fill="#f85565"></path></img>
                        </div >
                        <!-- comment <h2>User Profile</h2>-->
                    </div>
                    <div class="profileMainContent">
                    <div class="profilePage-details">
                        <div class="info-group">
                            <label>Username</label>
                            <p><?php echo $user['username']?></p>
                        </div>
                        <div class="info-group">
                            <label>Email</label>
                            <p><?php echo $user['email']?></p>
                        </div>
                        <div class="info-group">
                            <label>Bio</label>
                            <p><?php echo $user['bio']?></p>
                        </div>
                        <div class="info-group">
                            <label>Type</label>
                            <p><?php echo $user['user_type']?></p>
                        </div>
                        
                        <div class="info-group">
                            <label>Organization</label>
                            <p><?php echo $user['organization']?></p>
                        </div>
                    </div>
                        <div class="profileSkills">
                            <label>Skills</label>
                            <div class="skills">
                                <p><i class="fas fa-paint-brush"></i> Design</p>
                                <div class="skill-bar">
                                    <div class="skill1"></div>
                                </div>
                                <p><i class="fas fa-code"></i> Programming</p>
                                <div class="skill-bar">
                                    <div class="skill2"></div>
                                </div>
                                <p> <i class="fas fa-users"></i> Communication Skills</p>
                                <div class="skill-bar">
                                    <div class="skill3"></div>
                                </div>
                                <p> <i class="fas fa-thumbs-up"></i> Work Quality</p>
                                <div class="skill-bar">
                                    <div class="skill4"></div>
                                </div>
                                <p> <i class="fas fa-people-arrows"></i> Adaptability to feedback</p>
                                <div class="skill-bar">
                                    <div class="skill5"></div>
                                </div>
                                <p><i class="fas fa-clock"></i> Time Management</p>
                                <div class="skill-bar">
                                    <div class="skill6"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->

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
    <script src="js/components.js"></script>
    <script src="script.js"></script>
    <script>
    // Call the avatar function after page loads
    document.addEventListener('DOMContentLoaded', function() {
        const username = "<?php echo addslashes($user['username']); ?>";
        if (username && typeof generateAvatar === 'function') {
            const avatarHtml = generateAvatar(username, '--profile');
            document.getElementById('dynamic-avatar').innerHTML = avatarHtml;
        }
    });
</script>
</body>
</html>
