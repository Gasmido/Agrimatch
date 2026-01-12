<?php
session_start();
require 'config.php';  // Database connection

// Get the search query from the URL
if (isset($_GET['query'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['query']);

    // Prepare the base SQL query
    $sql = "SELECT q.id AS question_id, q.question, u.id AS user_id, u.username, u.profile_picture, q.created_at 
            FROM questions q 
            JOIN users u ON q.user_id = u.id 
            WHERE q.question LIKE '%$search_query%'";

    // If the user is logged in, exclude their questions from the search results
    if (isset($_SESSION['user_id'])) {
        $user_id = intval($_SESSION['user_id']);
        $sql .= " AND q.user_id != $user_id";
    }

    // Sort results by creation date
    $sql .= " ORDER BY q.created_at DESC";

    // Execute the query
    $result = mysqli_query($conn, $sql);

    $search_results = [];

    if ($result && mysqli_num_rows($result) > 0) {
        // Store the search results in an array
        while ($row = mysqli_fetch_assoc($result)) {
            $search_results[] = $row;
        }
    } else {
        $no_results_message = "No questions found for your query.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agri Match</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

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

<script>
    function toggleMobileMenu() {
        var mobileMenu = document.getElementById("mobileMenu");
        mobileMenu.classList.toggle("show"); // Toggle the mobile menu visibility
    }
</script>
    <!-- Search Results Body -->
    <div style="display:flex; align-items: center;justify-content: center;margin-left: auto;margin-right: auto;">
        <div class="form-container" style="min-width:100%">
            <h1>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>

            <div class="questions">
                <?php if (!empty($search_results)): ?>
                    <?php foreach ($search_results as $result): ?>
                        <div class="question-item" style="display: flex; align-items: center; margin-bottom: 15px;">
                            <?php
                            // Use the profile picture from the database, or default if not available
                            $profilePicSrc = !empty($result['profile_picture']) ? "user_image/{$result['profile_picture']}" : "user_image/default.jpg";
                            ?>
                            <!-- Display Profile Picture -->
                            <?php
                            echo "
                            <a href='visit_account.php?user_id=" . htmlspecialchars($result['user_id']) . "'>
                            ";
                            ?>
                            <img src="<?php echo htmlspecialchars($profilePicSrc); ?>" 
                                 alt="Profile Picture" 
                                 style="border-radius: 50%; width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                             </a>
                            <div style="text-align:left">
                                <h3 style="margin: 0;"><?php echo htmlspecialchars($result['question']); ?></h3>
                                <p style="margin: 5px 0 0;">Asked by <strong><?php echo htmlspecialchars($result['username']); ?></strong> on <?php echo htmlspecialchars($result['created_at']); ?></p>
                                
                                <!-- Update the "View Answers" link to direct to message.php with question_id and user_id -->
                                <?php
                                echo "
                                <a href='view_question.php?id=" . htmlspecialchars($result['question_id']) . "' class='view-link'>View Answers</a>"; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><?php echo $no_results_message; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
