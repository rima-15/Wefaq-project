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
            
            // 2. Add creator to project team
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
                "project_ID" => $project_ID
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
        $query = "SELECT * FROM project WHERE project_ID = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("i", $project_ID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $response = $result->fetch_assoc();
            } else {
                $response = ["success" => false, "error" => "Project not found"];
            }
            $stmt->close();
        } else {
            $response = ["success" => false, "error" => "Database error: " . $conn->error];
        }
    } else {
        $response = ["success" => false, "error" => "Invalid project ID"];
    }
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
// Add this condition before your final else
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

else {
    $response = ["success" => false, "message" => "Invalid request"];
}

echo json_encode($response);
$conn->close();

?>