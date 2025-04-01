<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // Changed to 1 to see errors during development
include 'connection.php';

// Fetch skills from the database
$query0 = "SELECT * FROM skill";
$result0 = mysqli_query($conn, $query0);

$skills = [];
while ($row = mysqli_fetch_assoc($result0)) {
    $skills[] = $row;
}

$project_id = "1";
$user_ID = "3";

// Fetch the project name from the database
$query = "SELECT project_name FROM project WHERE project_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $project_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $project_name);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$query2 = "SELECT notification_ID, type, message, status, created_at, related_ID 
          FROM Notification 
          WHERE user_ID = ? 
          ORDER BY created_at DESC";

$stmt2 = $conn->prepare($query2);
$stmt2->bind_param("i", $user_ID);
$stmt2->execute();
$result2 = $stmt2->get_result();

$rate_notifications = [];
$invite_notifications = [];

while ($row = $result2->fetch_assoc()) {
    if ($row['type'] === 'rate') {
        $rate_notifications[] = $row;
    } elseif ($row['type'] === 'invite') {
        $invite_notifications[] = $row;
    }
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $units = [
        'y' => 'year',
        'm' => 'month',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];

    foreach ($units as $key => $text) {
        if ($diff->$key > 0) {
            return $diff->$key . ' ' . $text . ($diff->$key > 1 ? 's' : '') . ' ago';
        }
    }
    return 'just now';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox - Project Management Platform</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar/dist/simplebar.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .btn.rated-complete {
            background-color: #9aa4a5 !important;
            cursor: not-allowed;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .btn.rated-complete i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar will be loaded here -->
        <div id="sidebarContainer"></div>
        <main class="main-content">
            <!-- Header will be loaded here -->
            <header class="content-header">
                <div class="header-actions">
                    
                </div>
            </header>

            <div class="inbox-container">
                <!-- Invitation Notifications Section -->
                <div class="inbox-section">
                    <div class="section-header">
                        <h2><i class="fas fa-envelope"></i> Invitation Notifications</h2>
                        <span class="notification-count"><?php echo count($invite_notifications); ?></span>
                    </div>
                    <div class="messages-list">
                        <?php foreach ($invite_notifications as $notification) { ?>
                            <div class="message-item unread">
                                <div class="message-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <h3>Project Invitation</h3>
                                        <span class="message-time"><?php echo time_elapsed_string($notification['created_at']); ?></span>
                                    </div>
                                    <p><?php echo $notification['message']; ?></p>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary btn-sm AcceptInvite" onclick="acceptInvitation(<?php echo $notification['related_ID']; ?>)">Accept</button>
                                        <button class="btn btn-outline btn-sm">Decline</button>
                                    </div>
                                </div>                                           
                            </div>
                        <?php } ?> 
                    </div>
                </div>    
                <!-- Rating Notifications Section -->
                <div class="inbox-section">
                    <div class="section-header">
                        <h2><i class="fas fa-star"></i> Rating Notifications</h2>
                        <span class="notification-count"><?php echo count($rate_notifications); ?></span>
                    </div>
                    <div class="messages-list">
            <?php foreach ($rate_notifications as $notification): 
                $is_rated = ($notification['status'] === 'read' && $notification['type'] === 'rate');
            ?>
                <div class="message-item unread">
                    <div class="message-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <h3>Rate Project Members</h3>
                            <span class="message-time"><?php echo time_elapsed_string($notification['created_at']); ?></span>
                        </div>
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <button class="btn btn-primary btn-sm RateopenModal <?php echo $is_rated ? 'rated-complete' : '' ?>" 
                                data-project-id="<?php echo $notification['related_ID']; ?>"
                                <?php echo $notification['status'] === 'read' ? 'disabled' : ''; ?>>
                        <?php echo $notification['status'] === 'read' ? '<i class="fas fa-check"></i> Thank you' : 'Rate Members'; ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
                </div>
            </div>
            <div class="ratingModal">
                <div class="RateModal-content">
                    <div class="modal-header">
                        <div class="headerInbox-up">
                            <h3>Rate your team members in <strong><?php echo $project_name; ?></strong> project</h3>
                            <button class="RateClose">×</button>
                        </div>
                        <p id="processDetails"></p>
                    </div>
                    <div class="modal-body">
                        <form id="ratingForm" <!--onsubmit="submitRatings(event)"--> <!-- Form to submit ratings -->
                            <input type="hidden" name="rater_ID" value="">
                            <input type="hidden" id="related_ID_input" name="related_ID" value=""> <!-- Assuming project ID is 123 -->

                            <!-- Member ratings will be added here dynamically -->
                            <div class="memberRating" id="memberRatings"></div>

                            <!-- Buttons to control the flow -->
                            <button type="button" class="btn btn-primary " id="nextMemberBtn">Next</button>
                            <button type="submit" class="btn btn-primary " id="submitRatingsBtn" style="display: none;">Submit All Ratings</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="confirmationModal" style="display: none;">
                <div class="modal-content">
                    <h2>All Members Rated!</h2>
                    <p>Thank you for rating all the members.</p>
                    <button class="confirmClose btn btn-primary">OK</button>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/components.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set the page title in the header
            const pageTitle = document.getElementById('pageTitle');
            if (pageTitle) {
                pageTitle.textContent = 'Inbox';
            }
        });

        // Load sidebar
        $(document).ready(function() {
            $("#sidebarContainer").load("components/sidebar.html", function() {
                // After loading sidebar, set active state
                $(".nav-links li a").removeClass("active");
                $(".nav-links li a[href='inbox.html']").addClass("active");
            });
        });
    </script>
    <script>
        // Global variables
        const skills = <?php echo json_encode($skills); ?>;
        let members = [];
        let currentIndex = 0;
        let allRatings = {};
        let currentProjectId = null;

        // Initialize rating system
        function initializeRatingSystem(projectID) {
            console.log("[DEBUG] Initializing rating for project:", projectID);
            currentProjectId = projectID; // Store in global variable
    
            // Set the hidden input value directly
            document.getElementById('related_ID_input').value = projectID;
            
            // Clear any existing listeners
            const nextBtn = document.getElementById('nextMemberBtn');
            const newNextBtn = nextBtn.cloneNode(true);
            nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
            
            // Add new listener
            document.getElementById('nextMemberBtn').addEventListener('click', function() {
                if (currentIndex < members.length - 1) {
                    currentIndex++;
                    showMemberRating(currentIndex);
                } else {
                    submitRatings();
                }
            });

            // Show loading state
            document.getElementById('processDetails').innerHTML = 
                '<i class="fas fa-spinner fa-spin"></i> Loading team members...';
            document.querySelector(".ratingModal").style.display = "flex";
            
            // Set the related ID in the form
            document.getElementById('related_ID_input').value = projectID;
            
            // Fetch project members
            fetch(`get_project_members.php?project_id=${projectID}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    members = data.members || [];
                    allRatings = {};
                    currentIndex = 0;
                    
                    if (members.length === 0) {
                        throw new Error('No members found for this project');
                    }
                    
                    showMemberRating(currentIndex);
                })
                .catch(error => {
                    console.error("Error loading members:", error);
                    document.getElementById('processDetails').innerHTML = 
                        `Error loading team members: ${error.message}. <button onclick="initializeRatingSystem(${projectID})">Try Again</button>`;
                });
        }

        function showMemberRating(index) {
            const container = document.getElementById('memberRatings');
            container.innerHTML = '';
            
            if (index >= members.length) {
                submitRatings();
                return;
            }

            const member = members[index];
            document.getElementById('processDetails').innerHTML = 
                `Please rate <strong>${member.name}</strong>'s skills from 0 to 5.`;

            skills.forEach(skill => {
                const div = document.createElement('div');
                div.className = 'RateSlider-container';

                const label = document.createElement('label');
                
                // Create a span to show the rating value
                const ratingValueSpan = document.createElement('span');
                ratingValueSpan.className = 'rating-value';
                ratingValueSpan.textContent = `0`;  // Default to 0 initially
                label.textContent = skill.skill_name + ' : ';
                label.appendChild(ratingValueSpan); // Append the span with rating value
                
                const slider = document.createElement('input');
                slider.type = 'range';
                slider.min = 0;
                slider.max = 5;
                slider.value = (allRatings[member.member_id]?.[skill.skill_ID] !== undefined) 
               ? allRatings[member.member_id][skill.skill_ID] 
               : 0;
                slider.className = 'slider';
                
                slider.addEventListener('input', function() {
                    if (!allRatings[member.member_id]) {
                        allRatings[member.member_id] = {};
                    }
                    allRatings[member.member_id][skill.skill_ID] = parseInt(this.value);
                    ratingValueSpan.textContent = parseInt(this.value);
                });
                
                div.appendChild(label);
                div.appendChild(slider);
                container.appendChild(div);
            });

            const nextBtn = document.getElementById('nextMemberBtn');
            nextBtn.textContent = index < members.length - 1 ? 'Next' : 'Done';
        }

    async function submitRatings() {
    const btn = document.getElementById('nextMemberBtn');
    btn.disabled = true;
    const originalText = btn.textContent;
    btn.textContent = "Saving...";

    try {
        if (!currentProjectId) {
            throw new Error("No project selected for rating");
        }

        // Create a deep copy of allRatings to preserve zeros
        const processedRatings = JSON.parse(JSON.stringify(allRatings));

        // Ensure all skills are included for each member, even if not rated (set to 0)
        members.forEach(member => {
            if (!processedRatings[member.member_id]) {
                processedRatings[member.member_id] = {};
            }
            
            skills.forEach(skill => {
                // Explicitly check if the value is undefined (not set)
                if (processedRatings[member.member_id][skill.skill_ID] === undefined) {
                    processedRatings[member.member_id][skill.skill_ID] = 0;
                }
            });
        });

        const payload = {
            rater_ID: <?php echo $user_ID; ?>,
            related_ID: parseInt(currentProjectId),
            ratings: processedRatings
        };

        console.log("Final Payload:", JSON.stringify(payload, null, 2));

        const response = await fetch('save_ratings.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });

        const responseText = await response.text();
        console.log("Raw Response:", responseText);

        const result = JSON.parse(responseText);
        if (!response.ok || result.status !== 'success') {
            throw new Error(result.message || 'Failed to save ratings');
        }
        
        // Update all rate buttons for this project
        document.querySelectorAll(`button[data-project-id="${currentProjectId}"]`).forEach(btn => {
            btn.innerHTML = '<i class="fas fa-check"></i> Thank You';
            btn.classList.add('rated-complete');
            btn.disabled = true;
        });
        
        closeRatingModal();
        showConfirmationMessage();

    } catch (error) {
        console.error("Submission Error:", error);
        alert(`Error saving ratings: ${error.message}`);
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

        function closeRatingModal() {
            document.querySelector(".ratingModal").style.display = "none";
            currentIndex = 0;
            allRatings = {};
        }

        function showConfirmationMessage() {
            document.querySelector(".confirmationModal").style.display = "flex";
        }

        // Event delegation for modal buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('RateopenModal')) {
                const projectId = e.target.getAttribute('data-project-id');
                initializeRatingSystem(projectId);
            }
            
            if (e.target.classList.contains('RateClose')) {
                closeRatingModal();
            }
            
            if (e.target.classList.contains('confirmClose')) {
                document.querySelector(".confirmationModal").style.display = "none";
            }
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
