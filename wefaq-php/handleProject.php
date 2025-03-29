<?php
header('Content-Type: application/json');
session_start();
require 'connection.php';
// Get JSON input if exists
$json = file_get_contents('php://input');
$data = json_decode($json, true) ?? $_POST; // Fallback to regular POST if not JSON

// Authentication check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not authenticated"]);

    exit;
}
    $leader_ID = $_SESSION['user_id'];
    $user_ID = $_SESSION['user_id'];

$response = [];

// Check connection
if ($conn->connect_error) {
    $response = ["success" => false, "message" => "Database connection failed: " . $conn->connect_error];
    echo json_encode($response);
    exit;
}

// In the create project section of handleProject.php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_project'])) {
    $project_name = $_POST['project_name'] ?? '';
    $project_description = $_POST['project_description'] ?? '';
    $project_deadline = $_POST['project_deadline'] ?? '';
    $leader_ID = $_SESSION['user_id']; // Make sure this is set

    if (empty($project_name) || empty($project_deadline)) {
        $response = ["success" => false, "message" => "Project name and deadline are required."];
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // 1. Create the project
            $sql = "INSERT INTO project (project_name, project_description, project_deadline, status, created_at, leader_ID) 
                    VALUES (?, ?, ?, 'In Progress', NOW(), ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            
            $stmt->bind_param("sssi", $project_name, $project_description, $project_deadline, $leader_ID);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating project: " . $stmt->error);
            }
            
            $project_ID = $stmt->insert_id;
            $stmt->close();
            
            // 2. Create chat for the project
            $sql = "INSERT INTO chat (project_ID) VALUES (?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            
            $stmt->bind_param("i", $project_ID);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating project chat: " . $stmt->error);
            }
            
            $chat_ID = $stmt->insert_id;
            $stmt->close();
            
            // 3. Add creator to project team
            $sql = "INSERT INTO projectteam (project_ID, user_ID) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            
            $stmt->bind_param("ii", $project_ID, $leader_ID);
            
            if (!$stmt->execute()) {
                throw new Exception("Error adding to project team: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            $response = [
                "success" => true, 
                "message" => "Project created successfully.", 
                "project_ID" => $project_ID,
                "chat_ID" => $chat_ID
            ];
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $response = ["success" => false, "message" => $e->getMessage()];
        }
    }
}
// Retrieve all projects
elseif (isset($_GET['fetch_all'])) {
  $query = "SELECT p.project_ID, p.project_name 
              FROM project p
              JOIN projectteam pt ON p.project_ID = pt.project_ID
              WHERE pt.user_ID = ?
              ORDER BY p.created_at DESC";
              
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $user_ID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
        $response = $projects;
        $stmt->close();
    } else {
        $response = ["success" => false, "message" => "Database error: " . $conn->error];
    }
}
// Get single project
elseif (isset($_GET['get_project'])) {
    $project_ID = $_GET['project_ID'] ?? 0;
    
    if ($project_ID > 0) {
        $query = "SELECT 
                    project_ID,
                    project_name,
                    project_description,
                    project_deadline,
                    status,
                    leader_ID,
                    created_at,
                    CASE 
                        WHEN status = 'completed' THEN 1 
                        ELSE 0 
                    END as is_completed
                  FROM project 
                  WHERE project_ID = ?";
        
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("i", $project_ID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $response = $result->fetch_assoc();
                // Ensure status is never null
                $response['status'] = $response['status'] ?? 'in progress';
            } else {
                $response = ["error" => "Project not found"];
            }
            $stmt->close();
        } else {
            $response = ["error" => "Database error"];
        }
    } else {
        $response = ["error" => "Invalid project ID"];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
// Delete a project
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_project'])) {
  $project_ID = $_POST['project_ID'] ?? 0;
    
    if ($project_ID > 0) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // 1. Verify user is the leader of this project
            $checkLeader = "SELECT 1 FROM project WHERE project_ID = ? AND leader_ID = ?";
            $stmt = $conn->prepare($checkLeader);
            $stmt->bind_param("ii", $project_ID, $user_ID);
            $stmt->execute();

            if (!$stmt->get_result()->num_rows > 0) {
                throw new Exception("Only project leaders can delete projects");
            }
              
            
            // 2. Delete from projectteam first (due to foreign key constraint)
            $deleteTeam = "DELETE FROM projectteam WHERE project_ID = ?";
            $stmt = $conn->prepare($deleteTeam);
            $stmt->bind_param("i", $project_ID);
            $stmt->execute();
            
            // 3. Delete from project
            $deleteProject = "DELETE FROM project WHERE project_ID = ?";
            $stmt = $conn->prepare($deleteProject);
            $stmt->bind_param("i", $project_ID);
            $stmt->execute();
            
            $conn->commit();
            $response = ["success" => true, "message" => "Project deleted successfully"];
            
        } catch (Exception $e) {
            $conn->rollback();
            $response = ["success" => false, "message" => $e->getMessage()];
        }
    } else {
        $response = ["success" => false, "message" => "Invalid project ID"];
    }
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_project'])) {
    error_log("Update project request received");
    
    $project_ID = (int)($_POST['project_ID'] ?? 0);
    $new_name = trim($_POST['new_name'] ?? '');

    // Simple validation
    if (empty($new_name)) {
        error_log("Empty name rejected");
        echo json_encode(['success' => false, 'message' => 'Name cannot be empty']);
        exit;
    }

    if ($project_ID <= 0) {
        error_log("Invalid project ID: $project_ID");
        echo json_encode(['success' => false, 'message' => 'Invalid project']);
        exit;
    }

    // Verify user is leader (use your existing session check)
    $stmt = $conn->prepare("SELECT leader_ID FROM project WHERE project_ID = ?");
    $stmt->bind_param("i", $project_ID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0 || $result->fetch_assoc()['leader_ID'] != $_SESSION['user_id']) {
        error_log("User not authorized to edit");
        echo json_encode(['success' => false, 'message' => 'Not authorized']);
        exit;
    }

    // Perform update
    $stmt = $conn->prepare("UPDATE project SET project_name = ? WHERE project_ID = ?");
    $stmt->bind_param("si", $new_name, $project_ID);
    
    if ($stmt->execute()) {
        error_log("Project $project_ID updated to: $new_name");
        echo json_encode(['success' => true]);
    } else {
        error_log("Database error: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_description'])) {
    header('Content-Type: application/json');
    
    try {
        $project_ID = (int)$_POST['project_ID'];
        $new_description = trim($_POST['new_description']);

        // Validate input
        if (empty($new_description)) {
            throw new Exception("Description cannot be empty");
        }

        // Verify leadership
        $stmt = $conn->prepare("SELECT leader_ID FROM project WHERE project_ID = ?");
        $stmt->bind_param("i", $project_ID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Project not found");
        }
        
        $leader_ID = $result->fetch_assoc()['leader_ID'];
        if ($leader_ID != $_SESSION['user_id']) {
            throw new Exception("Only project leaders can edit descriptions");
        }

        // Update description
        $stmt = $conn->prepare("UPDATE project SET project_description = ? WHERE project_ID = ?");
        $stmt->bind_param("si", $new_description, $project_ID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
// 
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_project'])) {
    header('Content-Type: application/json');
    
    try {
        $project_ID = $_POST['project_ID'];
        
        // Verify leadership
        $stmt = $conn->prepare("SELECT leader_ID FROM project WHERE project_ID = ?");
        $stmt->bind_param("i", $project_ID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Project not found");
        }
        
        $leader_ID = $result->fetch_assoc()['leader_ID'];
        if ($leader_ID != $_SESSION['user_id']) {
            throw new Exception("Only project leaders can complete projects");
        }

        // Update status
        $stmt = $conn->prepare("UPDATE project SET status = 'Completed' WHERE project_ID = ?");
        $stmt->bind_param("i", $project_ID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
// Add a new task
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    header('Content-Type: application/json');
    
    try {
        $task_name = trim($_POST['task_name']);
        $task_description = trim($_POST['task_description'] ?? '');
        $task_deadline = $_POST['task_deadline'];
        $project_ID = (int)$_POST['project_ID'];
        
        // Validate input
        if (empty($task_name)) {
            throw new Exception("Task name is required");
        }
        
        if (empty($task_deadline)) {
            throw new Exception("Deadline is required");
        }
        
        // Insert task
        $stmt = $conn->prepare("INSERT INTO task 
            (task_name, task_description, status, created_at, task_deadline, created_by, project_ID) 
            VALUES (?, ?, 'unassigned', NOW(), ?, ?, ?)");
        
        $stmt->bind_param("sssii", $task_name, $task_description, $task_deadline, $_SESSION['user_id'], $project_ID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'task_ID' => $stmt->insert_id]);
        } else {
            throw new Exception("Failed to add task: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get all tasks for a project (updated for dynamic avatars)
elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['get_tasks'])) {
    header('Content-Type: application/json');
    
    try {
        $project_ID = (int)$_GET['project_ID'];
        
        $stmt = $conn->prepare("
            SELECT 
                t.*, 
                u.username,
                u.user_ID
            FROM task t
            LEFT JOIN user u ON t.assigned_to = u.user_ID
            WHERE t.project_ID = ?
            ORDER BY 
                CASE t.status
                    WHEN 'completed' THEN 4
                    WHEN 'in progress' THEN 3
                    WHEN 'not started' THEN 2
                    WHEN 'unassigned' THEN 1
                END,
                t.task_deadline ASC
        ");
        
        $stmt->bind_param("i", $project_ID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            // Add initials for client-side avatar generation
            $row['initials'] = $row['username'] ? strtoupper(substr($row['username'], 0, 1)) : '';
            $tasks[] = $row;
        }
        
        echo json_encode([
            'success' => true, 
            'tasks' => $tasks,
            'avatar_colors' => ['#9096DE', '#EED442', '#886B63', '#D3D3D3','#634d47','#b0aeae','#c4c8f2','#fae987']
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Update task status (unchanged)
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_task_status'])) {
    header('Content-Type: application/json');
    
    try {
        $task_ID = (int)$_POST['task_ID'];
        $new_status = $_POST['new_status'];
        $user_ID = $_SESSION['user_id'];
        
        // Validate status
        $valid_statuses = ['unassigned', 'not started', 'in progress', 'completed'];
        if (!in_array($new_status, $valid_statuses)) {
            throw new Exception("Invalid status");
        }
        
        // Special handling for assigning task
        if ($new_status === 'not started' && isset($_POST['assign_to_self'])) {
            $stmt = $conn->prepare("
                UPDATE task 
                SET status = 'not started', assigned_to = ? 
                WHERE task_ID = ? AND status = 'unassigned'
            ");
            $stmt->bind_param("ii", $user_ID, $task_ID);
        } else {
            $stmt = $conn->prepare("
                UPDATE task 
                SET status = ? 
                WHERE task_ID = ?
            ");
            $stmt->bind_param("si", $new_status, $task_ID);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Failed to update task: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Delete task (unchanged)
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_task'])) {
    header('Content-Type: application/json');
    
    try {
        $task_ID = (int)$_POST['task_ID'];
        
        $stmt = $conn->prepare("DELETE FROM task WHERE task_ID = ?");
        $stmt->bind_param("i", $task_ID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Failed to delete task: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
// Get team members for a project
elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['get_team_members'])) {
    header('Content-Type: application/json');
    
    try {
        $project_ID = (int)$_GET['project_ID'];
        
        $stmt = $conn->prepare("
            SELECT u.user_ID, u.username 
            FROM projectteam pt
            JOIN user u ON pt.user_ID = u.user_ID
            WHERE pt.project_ID = ?
        ");
        
        $stmt->bind_param("i", $project_ID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $members = [];
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
        
        echo json_encode([
            'success' => true, 
            'members' => $members
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
// Handle file upload
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_file'])) {
    header('Content-Type: application/json');
    
    try {
        $project_ID = (int)$_POST['project_ID'];
        $user_ID = $_SESSION['user_id'];
        
        // Validate project access
        $stmt = $conn->prepare("SELECT 1 FROM projectteam WHERE project_ID = ? AND user_ID = ?");
        $stmt->bind_param("ii", $project_ID, $user_ID);
        $stmt->execute();
        
        if (!$stmt->get_result()->num_rows > 0) {
            throw new Exception("You don't have access to this project");
        }
        
        // Validate file upload
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed");
        }
        
        $file = $_FILES['file'];
        $file_name = $file['name'];
        $file_type = $file['type'];
        $file_size = $file['size'];
        $file_content = file_get_contents($file['tmp_name']);
        
        // Insert file metadata into database
        $stmt = $conn->prepare("INSERT INTO file 
            (file_name, file_type, file_size, uploaded_at, uploaded_by, project_ID) 
            VALUES (?, ?, ?, NOW(), ?, ?)");
        
        $stmt->bind_param("ssiii", $file_name, $file_type, $file_size, $user_ID, $project_ID);
        
        if ($stmt->execute()) {
            $file_ID = $stmt->insert_id;
            
            // Save file to server (optional - you can choose to store only in DB)
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_path = $upload_dir . $file_ID . '_' . $file_name;
            move_uploaded_file($file['tmp_name'], $file_path);
            
            echo json_encode(['success' => true, 'file_ID' => $file_ID]);
        } else {
            throw new Exception("Failed to save file metadata: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get files for a project
elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['get_files'])) {
    header('Content-Type: application/json');
    
    try {
        $project_ID = $_GET['project_ID'];
        
        error_log("Fetching files for project: " . $project_ID);
        
        $stmt = $conn->prepare("
            SELECT f.*, u.username 
            FROM file f
            LEFT JOIN user u ON f.uploaded_by = u.user_ID
            WHERE f.project_ID = ?
            ORDER BY f.uploaded_at DESC
        ");
        
        $stmt->bind_param("i", $project_ID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $files = [];
        while ($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
        
        error_log("Found " . count($files) . " files");
        echo json_encode(['success' => true, 'files' => $files]);
    } catch (Exception $e) {
        error_log("Error getting files: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Delete a file
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_file'])) {
    header('Content-Type: application/json');
    
    try {
        $file_ID = (int)$_POST['file_ID'];
        $user_ID = $_SESSION['user_id'];
        
        // Verify user is a member of the project that contains this file
        $stmt = $conn->prepare("
            SELECT 1 
            FROM projectteam pt
            JOIN file f ON pt.project_ID = f.project_ID
            WHERE f.file_ID = ? AND pt.user_ID = ?
        ");
        $stmt->bind_param("ii", $file_ID, $user_ID);
        $stmt->execute();
        
        if (!$stmt->get_result()->num_rows > 0) {
            throw new Exception("You must be a project member to delete files");
        }
        
        // Delete file record
        $stmt = $conn->prepare("DELETE FROM file WHERE file_ID = ?");
        $stmt->bind_param("i", $file_ID);
        
        if ($stmt->execute()) {
            // Delete the actual file if stored on server
            $file_path = 'uploads/' . $file_ID . '_*';
            array_map('unlink', glob($file_path));
            
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Failed to delete file: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
// Add this to handleProject.php
elseif (isset($_GET['check_leadership'])) {
    header('Content-Type: application/json');
    $project_ID = (int)$_GET['project_ID'];
    $user_ID = $_SESSION['user_id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT 1 FROM project WHERE project_ID = ? AND leader_ID = ?");
    $stmt->bind_param("ii", $project_ID, $user_ID);
    $stmt->execute();
    
    echo json_encode([
        'isLeader' => $stmt->get_result()->num_rows > 0
    ]);
    exit;
}
else {
    $response = ["success" => false, "message" => "Invalid request"];
}

echo json_encode($response);
$conn->close();

?>