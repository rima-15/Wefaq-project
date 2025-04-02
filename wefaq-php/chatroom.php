<?php
session_start();
require 'connection.php'; // Database connection
include 'auth_check.php'; // Add centralized authentication check

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

$user_ID = $_SESSION['user_id'];
$chat_ID = $_GET['chat_ID'] ?? 1; // Default to chat ID 1 if not provided

// Fetch messages
$stmt = $conn->prepare("SELECT m.message_text, m.timestamp, u.username FROM message m JOIN user u ON m.sender_ID = u.user_ID WHERE m.chat_ID = ? ORDER BY m.timestamp ASC");
$stmt->bind_param("i", $chat_ID);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Chat Room</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .chat-container {
            width: 100vw;
            height: 100vh;
            background: white;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            background: #9096DE;
            color: white;
            padding: 5px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .back-button {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }
        .chat-box {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #f9f9f9;
        }
        .chat-message {
            display: flex;
            margin: 10px 0;
        }
        .my-message {
            justify-content: flex-end;
        }
        .chat-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .chat-content {
            max-width: 70%;
            padding: 8px 12px;
            border-radius: 10px;
            background: #ddd;
        }
        .my-message .chat-content {
            background: #9096DE;
            color: white;
        }
        .chat-user {
            font-size: 12px;
            color: white;
        }
        .chat-time {
            font-size: 10px;
            margin-left: 5px;
        }
        .chat-input {
            display: flex;
            padding: 15px;
            background: white;
            border-top: 1px solid #ddd;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .chat-input button {
            background: #9096DE;
            color: white;
            border: none;
            padding: 10px 15px;
            margin-left: 5px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <header class="chat-header">
            <h2>Project Chat Room</h2>
            <a href="project.html" class="back-button"><i class="fas fa-arrow-left"></i> Back</a>
        </header>
        <div class="chat-box" id="chatBox">
            <?php foreach ($messages as $message): ?>
                <div class="chat-message <?php echo ($message['username'] === $_SESSION['username']) ? 'my-message' : ''; ?>">
                    <div class="chat-avatar">
                        <img src="Wefaq.jpg" alt="<?php echo htmlspecialchars($message['username']); ?>">
                    </div>
                    <div class="chat-content">
                        <div class="chat-user"><?php echo htmlspecialchars($message['username']); ?> <span class="chat-time"><?php echo $message['timestamp']; ?></span></div>
                        <div class="chat-text"><?php echo htmlspecialchars($message['message_text']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="chat-input">
            <input type="text" id="messageInput" placeholder="Type a message...">
            <button id="sendMessage"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
    <script>
        document.getElementById("sendMessage").addEventListener("click", function() {
            let messageText = document.getElementById("messageInput").value.trim();
            if (messageText === "") return;
            fetch("chatroom_backend.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `chat_ID=<?php echo $chat_ID; ?>&message_text=${encodeURIComponent(messageText)}`
            }).then(() => location.reload());
        });
    </script>
</body>
</html>