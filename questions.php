<?php
require 'config.php';
include 'head.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view your questions.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch questions posted by the logged-in user, ordered by question_id
$sql = "SELECT q.id AS question_id, q.question, u.username, u.profile_picture 
        FROM questions q
        JOIN users u ON q.user_id = u.id
        WHERE q.user_id = ?
        ORDER BY q.id ASC";  // Order by question_id
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

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
                <li><a href="inbox.php">Inbox</a></li>
                <li><a href="index.php">Home</a></li>
            <?php else: ?>
                <li><a href="index.php">Home</a></li>
            <?php endif; ?>
        </ul>

        <!-- Mobile Links for Mobile Navigation (with Search Form) -->
        <ul class="nav-links-mobile" id="mobileMenu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="post_question.php">Ask a Question</a></li>
                <li><a href="inbox.php">Inbox</a></li>
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

<script>
    function toggleMobileMenu() {
        var mobileMenu = document.getElementById("mobileMenu");
        mobileMenu.classList.toggle("show"); // Toggle the mobile menu visibility
    }
</script>

<div class="container">
    <h1>Your Questions</h1>
    <div class="questions">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Get question details
                $question_id = $row['question_id'];
                $username = htmlspecialchars($row['username']);
                $profilePicSrc = !empty($row['profile_picture']) ? "user_image/{$row['profile_picture']}" : "user_image/default.jpg";

                // Display the question item
                echo "<div class='question-item' style='display: flex; align-items: center; margin-bottom: 15px;'>";

                // Question title and username
                echo "<div style='text-align: left;min-width:100%'>";
                echo "<h3 style='margin: 0;'>Your Question:</h3>";
                echo "<p style='margin: 0; color: gray;'><strong>" . htmlspecialchars($row['question']) . "</strong></p>";
                echo "<a href='view_question.php?id=" . htmlspecialchars($row['question_id']) . "' class='view-link'>View Answers</a>";
                $_SESSION['gogo'] = "yes";
                /* Fetch users who have messaged about this question
                $message_sql = "SELECT DISTINCT u.username, u.profile_picture,
                                        CASE WHEN pm.sender_id = ? THEN pm.receiver_id ELSE pm.sender_id END AS sender_id
                                FROM private_messages pm
                                JOIN users u ON u.id = CASE WHEN pm.sender_id = ? THEN pm.receiver_id ELSE pm.sender_id END
                                WHERE pm.question_id = ?";
                $message_stmt = $conn->prepare($message_sql);
                $message_stmt->bind_param("iii", $user_id, $user_id, $question_id);
                $message_stmt->execute();
                $message_result = $message_stmt->get_result();

                // Display users who have messaged about the question
                if ($message_result->num_rows > 0) {
                    echo "<p style='margin-top: 5px; color: gray;'>Users who have messaged you:</p>";
                    echo "<ul style='margin: 0; padding-left: 20px;min-width:100%'>";
                    while ($msg_row = $message_result->fetch_assoc()) {
                        echo "<li style='display: flex; align-items: center; margin-bottom: 15px;'>";

                        // Profile picture of the sender
                        $senderProfilePicSrc = !empty($msg_row['profile_picture']) ? "user_image/{$msg_row['profile_picture']}" : "user_image/default.jpg";
                        echo "<img src='" . htmlspecialchars($senderProfilePicSrc) . "' alt='Profile Picture' class='profile-pic' style='border-radius: 50%; width: 40px; height: 40px; object-fit: cover; margin-right: 10px;' />";

                        // Username and link
                        echo "<div style='text-align:left;'>";
                        echo "<h4 style='margin: 0;'>" . htmlspecialchars($msg_row['username']) . "</h4>";

                        // Use the sender_id from the fetched message result for the link
                        $sender_id = htmlspecialchars($msg_row['sender_id']);
                        echo "<a href='message.php?question_id=" . htmlspecialchars($question_id) . "&question_owner_id=" . htmlspecialchars($user_id) . "&user_id=" . $sender_id . "' class='view-link'>View Conversation</a>";
                        echo "</div>"; // Close inner div for username and link
                        echo "</li>"; // Close list item for the user message
                    }
                    echo "</ul>"; // Close unordered list
                } else {
                    echo "<p style='margin-top: 5px; color: gray;'>No messages received about this question.</p>";
                }
                */
                echo "</div>"; // Close inner div
                echo "</div>"; // Close question-item
            }
        } else {
            echo "<h2 style='background-color: lightgrey'>No questions posted yet.</h2>";
        }
        ?>
    </div>
</div>

<?php
include 'footer.php';
?>
