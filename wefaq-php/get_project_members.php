<?php
ob_start();
header('Content-Type: application/json');
require 'connection.php';
session_start();
include 'auth_check.php';
$response = ['status' => 'error', 'message' => 'Unknown error'];

// Determine action type from either GET or POST
$action = $_GET['action'] ?? $_POST['action'] ?? null;

try {
    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
    
    error_log("[DEBUG] Action: {$action}, Current User ID: {$currentUserId}");

   
    // ==============================================
    // DELETE MEMBER ACTION
    // ==============================================
    if ($action === 'delete') {
        // Validate input
        if (empty($_GET['project_id']) || !is_numeric($_GET['project_id'])) {
            throw new Exception("Valid project ID is required");
        }
        if (empty($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
            throw new Exception("Valid user ID is required");
        }

        $projectId = (int)$_GET['project_id'];
        $userId = (int)$_GET['user_id'];

        // Verify project exists and current user is leader
        $stmt = $conn->prepare("SELECT leader_id FROM project WHERE project_id = ?");
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $stmt->bind_result($leaderId);
        $stmt->fetch();
        $stmt->close();

        if (!$leaderId) {
            throw new Exception("Project not found");
        }
        if ($currentUserId !== $leaderId) {
            throw new Exception("Only project leader can remove members");
        }
        if ($userId === $leaderId) {
            throw new Exception("Cannot remove project leader");
        }

        // Delete member
        $deleteStmt = $conn->prepare("DELETE FROM projectTeam WHERE project_id = ? AND user_id = ?");
        $deleteStmt->bind_param("ii", $projectId, $userId);
        $deleteStmt->execute();
        
        if ($deleteStmt->affected_rows === 0) {
            throw new Exception("User was not a member of this project");
        }
        $deleteStmt->close();

        $response = [
            'status' => 'success',
            'message' => "Member removed from project"
        ];
    }
    // ==============================================
    // GET TEAM MEMBERS (inbox)
    // ==============================================
    else {
        // Validate project ID
        if (empty($_GET['project_id']) || !is_numeric($_GET['project_id'])) {
            throw new Exception("Valid project ID is required");
        }
        $projectId = (int)$_GET['project_id'];

        // Verify project exists
        $stmt = $conn->prepare("SELECT leader_id FROM project WHERE project_id = ?");
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $stmt->bind_result($leaderId);
        $stmt->fetch();
        $stmt->close();

        if (!$leaderId) {
            throw new Exception("Project not found");
        }

        // Get team data
        if ($action === 'get_team') {
            // Get leader info
            $leaderStmt = $conn->prepare("SELECT user_id, username, email FROM user WHERE user_id = ?");
            $leaderStmt->bind_param("i", $leaderId);
            $leaderStmt->execute();
            $leaderResult = $leaderStmt->get_result();
            $leader = $leaderResult->fetch_assoc();
            $leaderStmt->close();

            // Get all members
            $teamStmt = $conn->prepare("
                SELECT u.user_id, u.username, u.email 
                FROM projectTeam pt
                JOIN user u ON pt.user_id = u.user_id
                WHERE pt.project_id = ?
                ORDER BY u.user_id = ? DESC, u.username ASC
            ");
            $teamStmt->bind_param("ii", $projectId, $leaderId);
            $teamStmt->execute();
            $teamResult = $teamStmt->get_result();
            
            $members = [];
            while ($row = $teamResult->fetch_assoc()) {
                $members[] = [
                    'user_id' => (int)$row['user_id'],
                    'username' => htmlspecialchars($row['username']),
                    'email' => htmlspecialchars($row['email']),
                    'is_leader' => ($row['user_id'] == $leaderId)
                ];
            }
            $teamStmt->close();

            $response = [
                'status' => 'success',
                'leader' => $leader,
                'members' => $members,
                'current_user_id' => $currentUserId
            ];
        } 
        // Original non-team functionality
        else {
            $stmt = $conn->prepare("
                SELECT u.user_id as member_id, u.username as name
                FROM projectTeam pm
                JOIN user u ON pm.user_id = u.user_id
                WHERE pm.project_id = ? AND u.user_id != ?
            ");
            $stmt->bind_param("ii", $projectId, $currentUserId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $members = [];
            while ($row = $result->fetch_assoc()) {
                $members[] = [
                    'member_id' => (int)$row['member_id'],
                    'name' => htmlspecialchars($row['name'])
                ];
            }
            $stmt->close();

            $response = [
                'status' => 'success',
                'members' => $members,
                'count' => count($members)
            ];
        }
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("[ERROR] get_project_members: " . $e->getMessage());
} finally {
    echo json_encode($response);
    if (isset($conn)) $conn->close();
    exit;
}
?>