<?php

include 'head.php';
include 'config.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
if (isset($_SESSION['gogo'])) {
    unset($_SESSION['gogo']);
    $re = "re";
}else {
    $re = "ra";
}
$questionId = $_GET['id'];


// Fetch the current question from the database
if ($questionId) {
    $stmt = $conn->prepare("
        SELECT questions.*, users.username, users.profile_picture 
        FROM questions 
        JOIN users ON questions.user_id = users.id 
        WHERE questions.id = ?
    ");
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $question = $result->fetch_assoc();
    $stmt->close();
}

// Find the next and previous questions
$nextQuestion = null;
$prevQuestion = null;
$firstQuestionId = null;
$lastQuestionId = null;

if ($questionId) {
    // Get next question
    $stmt = $conn->prepare("SELECT id FROM questions WHERE id > ? ORDER BY id ASC LIMIT 1");
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $nextQuestion = $result->fetch_assoc();
    $stmt->close();

    // Get previous question
    $stmt = $conn->prepare("SELECT id FROM questions WHERE id < ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $prevQuestion = $result->fetch_assoc();
    $stmt->close();

    // Get the first question in the database
    $stmt = $conn->prepare("SELECT id FROM questions ORDER BY id ASC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $firstQuestion = $result->fetch_assoc();
    $firstQuestionId = $firstQuestion['id'];
    $stmt->close();

    // Get the last question in the database
    $stmt = $conn->prepare("SELECT id FROM questions ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $lastQuestion = $result->fetch_assoc();
    $lastQuestionId = $lastQuestion['id'];
    $stmt->close();
}

// Fetch comments for the question
$comments = [];
if ($questionId) {
    $stmt = $conn->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE question_id = ?");
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch comments for the question along with the stars received
$comments = [];
if ($questionId) {
    $stmt = $conn->prepare("
        SELECT 
            comments.*, 
            users.username, 
            users.profile_picture, 
            users.verified, 
            user_stars.stars_received
        FROM comments
        JOIN users ON comments.user_id = users.id
        LEFT JOIN user_stars ON comments.user_id = user_stars.user_id
        WHERE question_id = ?
    ");
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Check if the current user has given stars to a specific comment
function hasUserGivenStars($conn, $userId, $commentId) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as star_count 
        FROM stars_given 
        WHERE user_id = ? AND comment_id = ?
    ");
    $stmt->bind_param("ii", $userId, $commentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $starStatus = $result->fetch_assoc();
    $stmt->close();

    return $starStatus['star_count'] > 0;
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="post_question.php">Ask a Question</a></li>
                <?php if ($re == "ra") {?>
                <li><a href="index.php">Home</a></li>
                <?php } else { ?>
                <li><a href="questions.php">Back</a></li>
                <?php } ?>
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



<!-- Body for Viewing the Question -->
<div style="display:flex; align-items: center;justify-content: center;margin-left: auto;margin-right: auto;margin-bottom: 0px;margin-top: 0px;">
    <div class="form-container" style="min-width: 100%;">
        <!-- Scroll to Footer Button -->
<div class="scroll-to-footer" style="text-align: center; margin-top: 20px;">
    <button onclick="scrollToFooter()" class="btn btn-primary lplp" style="">Scroll to Answer Form</button>
</div>

        <div style='text-align:center;height:40px;color: red;'>
        <?php if ($question): 
            if (isset($_SESSION['tuko'])) {
                    echo "<h2 id='tuko-message'>" . $_SESSION['tuko'] . "</h2><br>";
                    unset($_SESSION['tuko']);
                }
                ?>
                </div>
                <p class="momol"><-- swipe left and right to see next and previous questions --></p>
            <div style="display: flex; align-items: center;box-shadow: 0px 3px 4px black; border-radius:10px;padding: 20px;margin-bottom: 30px;" id="momo">
                <!-- Display the profile picture -->
                <?php
                
                $profilePicSrc = !empty($question['profile_picture']) ? "user_image/{$question['profile_picture']}" : "user_image/default.jpg";
                
               echo "
                 <a href='visit_account.php?user_id=" . htmlspecialchars($question['user_id']) . "'><img src='" . htmlspecialchars($profilePicSrc) . "' alt='Profile Picture' class='profile-pic' style='border-radius: 50%; width: 50px; height: 50px; object-fit: cover; margin-right: 15px;' /></a>";
                ?>
                
                <!-- Display the question title and other details -->
                <div>
                    <h2>Q: <?php echo htmlspecialchars($question['question']); ?></h2>
                    <p>Asked by: <strong><?php echo htmlspecialchars($question['username']); if ($_SESSION['user_id'] == $question['user_id']) {echo " (You)";} ?></strong></p>
                    <p><?php echo nl2br(htmlspecialchars($question['created_at'])); ?></p>
                </div>
            </div>
        <?php else: ?>
            <p>Question not found.</p>
        <?php endif; ?>

        <h3>Answers:</h3>
<!-- Display Existing Comments -->
<div id="comments">
    <div id="comment-list">
        <?php 
        // Check if the user is logged in
        if (isset($_SESSION['user_id']) && $comments != null) {
            for ($i = 0; $i < count($comments); $i++):
 
                // Set the profile picture, use default if not set
                $commentProfilePic = !empty($comments[$i]['profile_picture']) ? "user_image/{$comments[$i]['profile_picture']}" : "user_image/default.jpg";

                // Check if the commenter has 500+ stars
                $isStarredUser = $comments[$i]['stars_received'] >= 500;

                // Initialize $hasGivenStars for the current comment
                $hasGivenStars = hasUserGivenStars($conn, $_SESSION['user_id'], $comments[$i]['id']);
                $you = "";
                
                // If the comment's user ID matches the session user ID, do not blur
                if ($comments[$i]['user_id'] == $_SESSION['user_id']) {
                    $commentClass = ''; // Do not blur if the user is the same
                    $you = "(You)";
                }  else {
                    $commentClass = ''; // Show normally
                }

                // Determine whether to show 'Read More' based on comment length
                $commentText = htmlspecialchars($comments[$i]['comment']);
                $isLongComment = strlen($commentText) > 190;
                $shortText = substr($commentText, 0, 190);
        ?>
            <div class="comment" style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #ccc;">
                
                <!-- Left container for profile picture and username/comment -->
                <div class="left-container <?php echo $commentClass; ?>" style="display: flex; align-items: center; overflow: hidden;">
                    <?php echo "
                    <a href='visit_account.php?user_id=" . htmlspecialchars($comments[$i]['user_id']) . "'> "; ?>
                    <img src="<?php echo htmlspecialchars($commentProfilePic); ?>" 
                        alt="Profile Picture" 
                        style="border-radius: 50%; width: 30px; height: 30px; object-fit: cover; margin-right: 10px;">
                    </a>
                    <!-- Display username and comment -->
                    <div style="max-width: 90%; margin: 0; overflow: hidden;">
                        <strong><?php echo htmlspecialchars($comments[$i]['username']); ?><?php echo $you; ?>: <?php if ($comments[$i]['verified'] == "verified"): ?>
        <img src="image/verified.png" style="width:25px; display: inline; vertical-align: middle; margin-bottom: 3px;padding-left: 0;">
    <?php endif; ?></strong>
                        
                        <span class="comment-text">
                            <?php if ($isLongComment): ?>
                                <span class="short-comment"><?php echo nl2br($shortText); ?>...</span>
                                <span class="full-comment" style="display: none;"><?php echo nl2br($commentText); ?></span>
                                <a href="#" class="read-more" onclick="toggleText(event)">Read More</a>
                            <?php else: ?>
                                <?php echo nl2br($commentText); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                  <?php
                if ($commentClass != '') {
                    echo "<h5>Verified Expert User</h5><h6> (Give stars to see answer.)</h6>";
                }
            ?>

                <!-- Right container for the form and button 
                <div class="right-container" style="text-align: right;">
                    <form method="POST" action="give_stars.php" style="background-color:transparent;box-shadow: none;padding: 0px;">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($comments[$i]['user_id']); ?>">
                        <input type="hidden" name="q_id" value="<?php echo htmlspecialchars($questionId); ?>">
                        <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comments[$i]['id']); ?>">
                        <?php if ($you != "") { ?>
                            <button type="submit" class="bobos" disabled style="background-color: grey"><i class='bx bxs-star'></i></button>
                        <?php } else { ?>
                            <button type="submit" class="bobos"><i class='bx bxs-star'></i></button>
                        <?php } ?>
                    </form>

                </div>
                 -->
            </div>
        <?php endfor; ?>
        <?php } elseif (!isset($_SESSION['user_id'])) { ?>
            <div class="comment">
                
            </div>
        <?php } else { ?>
            <div class="comment">
                <p>No Answers Found...</p>
            </div>
        <?php } ?>
    </div>
</div>



       <!-- Comment Form -->
<div style="display: block; align-content: center;align-items: center; text-align: center;">
    <?php if (isset($_SESSION['user_id'])): ?>
        <form action="submit_comment.php" method="POST" style="align-items: center;margin-left: auto;margin-right: auto;">
            <div class="form-group">
                <div class="counter">
                    <p class="char-count" id="charCount" maxlength="3000">3000 characters remaining</p>
                </div>
                <label for="comment">Your Answer</label>
                <textarea id="comment" name="comment" oninput="countCharacters(this)" required></textarea>
            </div>
            <input type="hidden" name="question_id" value="<?php echo $questionId; ?>">
            <button type="submit">Submit Answer</button>
        </form>
    <?php else: ?>
        <p>Please <a href="login.php">login</a> to answer.</p>
    <?php endif; ?>
</div>


        <!-- Navigation Buttons for Next and Previous Questions -->
        <?php if ($re == "ra") {?>
        <div class="question-navigation">
            <?php if ($prevQuestion): ?>
                <a href="view_question.php?id=<?php echo $prevQuestion['id']; ?>" class="nav-btn prev-btn">Previous Question</a>
            <?php else: ?>
                <a href="view_question.php?id=<?php echo $lastQuestionId; ?>" class="nav-btn prev-btn">Previous Question</a>
            <?php endif; ?>

            <?php if ($nextQuestion): ?>
                <a href="view_question.php?id=<?php echo $nextQuestion['id']; ?>" class="nav-btn next-btn">Next Question</a>
            <?php else: ?>
                <a href="view_question.php?id=<?php echo $firstQuestionId; ?>" class="nav-btn next-btn">Next Question</a>
            <?php endif; ?>
        </div>
    <?php }  ?>
    </div>
</div>
<script>
      function countCharacters(textarea) {
            const maxLengths = 3000;
            const currentLength = textarea.value.length;
            
            // Get the character counter element
            const charCountElement = document.getElementById('charCount');
            
            // Update the character counter
            const remainingCharacters = maxLengths - currentLength;
            charCountElement.textContent = remainingCharacters + ' characters remaining';
            console.log(maxLengths);

            // Add a warning class if characters exceed the limit
            if (remainingCharacters < 0) {
                charCountElement.classList.add('warning');
                textarea.value = textarea.value.substring(0, maxLengths); 
                charCountElement.textContent = '0 characters remaining';
            } else {
                charCountElement.classList.remove('warning');
            }
        }
</script>
<script type="text/javascript">
function toggleText(event) {
    event.preventDefault();
    const readMoreLink = event.target;
    const shortComment = readMoreLink.previousElementSibling.previousElementSibling; // Get the short-comment span
    const fullComment = readMoreLink.previousElementSibling; // Get the full-comment span

    // Toggle between showing the full comment and the short comment
    const isFullCommentVisible = fullComment.style.display === 'inline';
    
    fullComment.style.display = isFullCommentVisible ? 'none' : 'inline';
    shortComment.style.display = isFullCommentVisible ? 'inline' : 'none';
    
    // Update the text of the read more/read less link
    readMoreLink.textContent = isFullCommentVisible ? 'Read More' : 'Read Less';
}
</script>

<script type="text/javascript">
    // Wait for the page to load, then set a timeout to hide the div after 3 seconds
    window.onload = function() {
        setTimeout(function() {
            var messageDiv = document.getElementById('tuko-message');
            if (messageDiv) {
                messageDiv.style.display = 'none'; // Hide the div
            }
        }, 3000); // 3000 milliseconds = 3 seconds
    };
</script>
<script type="text/javascript">
    function scrollToFooter() {
    const footer = document.querySelector('footer'); // Assuming your footer has the <footer> tag
    if (footer) {
        footer.scrollIntoView({ behavior: 'smooth' });
    }
}

</script>
<script>
   // Add event listeners only for the 'momo' div
const momoDiv = document.getElementById('momo');
let touchStartX = 0;
let touchEndX = 0;

function handleSwipe() {
    const swipeDistance = Math.abs(touchEndX - touchStartX); // Calculate swipe distance
    const screenWidth = window.innerWidth; // Get the width of the screen
    const swipeThreshold = screenWidth * 0.30; // Set the threshold to 30% of the screen width

    if (swipeDistance > swipeThreshold) {
        if (touchEndX < touchStartX) {
            // Swipe left - Navigate to the next question
            <?php if ($nextQuestion): ?>
                window.location.href = "view_question.php?id=<?php echo $nextQuestion['id']; ?>";
            <?php else: ?>
                window.location.href = "view_question.php?id=<?php echo $firstQuestionId; ?>";
            <?php endif; ?>
        }

        if (touchEndX > touchStartX) {
            // Swipe right - Navigate to the previous question
            <?php if ($prevQuestion): ?>
                window.location.href = "view_question.php?id=<?php echo $prevQuestion['id']; ?>";
            <?php else: ?>
                window.location.href = "view_question.php?id=<?php echo $lastQuestionId; ?>";
            <?php endif; ?>
        }
    }
}

// Add event listeners to the 'momo' div for touch events
momoDiv.addEventListener('touchstart', function(event) {
    touchStartX = event.changedTouches[0].screenX; // Capture the X coordinate when the touch starts
});

momoDiv.addEventListener('touchend', function(event) {
    touchEndX = event.changedTouches[0].screenX; // Capture the X coordinate when the touch ends
    handleSwipe(); // Call handleSwipe to check swipe distance
});

</script>

<?php
include 'footer.php';
?>
