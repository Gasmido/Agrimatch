<?php
require 'config.php';
include 'head.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view messages.";
    exit();  
}

$currentUserId = $_SESSION['user_id'];

// Get the user ID of the person to whom messages are being sent
$chatUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($chatUserId === 0) {
    echo "Invalid user.";
    exit();
}

// Fetch conversation details including conversation_id and question_id
$sql = "SELECT c.id AS conversation_id, c.question_id 
        FROM conversations c 
        WHERE (c.user1_id = ? AND c.user2_id = ?) OR (c.user1_id = ? AND c.user2_id = ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $currentUserId, $chatUserId, $chatUserId, $currentUserId);
$stmt->execute();
$conversationResult = $stmt->get_result();

if ($conversationResult->num_rows === 0) {
    echo "No conversation found.";
    exit();
}

$conversation = $conversationResult->fetch_assoc();
$conversation_id = $conversation['conversation_id'];
$question_id = $conversation['question_id'];

// Fetch the question text based on question_id
$sql = "SELECT question FROM questions WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $question_id);
$stmt->execute();
$questionResult = $stmt->get_result();

if ($questionResult->num_rows === 0) {
    echo "Question not found.";
    exit();
}

$question = $questionResult->fetch_assoc();
$questionText = $question['question'];

// Fetch the username and profile picture of the chat user
$sql = "SELECT username, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $chatUserId);
$stmt->execute();
$usernameResult = $stmt->get_result();

if ($usernameResult->num_rows === 0) {
    echo "User not found.";
    exit();
}

$user = $usernameResult->fetch_assoc();
$chatUsername = $user['username'];
$profilePicture = $user['profile_picture'];

// Fetch messages between the two users
$sql = "SELECT pm.*, u1.username AS sender_name, u1.profile_picture AS sender_picture, 
               u2.username AS receiver_name, u2.profile_picture AS receiver_picture
        FROM private_messages pm
        LEFT JOIN users u1 ON pm.sender_id = u1.id
        LEFT JOIN users u2 ON pm.receiver_id = u2.id
        WHERE (pm.sender_id = ? AND pm.receiver_id = ?) OR (pm.sender_id = ? AND pm.receiver_id = ?)
        ORDER BY pm.sent_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $currentUserId, $chatUserId, $chatUserId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

?>

<style type="text/css">
    .message-box {
        border-radius: 12px;
        padding: 15px;
        margin: 10px 0;
        background-color: #f2f2f2; 
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        max-width: 70%;
        clear: both;
        overflow-wrap: break-word; 
    }

    .messages {
        max-height: 400px;
        overflow-y: auto;
        padding: 20px;
    }

    .message-container {
        max-width: 800px;
        margin: 20px auto;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        background-color: #fff;
        padding: 10px;
        width: 100%;
    }

    .message-form {
        display: flex;
        align-items: center;
        padding: 20px;
        border-top: 1px solid #eee; 
        min-width: 100%;
    }

    .message-form textarea {
        flex-grow: 1;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        resize: none;
        margin-right: 10px;
    }

    .message-form button {
        background-color: #007bff; 
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }

    .message-form button:hover {
        background-color: #0069d9; 
    }

    .message-left .message-box {
        background-color: #e9ecef; 
    }

    .message-right .message-box {
        background-color: #a2d9ce; 
        float: right;
    }

    .profile-picture {
        width: 30px; /* Adjust size as needed */
        height: 30px; /* Adjust size as needed */
        border-radius: 50%;
    }
    .question-container {
      max-width: 800px;
      margin: 20px auto;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      border-radius: 12px;
      background-color: #fff;
      padding: 20px;
      text-align: center;
      width: 100%;
    }
    .question-container p {
        font-size: 20px;
    }
