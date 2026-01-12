<?php
require 'config.php';
include 'head.php';

if (isset($_SESSION['gogo'])) {
    unset($_SESSION['gogo']);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to ask a question.";
    exit();  
}

$user_id = $_SESSION['user_id'];

// Fetch user details to check verification status, question count, and ads watched
$user_query = $conn->prepare("SELECT verified, question_asked, last_question_time, ads_watched FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user_data = $user_result->fetch_assoc();

$verified = $user_data['verified'];
$question_asked = $user_data['question_asked'];
$last_question_time = $user_data['last_question_time'];
$ads_watched = $user_data['ads_watched'];

date_default_timezone_set('Asia/Manila');


$current_time = new DateTime();
$last_question_time = new DateTime($last_question_time);

// Calculate time difference
$time_diff = $current_time->getTimestamp() - $last_question_time->getTimestamp();
$hours_since_last_question = $time_diff / 3600;

// Reset ads_watched and question_asked if 24 hours have passed
if ($hours_since_last_question >= 24) {
    $reset_query = $conn->prepare("UPDATE users SET ads_watched = 0, question_asked = 0 WHERE id = ?");
    $reset_query->bind_param("i", $user_id);
    $reset_query->execute();
    $ads_watched = 0; // Update local variable after reset
    $question_asked = 0;
}

// Handle question posting
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question = $_POST['question'];
    $pito = $_POST['topic'];

    // Insert question into the database
    $sql = "INSERT INTO questions (user_id, question, topic) VALUES ('$user_id', '$question', '$pito')";

    if (mysqli_query($conn, $sql)) {
        echo "Question posted successfully!";
        header('Location: questions.php');  
        
        // Update question count and last question time for non-verified users
        if ($verified == "not_verified") {
            $new_question_count = ($hours_since_last_question >= 24) ? 1 : $question_asked + 1;
            $update_user_query = $conn->prepare("UPDATE users SET question_asked = ?, last_question_time = NOW() WHERE id = ?");
            $update_user_query->bind_param("ii", $new_question_count, $user_id);
            $update_user_query->execute();
        }
        
        exit(); 
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
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

<script>
function toggleMobileMenu() {
    var mobileMenu = document.getElementById("mobileMenu");
    mobileMenu.classList.toggle("show"); // Toggle the mobile menu visibility
}
</script>

<!-- Form to Post a Question -->
<div style="display:flex; align-items: center;justify-content: center;margin-left: auto;margin-right: auto;">
    <div style="display: block; align-content: center;align-items: center; text-align: center;">
        <div class="form-container" style="min-width:100%">
            <?php
if ($verified == "not_verified" && $question_asked >= 3 && $hours_since_last_question < 24) {
    $time_remaining = 24 - $hours_since_last_question;
    echo "You have reached the maximum number of questions (3). Please wait " . ceil($time_remaining) . " hours to ask another 3 free questions.";
    $button_disabled = ($ads_watched >= 2) ? "disabled" : "";
?>
    <form action="watch_video.php" method="GET" style="margin-left: auto;margin-right: auto;">
        <button type="submit" <?php echo $button_disabled; ?>>
            <?php if ($ads_watched >= 2) { echo "Sorry, a maximum of 2 ads can only be watched."; } else { ?>
        Watch a 30-Second Video to Ask Another Question</button>
    <?php } ?>
    </form>
<?php
} else {
?>
    <h2>Ask a Question</h2>
    <form action="post_question.php" method="POST" style="align-items: center;margin-left: auto;margin-right: auto;">
        <div class="form-group">
            <label for="topic">Select a topic for this question:</label>
            <select name="topic" id="topic" required>
                <option value="" disabled selected>Choose a topic</option>
                <option value="Animal Science">Animal Science</option>
                <option value="Crop Science">Crop Science</option>
                <option value="Crop Protection">Crop Protection</option>
                <option value="Soils">Soils</option>
                <option value="Agricultural Management">Agricultural Management</option>
                <option value="Agricultural Extension">Agricultural Extension</option>
            </select>
            <label for="question">Question</label>
            <textarea id="question" name="question" required></textarea>
        </div>
        <button type="submit">Post Question</button>
    </form>
<?php } ?>

        </div>
    </div>
</div>

<?php
include 'footer.php';
?>
