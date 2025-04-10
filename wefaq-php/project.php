<?php
session_start();
require 'connection.php';
include 'auth_check.php';

// 1. Check Authentication (already handled in auth_check.php)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Validate Project ID Parameter
if (!isset($_GET['project_ID']) || !ctype_digit($_GET['project_ID'])) {
    header("Location: dashboard.php");
    exit();
}

$project_id = (int)$_GET['project_ID'];

// 3. Check Project Existence and User Membership
$stmt = $conn->prepare("
    SELECT p.* 
    FROM project p
    JOIN projectteam pt ON p.project_ID = pt.project_ID
    WHERE p.project_ID = ? AND pt.user_ID = ?
    LIMIT 1
");
$stmt->bind_param("ii", $project_id, $user_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    // Log unauthorized access attempt
    error_log("Unauthorized project access attempt - User: $user_id, Project: $project_id");
    
    header("Location: dashboard.php");
    exit();
}

// 4. Check if user is leader (for UI elements that should only be visible to leaders)
$is_leader = ($project['leader_ID'] == $user_id);

// 5. Store project ID in session for chatroom.php access
$_SESSION['current_project_id'] = $project_id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <meta name="user-id" content="<?php echo $user_id; ?>">
    <meta name="project-id" content="<?php echo $project_id; ?>">
    <link rel="icon" href="logoHand.png" type="image/png">
    <title><?php echo htmlspecialchars($project['project_name']); ?> - Wefaq</title>    <link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar/dist/simplebar.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"><!-- comment -->    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="script.js"></script>
    <style>

    </style>

</head>
<body>

<div class="dashboard-container">
    <main class="main-content">
        <div class="project-content">
            <div class="project-description">
                <div class="project-description-header">

                <h3>  </h3>
                <button class="btn-icon edit project-edit-btns" id="editProjectDescription" title="Edit Tasks">
                    <i class="fas fa-edit"></i>
                </button>
                </div>
                <p id="project-description-content">   
                </p>
            </div>

            <!-- Improved Tabs -->
            <div class="project-tabs">
                <button class="tab-btn active" data-tab="dashboard">Dashboard</button>
                <button class="tab-btn" data-tab="tasks">Tasks</button>
                <button class="tab-btn" data-tab="files">Files</button>
            </div>

            <div class="tab-content">
                <div id="dashboard" class="tab-pane active">
                    <div class="dashboard-grid">
                        <!-- Work Distribution Chart -->
                        <div class="card chart-box">
                            <h3>Work Distribution</h3>
                            <canvas id="workDistributionChart"></canvas>
                        </div>

                        <!-- Task Completion Progress Chart -->
                        <div class="card chart-box">
                            <h3>Task Completion Progress</h3>
                            <canvas id="taskCompletionChart"></canvas>
                        </div>

                        <!-- Remaining Time to Deadline Chart -->
                        <div class="card chart-box">
                            <h3 >Remaining Time to Deadline</h3>
                            <canvas   width="300" height="200" id="deadlineChart"></canvas>
                            
                        </div>

                        <!-- Team Member Contribution Chart (Horizontal Bar Chart) -->
                        <div class="card chart-box">
                            <h3>Team Member Contribution</h3>
                            <canvas id="teamContributionChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tasks Tab -->
                <div id="tasks" class="tab-pane">
                    <div class="tasks-header">
                        <h2>Project Tasks</h2>
                        <div class="task-actions">
                          
                            <button class="btn btn-primary" id="addTaskBtn" onclick="openGenericModal('addTaskModal')">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                    <div class="tasks-list">
                        <table class="tasks-table">
                            <thead>
                            <tr>
                                <th>Task Name</th>
                                <th>Task Description</th>
                                <th>Assigned Member</th>
                                <th>Status</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Design User Interface</td>
                                <td class='taskDescription'>Create a visually intuitive and user-friendly layout for the system.</td>
                                <td>
                                    <div class="member">
                                        <img src="images/avatarF2.jpeg" alt="Sarah" class="member-avatar">
                                        <span>Sarah</span>
                                    </div>
                                </td>
                                <td><span class="status-badge completed-task">Completed</span></td>
                                <td>Mar 5, 2025</td>
                                <td class="actions">
                                    <button class="btn-icon delete" title="Delete Task" onclick="openGenericModal('deleteTaskModal')">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </td>
                            </tr>
                            <tr>
                                <td>Implement Authentication</td>
                                <td class='taskDescription'></td>
                                <td>
                                    <div class="member">
                                        <img src="images/avatarF1.jpeg" alt="John" class="member-avatar">
                                        <span>John</span>
                                    </div>
                                </td>
                                <td><span class="status-badge in-progress">In Progress</span></td>
                                <td>Mar 10, 2025</td>
                                <td class="actions">
                                    <button class="btn-icon status-icons" title="Complete Task" onclick="openGenericModal('completeTaskModal')">
                                        <i class="fas fa-flag-checkered"></i>
                                    </button>
                                    <button class="btn-icon delete" title="Delete Task" onclick="openGenericModal('deleteTaskModal')">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </td>
                            </tr>
                            <tr>
                                <td>Database Setup</td>
                                <td class='taskDescription'>Define tables, relationships, and constraints to store and manage system data.</td>

                                <td>
                                    <div>
                                        <span></span>
                                    </div>
                                </td>
                                <td><span class="status-badge pending">Unassigned</span></td>
                                <td>Mar 15, 2025</td>
                                <td class="actions">

                                    <button class="btn-icon status-icons" title="Choose Task" onclick="openGenericModal('assignTaskModal')">
                                        <i class="fas fa-user-check"></i>

                                    </button>

                                    <button class="btn-icon delete" title="Delete Task" onclick="openGenericModal('deleteTaskModal')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Testing and QA</td>
                                <td class='taskDescription'></td>
                                <td>
                                    <div class="member">
                                        <img src="images/avatarM1.jpeg" alt="Emma" class="member-avatar">
                                        <span>Emma</span>
                                    </div>
                                </td>
                                <td><span class="status-badge not-started">Not Started</span></td>
                                <td>Mar 20, 2025</td>
                                <td class="actions">
                                    <button class="btn-icon status-icons" title="Start Task" onclick="openGenericModal('startTaskModal')">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn-icon delete" title="Delete Task" onclick="openGenericModal('deleteTaskModal')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>


                            </tbody>
                        </table>
                    </div>
                </div>
                
                                <!-- Files Tab -->
                <div id="files" class="tab-pane">
                        <div class="file-section">
                            <h2>Files</h2>
                            <label class="upload-btn">
                                <i class="fas fa-upload"></i> Upload File
                                <input type="file" id="fileInput" style="display:none" onchange="uploadFile()">
                            </label>
                            <table class="file-table" id="fileTable">
                                <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Uploader</th>
                                    <th>Upload Time</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody class="file-rows">
                                    
                                </tbody>
                            </table>
                        </div>

                </div>


                <!-- Invite Popup -->
                <div id="invitePopup" class="popup modal">
                    <div class="popup-content modal-content">
                        <div class="modal-header">
                            <h2>Add Member</h2>
                            <button class="close-modal" onclick="closeGenericModal('invitePopup')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="inviteForm" >
                                <div id="invite-model">
                                    <input type="text" id="inviteInput"  placeholder="Enter username" required>
                                    <button class="btn btn-primary" id="inviteBtn" type="button" >Invite</button>
                                </div>
                            </form>

                            <p class="light-gray" id="members-header"> </p><br>
                            <ul class="member-list" id="dynamic-member-list">
                            </ul>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Add Task Modal (hidden by default) -->

                <div id="addTaskModal" class="modal">
                    <div class="modal-content create-modal">
                        <div class="modal-header">
                            <h2>Add New Task</h2>
                            <button class="close-modal" onclick="closeGenericModal('addTaskModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="addTaskForm" >
                                <div class="form-group">
                                    <label for="taskName">Task Name</label>
                                    <input type="text" id="taskName" name="task_name" maxlength='25' required>
                                </div>
                                  <div class="form-group">
                                    <label for="taskDescription">Task Description</label>
                                    <textarea id="taskDescription" name="task_description" maxlength="85"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="taskDeadline">Deadline</label>
                                    <input type="date" id="taskDeadline" name="task_deadline" required>
                                </div>
                                <div id="container-btn-form">
                                    <button type="submit"  class="btn btn-primary" >Add</button>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Project Popup -->
                <div id="deleteProjectModal" class="modal confirm-modal">
                    <div class="modal-content modal-confirm-content">
                        <div class="modal-header">
                            <h2>Delete Project</h2>
                            <button class="close-modal" onclick="closeGenericModal('deleteProjectModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this project?</p>
                            <div class="modal-actions">
                                <button class="btn btn-danger small-btn" id="confirmProjectDelete">Delete</button>
                                <button class="btn btn-secondary small-btn" id="cancelDelete" onclick="closeGenericModal('deleteProjectModal')">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mark as Complete Popup -->
                <div id="completeProjectModal" class="modal">
                    <div class="modal-content modal-confirm-content">
                        <div class="modal-header">
                            <h2>Mark as Complete</h2>
                            <button class="close-modal" onclick="closeGenericModal('completeProjectModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <span>Are you sure you want to mark this project as complete?</span>
                            <div class="modal-actions">
                                <button class="btn btn-primary small-btn" id="confirmCompleteProject">Complete</button>
                                <button class="btn btn-secondary small-btn" id="cancelComplete" onclick="closeGenericModal('completeProjectModal')">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                  <!-- Delete task Popup -->
                <div id="deleteTaskModal" class="modal confirm-modal">
                    <div class="modal-content modal-confirm-content">
                        <div class="modal-header">
                            <h2>Delete Task</h2>
                            <button class="close-modal" onclick="closeGenericModal('deleteTaskModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this task?</p>
                            <div class="modal-actions">
                                <button class="btn btn-danger small-btn" id="confirmDeleteTask" data-task-id>Delete</button>
                                <button class="btn btn-secondary small-btn" id="cancelDelete" onclick="closeGenericModal('deleteTaskModal')">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                  
                       <!-- Delete file Popup -->
                <div id="deleteFileModal" class="modal confirm-modal">
                    <div class="modal-content modal-confirm-content">
                        <div class="modal-header">
                            <h2>Delete File</h2>
                            <button class="close-modal" onclick="closeGenericModal('deleteFileModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this file?</p>
                            <div class="modal-actions">
                                <button class="btn btn-danger small-btn" id="confirmDeleteFile" >Delete</button>
                                <button class="btn btn-secondary small-btn" id="cancelDelete" onclick="closeGenericModal('deleteFileModal')">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                 <!-- Assign task-->
                <div id="assignTaskModal" class="modal">
                    <div class="modal-content modal-confirm-content">
                        <div class="modal-header">
                            <h2>Assign Task</h2>
                            <button class="close-modal" onclick="closeGenericModal('assignTaskModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <span>Are you sure you want to assign this task to yourself?</span>
                            <div class="modal-actions">
                                <button class="btn btn-primary small-btn" id="confirmAssignTask">Assign</button>
                                <button class="btn btn-secondary small-btn" id="cancelComplete" onclick="closeGenericModal('assignTaskModal')">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                   <!-- Start task-->
                <div id="startTaskModal" class="modal">
                    <div class="modal-content modal-confirm-content">
                        <div class="modal-header">
                            <h2>Start Task</h2>
                            <button class="close-modal" onclick="closeGenericModal('startTaskModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <span>Are you sure you want to start this task?</span>
                            <div class="modal-actions">
                                <button class="btn btn-primary small-btn" id="confirmStartTask">Start</button>
                                <button class="btn btn-secondary small-btn" id="cancelComplete" onclick="closeGenericModal('startTaskModal')">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                     <!-- complete task-->
                <div id="completeTaskModal" class="modal">
                    <div class="modal-content modal-confirm-content">
                        <div class="modal-header">
                            <h2>Complete Task</h2>
                            <button class="close-modal" onclick="closeGenericModal('completeTaskModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <span>Are you sure you want to mark this task as complete?</span>
                            <div class="modal-actions">
                                <button class="btn btn-primary small-btn" id="confirmCompleteTask">Complete</button>
                                <button class="btn btn-secondary small-btn" id="cancelComplete"  onclick="closeGenericModal('completeTaskModal')">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
        </div>
        <!-- Chat Icon -->
<div class="chat-icon" onclick="openChatRoom()">
    <i class="fas fa-comments fa-2x"></i>
</div>

<!-- Replace the existing chat icon script with this: -->
<script>
    function openChatRoom() {
        // Get project_ID from URL or fallback to sessionStorage
        const urlParams = new URLSearchParams(window.location.search);
        let project_ID = urlParams.get('project_ID');
        
        if (!project_ID) {
            // Try to get from sessionStorage if not in URL
            project_ID = sessionStorage.getItem('currentProjectID');
        }

        if (project_ID) {
            // Store in sessionStorage as backup
            sessionStorage.setItem('currentProjectID', project_ID);
            window.location.href = `chatroom.php?project_ID=${project_ID}`;
        } else {
            alert('Error: Could not determine project. Please reopen this project.');
            console.error('Project ID missing in both URL and sessionStorage');
        }
    }
    
</script>
    </main>
</div>
<script src="js/components.js"></script>

   <script>
        // Pass PHP variables to JavaScript
        const CURRENT_PROJECT = {
            id: <?php echo $project_id; ?>,
            name: "<?php echo addslashes($project['project_name']); ?>",
            isLeader: <?php echo $is_leader ? 'true' : 'false'; ?>
        };
        const CURRENT_USER_ID = <?php echo $user_id; ?>;
    </script>
    <script>
// 1. Get IDs with proper type conversion
const urlParams = new URLSearchParams(window.location.search);
const projectId = <?php echo $project_id; ?>; 
const currentUserIdMeta = document.querySelector('meta[name="user-id"]');
const currentUserId = currentUserIdMeta ? parseInt(currentUserIdMeta.content) : null;

// 2. Enhanced loadTeamMembers with better ID comparison
async function loadTeamMembers() {
    const memberList = document.getElementById('dynamic-member-list');
    if (!memberList) return;

    try {
        memberList.innerHTML = '<div class="loading-members">Loading team members...</div>';
        
        const response = await fetch(`get_project_members.php?project_id=${projectId}&action=get_team`);
        
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        
        const data = await response.json();
        
        if (data.status !== 'success') throw new Error(data.message || 'Failed to load team data');
        
        memberList.innerHTML = '';
        
        // Process leader first
        if (data.leader) {
            const leaderElement = createMemberElement(
                data.leader, 
                true, 
                data.leader.user_id == currentUserId
            );
            memberList.appendChild(leaderElement);
        }
        
        // Process other members
        data.members.forEach(member => {
            // Skip leader if already processed
            if (member.user_id != data.leader?.user_id) {
                const memberElement = createMemberElement(
                    member,
                    false,
                    member.user_id == currentUserId
                );
                memberList.appendChild(memberElement);
            }
        });
        
    } catch (error) {
        console.error('Error loading team:', error);
        memberList.innerHTML = `
            <div class="error-message">
                Error: ${error.message}
                <button onclick="loadTeamMembers()" class="retry-btn">Retry</button>
            </div>
        `;
    }
}

// 3. Updated member element creation with leader-specific "You" badge
function createMemberElement(user, isLeader, isCurrentUser, isCurrentLeader = false) {
    const li = document.createElement('li');
    li.className = 'member-item';
    
    const avatarHTML = generateAvatar(user.username, '--invite');
    
    // Determine which "You" badge to show
    let youBadge = '';
    if (isCurrentUser) {
        youBadge = isCurrentLeader 
            ? '<span class="status-badge completed leader-you" id="you-leader">You (Leader)</span>'
            : '<span class="status-badge completed">You</span>';
    }
    
    li.innerHTML = `
        <div class="member-info">
            ${avatarHTML}
            <div>
                <strong>${user.username}</strong>
                <div class="member-email">${user.email || 'No email provided'}</div>
            </div>
        </div>
        <span class="member-role light-gray">${isLeader ? 'Leader' : 'Member'}</span>
        ${youBadge}
        ${!isCurrentUser ? `
            <button class="btn-icon delete delete-member-btn" 
                    title="Delete Member" 
                    data-user-id="${user.user_id}"
                    onclick="deleteMember(${user.user_id}, this)">
                ${isLeader ? '<span class="status-badge completed" id="you-leader"> You</span>' :
                '<i class="fas fa-trash"></i>'}
            </button>
        ` : ''}
    `;
    
    return li;
}

// 4. Initialize with checks
document.addEventListener('DOMContentLoaded', function() {
    if (!projectId) {
        showError('Project ID missing from URL');
        return;
    }
    
    loadTeamMembers();
});

function showError(message) {
    const memberList = document.getElementById('dynamic-member-list');
    if (memberList) {
        memberList.innerHTML = `
            <div class="error-message">
                ${message}
            </div>
        `;
    }
}
async function deleteMember(userId, element) {
    if (!confirm(`Are you sure you want to remove this member?`)) return;
    
    try {
        const response = await fetch(`get_project_members.php?action=delete&project_id=${projectId}&user_id=${userId}`);
        const result = await response.json();
        
        if (result.status === 'success') {
            showToast('Member removed successfully');
            element.closest('.member-item').remove();
        } else {
            throw new Error(result.message || 'Failed to remove member');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showToast(error.message, 'error');
    }
    finally {
    loadTeamMembers();
        }
}
</script>    

<script>
    
         document.addEventListener('DOMContentLoaded', function() {
        // Get project ID from URL or session
        const urlParams = new URLSearchParams(window.location.search);
        const project_ID = urlParams.get('project_ID');
        
        if (!project_ID) {
            console.error("Project ID not found in URL");
            return;
        }

        // Fetch all data needed for dashboard
        fetchDashboardData(project_ID);

        // Tab Switching Functionality
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                tabBtns.forEach(b => b.classList.remove('active'));
                tabPanes.forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                const tabId = btn.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
    });
 // Get projectId from PHP
document.addEventListener('DOMContentLoaded', function() {

const inviteBtn = document.getElementById('inviteBtn');
if (!inviteBtn) {
  console.error("ERROR: Could not find #inviteBtn element!");
} else {
  console.log("Button found, attaching click handler...");
  inviteBtn.addEventListener('click', sendInvite);
}
async function sendInvite() {
    const username = document.getElementById('inviteInput').value.trim();
    console.log("Sending invite with:", {
        project_id: CURRENT_PROJECT.id,
        username: username
    });
    
    try {
        const response = await fetch('invite_member.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                project_id: CURRENT_PROJECT.id,
                username: username
            }),
            credentials: 'include'
        });
        
        console.log("Response status:", response.status);
        const result = await response.json();
        console.log("Response data:", result);
        
        if (result.status === 'success') {
            showToast(`Invite sent to ${username}!`, 'success');
            document.getElementById('inviteInput').value = '';
        } else {
            throw new Error(result.message || "Failed to send invite");
        }
    } catch (error) {
        console.error("Full error details:", {
            error: error,
            message: error.message,
            stack: error.stack
        });
        showToast(`${error.message}`, 'error');
    }
}
// Initialize form
document.getElementById('inviteForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Stop form submission
    console.log("Form submit intercepted");
    sendInvite();
    return false; // Extra prevention
});

// Show toast messages
function showToast(message, type = 'success', duration = 3000) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.textContent = message;
    
    // Add to DOM
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Remove after duration
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, duration);
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