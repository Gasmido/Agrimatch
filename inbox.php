<?php
require 'config.php';
include 'head.php';
if (isset($_SESSION['gogo'])) {
    unset($_SESSION['gogo']);
}
if (isset($_SESSION['inbox'])) {
    unset($_SESSION['inbox']);
}
$_SESSION['inbox'] = "yes";
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view your inbox.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the user has an active subscription
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND expiry_date > NOW()");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$subscriptionResult = $stmt->get_result();
$stmt->close();

// Fetch conversations involving the logged-in user
$sql = "SELECT c.id AS conversation_id, c.user1_id, c.user2_id, c.started_at, 
               CASE WHEN c.user1_id = ? THEN u2.username ELSE u1.username END AS username,
               CASE WHEN c.user1_id = ? THEN u2.profile_picture ELSE u1.profile_picture END AS profile_picture
        FROM conversations c
        LEFT JOIN users u1 ON c.user1_id = u1.id
        LEFT JOIN users u2 ON c.user2_id = u2.id
        WHERE c.user1_id = ? OR c.user2_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
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
                <li><a href="questions.php">Your Questions</a></li>
                <li><a href="index.php">Home</a></li>
            <?php else: ?>
                <li><a href="index.php">Home</a></li>
            <?php endif; ?>
        </ul>

        <!-- Mobile Links for Mobile Navigation (with Search Form) -->
        <ul class="nav-links-mobile" id="mobileMenu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="post_question.php">Ask a Question</a></li>
                <li><a href="questions.php">Your Questions</a></li>
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
        mobileMenu.classList.toggle("show");
    }
</script>

<div class="container">
    <h1>Your Inbox</h1>
    <div class="questions">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='question-item' style='display: flex; align-items: center; margin-bottom: 15px;'>";
                
                // Profile picture and username
                $profilePicSrc = !empty($row['profile_picture']) ? "user_image/{$row['profile_picture']}" : "user_image/default.jpg";
                echo "<img src='" . htmlspecialchars($profilePicSrc) . "' alt='Profile Picture' class='profile-pic' style='border-radius: 50%; width: 40px; height: 40px; object-fit: cover; margin-right: 10px;' />";
                
                echo "<div style='text-align:left'>";
                echo "<h3 style='margin: 0;'>" . htmlspecialchars($row['username']) . "</h3>";
                
                // Display when the conversation started
                echo "<p style='margin: 0; color: gray;'>Conversation started at: " . htmlspecialchars($row['started_at']) . "</p>";
                
                // Link to the conversation page
                if ($user_id == $row['user1_id']) {
                    echo "<a href='message.php?question_owner_id=" . htmlspecialchars($row['user1_id']) . " &user_id=" . htmlspecialchars($row['user2_id']) . "' class='view-link'>View Conversation</a>";
                }
                else {
                    echo "<a href='message.php?question_owner_id=" . htmlspecialchars($row['user2_id']) . "&user_id=" . htmlspecialchars($row['user1_id']) . "' class='view-link'>View Conversation</a>";
                }
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<h2 style='background-color: lightgrey'>No conversations at the moment.</h2>";
        }
        ?>
        <?php
        if ($subscriptionResult->num_rows > 0) {
        echo "
        <a href='message.php?question_owner_id=" . htmlspecialchars($user_id) . " &user_id=13' class='view-link'>Chat with Admin</a>";
        }
        ?>
    </div>
</div>

<?php
include 'footer.php';
?>