</style>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-container">
       <img class="ree" src="image/logo.jpg" style="border-radius:50%; max-width: 70px; margin-right: 10px;">
        <a href="index.php" class="brand"><img src="image/logo.jpg" class="non" style="border-radius:50%; max-width: 35px; margin-right: 10px;">AgriMatch</a>

        <!-- Hamburger Menu Button (Mobile View) -->
        <button class="navbar-toggle" onclick="toggleMobileMenu()">☰</button>

                    <!-- Search Form for Desktop -->
        <form class="search-form" action="search.php" method="GET">
            <input type="text" placeholder="Search questions..." name="query" required>
            <button type="submit">Search</button>
        </form>

        <!-- Regular Links for Desktop -->
         <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
        </ul>

        <!-- Mobile Links for Mobile Navigation (with Search Form) -->
        <ul class="nav-links-mobile" id="mobileMenu">
           <li><a href="index.php">Home</a></li>
            
            <!-- Mobile Search Form -->
            <li>
                <form class="search-form-mobile" action="search.php" method="GET">
                    <input type="text" placeholder="Search questions..." name="query" required>
                    <button type="submit">Search</button>
                </form>
            </li>
        </ul>
    </div>
</nav>
<div class="question-container">
    <h1>Question:</h1>
    <p><?php echo htmlspecialchars($questionText); ?></p>
</div>
<div class="message-container">
    <h2>
        <img src="user_image/<?php echo htmlspecialchars($profilePicture); ?>" alt="<?php echo htmlspecialchars($chatUsername); ?>'s Profile Picture" class="profile-picture">
        <?php echo htmlspecialchars($chatUsername); ?>
    </h2>

    <div class="messages">
        <?php while ($message = $result->fetch_assoc()): ?>
            <div class="<?= $message['sender_id'] == $currentUserId ? 'message-right' : 'message-left' ?>">
                <div class="message-box"> 
                    <div class="message">
                        <div>
                            <div class="message-text"><?php echo htmlspecialchars($message['message']); ?></div>
                            <div class="timestamp"><small><?php echo date('Y-m-d H:i', strtotime($message['sent_at'])); ?></small></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <form action="send_message.php" method="POST" class="message-form">
        <input type="hidden" name="receiver_id" value="<?php echo $chatUserId; ?>">
        <input type="hidden" name="conversation_id" value="<?php echo htmlspecialchars($conversation_id); ?>">
        <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($question_id); ?>">
        <textarea name="message" required placeholder="Enter message..." style="resize: none;"></textarea>
        <button type="submit" name="subb" style="width:80px; height: 50px;">Send</button> 
    </form>

    <form action="end_conversation.php" method="POST" style="margin-top: 20px; text-align: center;min-width: 100%;"> 
        <input type="hidden" name="conversation_id" value="<?php echo htmlspecialchars($conversation_id); ?>">
        <button type="submit" name="mos" onclick="return confirm('Are you sure you want to end this conversation?');">End this Conversation</button>
    </form>
</div>
<script>
function fetchMessages() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_messages.php?conversation_id=' + <?php echo json_encode($conversation_id); ?>, true);
    xhr.onload = function() {
        if (this.status === 200) {
            var response = JSON.parse(this.responseText);
            if (response.status === 'success') {
                var messagesDiv = document.querySelector('.messages');
                messagesDiv.innerHTML = ''; // Clear existing messages
                response.messages.forEach(function(message) {
                    var messageBox = document.createElement('div');
                    messageBox.className = (message.sender_id == <?php echo json_encode($currentUserId); ?>) ? 'message-right' : 'message-left';
                    messageBox.innerHTML = '<div class="message-box"><div class="message-text">' + 
                        message.message + '</div><div class="timestamp"><small>' + 
                        new Date(message.sent_at).toLocaleString('en-GB', { 
                            hour: '2-digit', 
                            minute: '2-digit', 
                            year: 'numeric', 
                            month: '2-digit', 
                            day: '2-digit',
                            hour12: false 
                        }) + '</small></div></div>';
                    messagesDiv.appendChild(messageBox);
                });
            }
        }
    };
    xhr.send();
}

function scrollToBottom() {
    var messagesDiv = document.querySelector('.messages');
    messagesDiv.scrollTop = messagesDiv.scrollHeight; // Scroll to the bottom
}

// Fetch messages every 5 seconds
setInterval(fetchMessages, 5000);

// Initial load to fetch messages and scroll to the bottom
document.addEventListener('DOMContentLoaded', function () {
    fetchMessages(); // Initial fetch
    scrollToBottom(); // Scroll to the bottom on initial load
});
</script>

<?php include 'footer.php'; ?>
