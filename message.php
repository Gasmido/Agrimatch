<?php
require 'config.php';
include 'head.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $log = "no";
} else {
    $log = "yes";
}

// Ensure required GET parameters are set
if (!isset($_GET['user_id'])) {
    echo "Invalid user.";
    exit();
}

$chatUserId = intval($_GET['user_id']);
if ($chatUserId === 0) {
    echo "Invalid user.";
    exit();
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

// Check if the user has an active subscription
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND expiry_date > NOW()");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$subscriptionResult = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT * FROM conversations WHERE user1_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$checkk = $stmt->get_result();
$stmt->close();

// Check if the conversation is already ended
$stmt = $conn->prepare("
    SELECT * 
    FROM conversation_end ce 
    JOIN conversations c ON ce.conversation_id = c.id 
    WHERE ((c.user1_id = ? AND c.user2_id = ?) OR (c.user1_id = ? AND c.user2_id = ?))
");
$stmt->bind_param("iiii", $user_id, $chatUserId, $chatUserId, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
if ($result->num_rows > 0) {
    $end = "yes";
} else {
    $end = "no";
}

// Check if a conversation already exists
$stmt = $conn->prepare("SELECT * FROM conversations WHERE ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?))");
$stmt->bind_param("iiii", $chatUserId, $user_id, $user_id, $chatUserId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
if ($result->num_rows > 0) {
    $conversation = $result->fetch_assoc();
    $conversation_id = $conversation['id'];
} else {
     $conversation_id = 1;
}


// Fetch the username and profile picture of the chat user
$stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $chatUserId);
$stmt->execute();
$userResult = $stmt->get_result();
$stmt->close();
if ($userResult->num_rows === 0) {
    echo "User not found.";
    exit();
}

$user = $userResult->fetch_assoc();
$chatUsername = $user['username'];
$profilePicture = $user['profile_picture'];
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
      overflow-wrap: break-word; /* Ensures long words wrap */
    }

    .messages {
      max-height: 400px;
      overflow-y: auto;
      padding: 20px;
    }

    .messages-container {
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

    .left .message-box {
      background-color: #e9ecef; /* Lighter gray for received messages */
    }

    .right .message-box {
      background-color: #a2d9ce; /* Light green for sent messages */
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
  <?php if(isset($_SESSION['role'])) { ?>
  <?php if($_SESSION['role'] == "admin") { ?>
<nav class="navbar">
    <div class="navbar-container">
        <img class="ree" src="image/logo.jpg" style="border-radius:50%; max-width: 70px; margin-right: 10px;">
        <a href="admin_home.php" class="brand"><img src="image/logo.jpg" class="non" style="border-radius:50%; max-width: 35px; margin-right: 10px;">AgriMatch</a>

        <!-- Hamburger Menu Button (Mobile View) -->
        <button class="navbar-toggle" onclick="toggleMobileMenu()">☰</button>

        <!-- Regular Links for Desktop -->
         <ul class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="admin_home.php">Home</a></li>
            <?php else: ?>
                <li><a href="admin_home.php">Home</a></li>
            <?php endif; ?>
        </ul>

        <!-- Mobile Links for Mobile Navigation (with Search Form) -->
        <ul class="nav-links-mobile" id="mobileMenu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="admin_home.php">Home</a></li>
            <?php else: ?>
                <li><a href="admin_home.php">Home</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
  <?php } else { ?>
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="post_question.php">Ask a Question</a></li>
                <li><a href="index.php">Home</a></li>
            <?php else: ?>
                <li><a href="index.php">Home</a></li>
            <?php endif; ?>
        </ul>

        <!-- Mobile Links for Mobile Navigation (with Search Form) -->
        <ul class="nav-links-mobile" id="mobileMenu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="post_question.php">Ask a Question</a></li>
                <li><a href="index.php">Home</a></li>
            <?php else: ?>
                <li><a href="index.php">Home</a></li>
            <?php endif; ?>
            
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
<?php } ?>
  <?php } else { ?>
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="post_question.php">Ask a Question</a></li>
                <li><a href="index.php">Home</a></li>
            <?php else: ?>
                <li><a href="index.php">Home</a></li>
            <?php endif; ?>
        </ul>

        <!-- Mobile Links for Mobile Navigation (with Search Form) -->
        <ul class="nav-links-mobile" id="mobileMenu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="post_question.php">Ask a Question</a></li>
                <li><a href="index.php">Home</a></li>
            <?php else: ?>
                <li><a href="index.php">Home</a></li>
            <?php endif; ?>
            
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
<?php } ?>

<?php
/*
if ($subscriptionResult->num_rows === 0) {
    echo "<p style='text-align: center;'>You must have an active subscription to view or send messages.</p>";
    include 'footer.php';
    exit();
}
*/
if (isset($_SESSION['inbox'])) {
    $inbox = $_SESSION['inbox'];
}
else {
    $inbox = "no";
}
if ($subscriptionResult->num_rows === 0 && $inbox == "no") {
    echo "<p style='text-align: center;'>You must be subscribed to send or initiate private messages.</p>";
    include 'footer.php';
    exit();
}
?>
<script>
    function toggleMobileMenu() {
        var mobileMenu = document.getElementById("mobileMenu");
        mobileMenu.classList.toggle("show");
    }
</script>

<div class="messages-container">
    <h2>
        <img src="user_image/<?php echo htmlspecialchars($profilePicture); ?>" alt="<?php echo htmlspecialchars($chatUsername); ?>'s Profile Picture" class="profile-picture">
        <?php echo htmlspecialchars($chatUsername); ?>
    </h2>
    <?php
    $stmt = $conn->prepare("SELECT * FROM private_messages WHERE conversation_id = ? ORDER BY sent_at");
    $stmt->bind_param("i", $conversation_id);
    $stmt->execute();
    $messages = $stmt->get_result();
    ?>
    <div class="messages" id="messages">
        <?php if ($messages->num_rows === 0): ?>
            <p style="text-align: center; color: black;">There are no current messages.</p>
        <?php else: ?>
            <?php while ($row = $messages->fetch_assoc()): ?>
                <div class="<?= $row['sender_id'] == $_SESSION['user_id'] ? 'right' : 'left' ?>">
                    <div class="message-box">
                        <p><?= htmlspecialchars($row['message']) ?></p>
                        <small><?= date('Y-m-d H:i', strtotime($row['sent_at'])) ?></small>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <form action="send_message.php" method="POST" class="message-form">
      <input type="hidden" name="conversation_id" value="<?= $conversation_id ?>">
      <input type="hidden" name="receiver_id" value="<?= $chatUserId ?>">
      <input type="hidden" name="user_id" value="<?= $user_id ?>">
      <textarea name="message" required placeholder="Enter message..." style="resize: none;"></textarea>
      <button type="submit" name="mos" style="width:80px; height: 50px;">Send</button>
    </form>
    <div style="text-align:center;">
        <?php if ($log === "no") { echo "<p>You must <a href='login.php'>LOGIN</a> to message.</p>"; } ?>
    </div>
    <?php if ($log === "yes" && $end === "no") { ?>
    <?php if ($subscriptionResult->num_rows === 0) { ?>
    <form action="give_stars.php" method="POST" style="margin-top: 20px; text-align: center;min-width: 100%;"> 
        <input type="hidden" name="conversation_id" value="<?= $conversation_id ?>">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <input type="hidden" name="receivere_id" value="<?= $_GET['user_id'] ?>">
        <button type="submit" onclick="return confirm('Do you really want to rate this user?');">Rate their responses.</button>
    </form>
    <?php } ?>
    <?php } if ($subscriptionResult->num_rows === 0 && $end === "yes") { ?>
        <button type="submit" disabled>You cannot rate the same user more than 1 time.</button>
    <?php } ?>
</div>
<script type="text/javascript">
    // Scroll to the messages-container div on page load
document.addEventListener('DOMContentLoaded', function () {
    // Existing code
    fetchMessages(); // Initial fetch
    scrollToBottom(); // Scroll to the bottom of messages on initial load

    // Scroll to messages-container after the page reloads
    document.querySelector('.messages-container').scrollIntoView({
        behavior: 'instant', // Smooth scrolling effect
        block: 'start'      // Align to the top of the messages-container
    });
});

</script>
    <script>
function fetchMessages() {
    var conversationId = <?php echo json_encode($conversation_id); ?>;
    var currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
    var otherUserId = <?php echo json_encode($gago); ?>;

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_messages.php?conversation_id=' + conversationId + '&user_id=' + currentUserId + '&other_user_id=' + otherUserId, true);
    xhr.onload = function() {
        if (this.status === 200) {
            var response = JSON.parse(this.responseText);
            if (response.status === 'error') {
                document.getElementById('messages').innerHTML = "<p style='text-align: center; color: red;'>" + response.message + "</p>";
                clearInterval(fetchMessagesInterval); // Stop further fetches if no subscription
            } else if (response.status === 'success' && response.messages.length > 0) {
                var messagesDiv = document.getElementById('messages');
                messagesDiv.innerHTML = ''; // Clear existing messages

                response.messages.forEach(function(message) {
                    var messageBox = document.createElement('div');
                    messageBox.className = (message.sender_id == currentUserId) ? 'right' : 'left';
                    messageBox.innerHTML = '<div class="message-box"><p>' + message.message + '</p><small>' + 
                        new Date(message.sent_at).toLocaleString('en-GB', { 
                            hour: '2-digit', 
                            minute: '2-digit', 
                            year: 'numeric', 
                            month: '2-digit', 
                            day: '2-digit',
                            hour12: false 
                        }) + '</small></div>';
                    messagesDiv.appendChild(messageBox);
                });
                scrollToBottom();
            } else {
                document.getElementById('messages').innerHTML = '<p style="text-align: center; color: #999;">There are no current messages.</p>';
            }
        }
    };
    xhr.send();
}

var fetchMessagesInterval = setInterval(fetchMessages, 5000);

document.addEventListener('DOMContentLoaded', function () {
    fetchMessages();
    scrollToBottom();
});

</script>


<?php include 'footer.php'; ?>
