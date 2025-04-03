<?php
session_start();
require 'connection.php';
include 'auth_check.php';

// Get project_ID with multiple fallbacks
$project_ID = $_GET['project_ID'] ?? $_SESSION['current_project_id'] ?? null;

if (!$project_ID || !is_numeric($project_ID)) {
    die("<script>alert('Missing project reference. Please reopen from project page.'); 
          window.history.back();</script>");
}

// Store for future use
$_SESSION['current_project_id'] = $project_ID;

// Get project name and chat_ID
$stmt = $conn->prepare("SELECT p.project_name, c.chat_ID 
                        FROM project p
                        JOIN chat c ON p.project_ID = c.project_ID
                        WHERE p.project_ID = ?");
$stmt->bind_param("i", $project_ID);
$stmt->execute();
$result = $stmt->get_result();
$project_data = $result->fetch_assoc();
$stmt->close();

if (!$project_data) {
    die("Project or chat room not found");
}

$project_name = htmlspecialchars($project_data['project_name']);
$chat_ID = $project_data['chat_ID'];

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_text'])) {
    $message_text = trim($_POST['message_text']);
    if (!empty($message_text)) {
        $stmt = $conn->prepare("INSERT INTO message (message_text, sender_ID, chat_ID) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $message_text, $_SESSION['user_id'], $chat_ID);
        $stmt->execute();
        $stmt->close();
        
        // Return success response for AJAX
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

// Fetch messages with user details
$stmt = $conn->prepare("
    SELECT m.message_ID, m.message_text, m.timestamp, 
           u.user_ID, u.username, u.gender
    FROM message m 
    JOIN user u ON m.sender_ID = u.user_ID 
    WHERE m.chat_ID = ? 
    ORDER BY m.timestamp ASC
");
$stmt->bind_param("i", $chat_ID);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get current user's username if not set
if (!isset($_SESSION['username'])) {
    $stmt = $conn->prepare("SELECT username FROM user WHERE user_ID = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $_SESSION['username'] = $result->fetch_assoc()['username'];
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $project_name; ?> Chat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f4f4f4;
        }
        .chat-container {
            width: 100%;
            max-width: 800px;
            height: 100vh;
            background: white;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .chat-header {
            background: #9096DE;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .back-button {
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .chat-box {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
        }
        .chat-message {
            display: flex;
            margin-bottom: 15px;
            max-width: 80%;
        }
        .my-message {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        .other-message {
            align-self: flex-start;
        }
        .chat-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        .chat-content {
            max-width: calc(100% - 60px);
            padding: 10px 15px;
            border-radius: 15px;
            background: #ddd;
            position: relative;
            word-wrap: break-word;
        }
        .my-message .chat-content {
            background: #9096DE;
            color: white;
        }
        .chat-user {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 3px;
        }
        .chat-time {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
            text-align: right;
        }
        .my-message .chat-time {
            color: rgba(255,255,255,0.7);
        }
        .chat-input-container {
            padding: 15px;
            background: white;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 10px;
        }
        .chat-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
        }
        .send-button {
            background: #9096DE;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <header class="chat-header">
            <h2><?php echo $project_name; ?> Chat</h2>
            <a href="project.html?project_ID=<?php echo $project_ID; ?>" class="back-button">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </header>
        
        <div class="chat-box" id="chatBox">
            <?php foreach ($messages as $message): 
                $isCurrentUser = ($message['user_ID'] == $_SESSION['user_id']);
                $avatarInitial = strtoupper(substr($message['username'], 0, 1));
                $avatarColor = $message['gender'] == 'F' ? '#ff9ff3' : '#48dbfb';
            ?>
                <div class="chat-message <?php echo $isCurrentUser ? 'my-message' : 'other-message'; ?>">
                    <div class="chat-avatar" style="background-color: <?php echo $avatarColor; ?>">
                        <?php echo $avatarInitial; ?>
                    </div>
                    <div class="chat-content">
                        <?php if (!$isCurrentUser): ?>
                            <div class="chat-user"><?php echo htmlspecialchars($message['username']); ?></div>
                        <?php endif; ?>
                        <div class="chat-text"><?php echo htmlspecialchars($message['message_text']); ?></div>
                        <div class="chat-time">
                            <?php echo date('h:i A', strtotime($message['timestamp'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="chat-input-container">
            <input type="text" id="messageInput" class="chat-input" placeholder="Type a message..." autocomplete="off">
            <button id="sendMessage" class="send-button">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatBox = document.getElementById('chatBox');
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendMessage');
            
            // Auto-scroll to bottom of chat
            chatBox.scrollTop = chatBox.scrollHeight;
            
            // Handle sending messages
            function sendMessage() {
                const messageText = messageInput.value.trim();
                if (messageText === '') return;
                
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `message_text=${encodeURIComponent(messageText)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        location.reload(); // Refresh to show new message
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            
            // Send on button click
            sendButton.addEventListener('click', sendMessage);
            
            // Send on Enter key
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
            
            // Focus input field on load
            messageInput.focus();
        });
    </script>
</body>
</html>