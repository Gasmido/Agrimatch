<?php
require 'config.php';
include 'head.php';

if (isset($_SESSION['gogo'])) {
    unset($_SESSION['gogo']);
}
if (isset($_SESSION['inbox'])) {
    unset($_SESSION['inbox']);
}
$result2 = $conn->query("
    SELECT posts.*, users.username, users.profile_picture, users.verified 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.date_posted DESC
");

?>

<script>
    window.onload = function() {
        <?php if (isset($_SESSION['login_success'])): ?>
            alert("Login successful!");
            <?php unset($_SESSION['login_success']); ?>
        <?php endif; ?>
    };
</script>
<script>
// Toggle the dropdown menu on click
function toggleDropdown() {
    var dropdownMenu = document.getElementById("dropdownMenu");
    dropdownMenu.classList.toggle("show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
    if (!event.target.matches('.dropbtn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
}
</script>
<nav class="navbar">
    <div class="navbar-container">
        <img class="ree" src="image/logo.jpg" style="border-radius:50%; max-width: 70px; margin-right: 10px;">
        <a href="index.php" class="brand"><img src="image/logo.jpg" class="non" style="border-radius:50%; max-width: 35px; margin-right: 10px;">AgriMatch</a>

        <button class="navbar-toggle" onclick="toggleMobileMenu()">☰</button>

        <form class="search-form" action="search.php" method="GET">
            <input type="text" placeholder="Search questions..." name="query" required>
            <button type="submit">Search</button>
        </form>

        <ul class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="post_question.php">Ask a Question</a></li>
                <li class="dropdown">
                    <a href="#" class="dropbtn" onclick="toggleDropdown()">Account Settings</a>
                    <div class="dropdown-content" id="dropdownMenu">
                        <a href="account_settings.php">Account Information</a>
                        <a href="pricing.php">Subscription Plans</a>
                        <a href="questions.php">Your Questions</a>
                        <a href="inbox.php">Inbox</a>
                        <a href="post.php">Make a Post</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>

        <ul class="nav-links-mobile" id="mobileMenu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="post_question.php">Ask a Question</a></li>
                <li><a href="account_settings.php">Account Information</a></li>
                <li><a href="pricing.php">Subscription Plans</a></li>
                <li><a href="questions.php">Your Questions</a></li>
                <li><a href="inbox.php">Inbox</a></li>
                <li><a href="post.php">Make a Post</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
            
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


<div class="hoho" style="background-image: url('image/handt.jpg'); background-size: 100% 140%;background-repeat: no-repeat; display: flex; flex-direction: column; align-items: center; text-align: center; color: white;"> 
    <div class="hero-text"> 
        <h1>Welcome to AgriMatch</h1>
        <p>Learn. Share. Earn.</p>
        <p>The First Scientific Agricultural Community in the Philippines.</p>
    </div>
</div>


<div class="container"> 
    <div class="mission-vision">
        <div class="section">
            <h2>Our Mission</h2>
            <p>To empower farmers and agricultural extension workers by providing a comprehensive, collaborative platform for learning, sharing experiences, and finding practical solutions. Through accessible knowledge and peer support, we aim to enhance productivity, sustainability & profitability in agriculture.</p>
        </div>
        <div class="section">
            <h2>Our Vision</h2>
            <p>To become the leading digital platform for agriculture education and innovation, where learners and agriculture experts collaborate, connect, learn, fostering a sustainable future and thriving agricultural communities.</p>
        </div>
    </div>

    <div class="principles-services">
        <div class="section">
            <h2>Core Principles</h2>
            <p><strong>Comprehend, Adapt, Relate, and Empathize (CARE)</strong> - Agrimatch demonstrates its commitment to understanding and supporting farming communities.</p>
        </div>
        <div class="section">
            <h2>Our Services</h2>
            <p>Agrimatch is an innovation platform designed to connect farmers with agricultural experts and peers. By facilitating collaboration and knowledge sharing, Agrimatch empowers users to access valuable resources, share experiences, and discover practical solutions.</p>
        </div>
    </div>

   <div class="questions">
    <h1>Latest Questions</h1>

    <?php
    // Define topics for separation
    $topics = [
        'Animal Science',
        'Crop Science',
        'Crop Protection',
        'Soils',
        'Agricultural Management',
        'Agricultural Extension'
    ];

    // Loop through each topic
    foreach ($topics as $topic) {
        // Fetch the questions by topic and exclude questions from the current user

        if (isset($_SESSION["user_id"])) {
            $sql = "SELECT q.id AS question_id, q.question, u.id AS user_id, u.username, u.profile_picture, q.created_at 
                    FROM questions q
                    JOIN users u ON q.user_id = u.id
                    WHERE q.topic = '$topic'
                    ORDER BY q.created_at DESC";
        } else {
            $sql = "SELECT q.id AS question_id, q.question, u.id AS user_id, u.username, u.profile_picture, q.created_at 
                    FROM questions q
                    JOIN users u ON q.user_id = u.id
                    WHERE q.topic = '$topic'
                    ORDER BY q.created_at DESC";
        }

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            echo "<div class='topic-section'>";
            echo "<h2>" . htmlspecialchars($topic) . "</h2>";

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='question-item' style='display: flex; align-items: center; margin-bottom: 15px; margin-top: 15px;'>";

                // Use the profile picture from the database, or default if not available
                $profilePicSrc = !empty($row['profile_picture']) ? "user_image/{$row['profile_picture']}" : "user_image/default.jpg";

                // Display the profile picture before the username
                echo "<a href='visit_account.php?user_id=" . htmlspecialchars($row['user_id']) . "'><img src='" . htmlspecialchars($profilePicSrc) . "' alt='Profile Picture' class='profile-pic' style='border-radius: 50%; width: 40px; height: 40px; object-fit: cover; margin-right: 10px;' /></a>";

                // Display the question details in the same line
                echo "<div style='text-align:left'>";
                echo "<h3 style='margin: 0;'>" . htmlspecialchars($row['question']) . "</h3>";
                echo "<p style='margin: 5px 0 0;'>Asked by <strong>" . htmlspecialchars($row['username']) . "</strong> on " . htmlspecialchars($row['created_at']) . "</p>";

                // Update the "View Answers" link to direct to view_question.php
                echo "<a href='view_question.php?id=" . htmlspecialchars($row['question_id']) . "' class='view-link'>View Answers</a>";
                
                echo "</div>";  // Close question details

                echo "</div>";  // Close question item
                echo "<hr>";
            }

            echo "</div>";  // Close topic-section
        } else {
            echo "<div class='topic-section'>";
            echo "<h2>" . htmlspecialchars($topic) . "</h2>";
            echo "<p>No questions found in this topic.</p>";
            echo "</div>";
            echo "<hr>";
        }
    }
    ?>
</div>
<style type="text/css">
    .topic-section {
        text-align: left;
        margin-bottom: 20px;
        border-radius: 3px;

        padding: 10px;
    }
</style>
<div style="margin-top:30px;margin-bottom: 30px;">
    <h1>Agricultural Threads</h1>
    <hr>

    <?php if ($result2->num_rows === 0): ?>
        <h3 style="color: grey">(There are no posts at the moment.)</h3>
    <?php else: ?>
        <div class="posts-container"> 
            <?php while ($row2 = $result2->fetch_assoc()): ?>
                <div class="post-box"> 
                    <div class="post-content">
                        <!-- User Profile Picture and Username -->
                        <div class="post-user">
                            <?php echo "
                            <a href='visit_account.php?user_id=" . htmlspecialchars($row2['user_id']) . "'><img src='user_image/" . htmlspecialchars($row2['profile_picture']) . "' alt='Profile Picture' class='profile-pic' style='border-radius: 50%; width: 40px; height: 40px; object-fit: cover; margin-right: 10px;' /></a>"; ?>
<span class="username"><?php echo htmlspecialchars($row2['username']); ?><?php if ($row2['verified']== "verified"): ?>
        <img src="image/verified.png" style="width:25px; display: inline; vertical-align: middle; margin-bottom: 3px;padding-left: 0;">
    <?php endif; ?></span>

                        </div>

                      <!-- Post Content -->
                       <?php
// Assuming $row2 contains the current post data
$stmt = $conn->prepare("SELECT * FROM post_hearts WHERE user_id = ? AND post_id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $row2['id']);
$stmt->execute();
$hearted = $stmt->get_result()->num_rows > 0;
?>

<!-- Post Content -->
<p style="color:grey"><strong>Posted on:</strong> <?php echo htmlspecialchars($row2['date_posted']); ?></p>
<?php if (!empty($row2['caption'])): ?>
    <p class="caption" id="caption-<?php echo $row2['id']; ?>">
        <?php echo htmlspecialchars($row2['caption']); ?>
    </p>
    <?php if (strlen($row2['caption']) > 600): ?>
        <button class="read-more" data-post-id="<?php echo $row2['id']; ?>">Read More</button>
    <?php endif; ?>
<?php endif; ?>

<?php if ($row2['media_type'] === 'photo'): ?>
    <img src="uploads/photos/<?php echo htmlspecialchars($row2['media_name']); ?>" alt="Photo" style="max-width: 100%; height: auto;">
<?php elseif ($row2['media_type'] === 'video'): ?>
    <video controls style="max-width: 100%; height: auto;">
        <source src="uploads/videos/<?php echo htmlspecialchars($row2['media_name']); ?>" type="video/mp4">
    </video>
<?php endif; ?>

<!-- Heart Button -->
<div class="heart-container">
    <button class="heart-button" data-post-id="<?php echo $row2['id']; ?>" 
        <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
        <?php if ($hearted): ?>
            <i class='bx bxs-heart' style="color: red;"></i> 
        <?php else: ?>
            <i class='bx bx-heart' style="color: black;"></i> 
        <?php endif; ?>
    </button>
    <span class="heart-count"><?php echo htmlspecialchars($row2['hearts_received']); ?></span>
</div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Toggle Read More
    document.querySelectorAll(".read-more").forEach(button => {
        button.addEventListener("click", () => {
            const postId = button.getAttribute("data-post-id");
            const caption = document.getElementById(`caption-${postId}`);
            
            if (button.textContent === "Read More") {
                caption.textContent = caption.getAttribute("data-full-caption");
                button.textContent = "Read Less";
            } else {
                caption.textContent = caption.getAttribute("data-short-caption");
                button.textContent = "Read More";
            }
        });
    });

    // Toggle Heart
    document.querySelectorAll(".heart-button").forEach(button => {
        button.addEventListener("click", () => {
            const postId = button.getAttribute("data-post-id");
            const userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;

            if (!userId) {
                alert("You must be logged in to heart posts.");
                return;
            }

            fetch("update_hearts.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `post_id=${postId}`,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const heartCount = button.nextElementSibling;
                        if (data.action === "added") {
                            button.innerHTML = "<i class='bx bxs-heart' style='color: red;'></i>";
                            heartCount.textContent = parseInt(heartCount.textContent) + 1;
                        } else if (data.action === "removed") {
                            button.innerHTML = "<i class='bx bx-heart' style='color: black;'></i>";
                            heartCount.textContent = parseInt(heartCount.textContent) - 1;
                        }
                    } else {
                        alert("Failed to update hearts.");
                    }
                })
                .catch(err => console.error("Error:", err));
        });
    });

    // Initialize captions
    document.querySelectorAll('.caption').forEach(caption => {
        const fullText = caption.textContent;
        if (fullText.length > 600) {
            const shortText = fullText.substring(0, 600) + '...';
            caption.setAttribute('data-full-caption', fullText);
            caption.setAttribute('data-short-caption', shortText);
            caption.textContent = shortText;
        }
    });
});

</script>
<style>
    .read-more {
        margin-bottom: 15px;
    }
    .post-user {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.profile-picture {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
}

.username {
    font-size: 16px;
    font-weight: bold;
    color: #333;
}

.posts-container {
    margin-top: 10px;
    display: grid;
    max-width: 800px;
    gap: 20px; 
    text-align: left;
    margin-left: auto;
    margin-right: auto;
}

.post-box {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px; 
    overflow: hidden; 
}

.heart-container {
    display: flex;
    align-items: center;
    margin-top: 10px; 
    text-align: left;
}

.heart-button {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: red;
    margin-right: 8px;
    transition: transform 0.2s ease;
     text-align: right;
}

.heart-button:hover {
    transform: scale(1.01);
    background-color: transparent;
    color: red;
}

.heart-count {
    font-size: 18px;
    font-weight: bold;

}
</style>

<h1>Ranking</h1>
<hr>
<?php
// Fetch top 10 verified users by stars_received in specific topics
$query = "
    SELECT 
        u.id, 
        u.username, 
        u.topics, 
        u.verified,
        u.profile_picture, 
        us.badge, 
        us.stars_received as total_stars 
    FROM 
        users u 
    JOIN 
        user_stars us 
    ON 
        u.id = us.user_id 
    WHERE 
        u.verified = 'verified' 
        AND u.topics IN ('Animal Science', 'Crop Science', 'Crop Protection', 'Soils', 'Agricultural Management', 'Agricultural Extension')
    GROUP BY 
        u.id 
    ORDER BY 
        total_stars DESC 
    LIMIT 10";

$result = $conn->query($query);

// Check for results
if ($result->num_rows > 0) {
    echo '<div class="top-users">';
    
    
    // Group users by topics
    $topics = [
        'Animal science' => [],
        'Crop science' => [],
        'Crop protection' => [],
        'Soils' => [],
        'Agricultural management' => [],
        'Agricultural extension' => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        $topics[$row['topics']][] = $row;
    }

    // Loop through each topic and display users in boxes
    foreach ($topics as $topic => $users) {

        if (count($users) > 0) {
            echo '<div class="topic-box">';
            echo '<h2>' . htmlspecialchars($topic) . '</h2>';
            echo '<ol>';
            foreach ($users as $user) {
                if (isset($user['badge'])) {
                    $badge = $user['badge'];
                } else {
                    $badge = null; // No badge found
                }
                // Determine the badge image path based on the badge level
                $badgeImageSrc = null;
                if ($badge == 3) {
                    $badgeImageSrc = "image/3rd.png";
                } elseif ($badge == 2) {
                    $badgeImageSrc = "image/2nd.png";
                } elseif ($badge == 1) {
                    $badgeImageSrc = "image/1st.png";
}
                echo '<li>';
echo "
                            <a href='visit_account.php?user_id=" . htmlspecialchars($user['id']) . "'><img src='user_image/" . htmlspecialchars($user['profile_picture']) . "' alt='Profile Picture' class='profile-pic' style='border-radius: 50%; width: 40px; height: 40px; object-fit: cover; margin-right: 10px;' /></a>"; 
?>
    <?php if (isset($badgeImageSrc)) { ?>
        <img src="<?php echo htmlspecialchars($badgeImageSrc); ?>" alt="Badge" 
               class='profile-pic' style='border-radius: 50%; width: 25px; height: 25px; object-fit: cover; margin-right: 10px;transform: translate(-30px, 0px);margin: 0;' />
    <?php } ?>
    <?php 
                echo '<strong>' . htmlspecialchars($user['username'])  . '</strong>'; if ($user['verified'] == "verified"): echo ' <img src="image/verified.png" style="width:25px; display: inline; vertical-align: middle; margin-bottom: 3px;padding-left: 0;">'; endif; echo '- ★ ' . htmlspecialchars($user['total_stars']);

                echo '</li>';
            }
            echo '</ol>';
            echo '</div>';
        }
    }

    echo '</div>';
} else {
    echo '<div class="top-users">';
    echo '<h2>No results found.</h2>';
    echo '</div>';
}

$conn->close();
?>


<style>
.top-users {
    font-family: Arial, sans-serif;
    margin: 20px;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: flex;
}

.top-users h2 {
    font-size: 1.5em;
    margin-bottom: 20px;
    color: blue;
}

.topic-box {
    margin-bottom: 20px;
    background-color: white;
    border-radius: 8px;
    padding: 15px;
    border: 2px solid black;
    max-width: 500px;
    margin-right: auto;
    margin-left: auto;
}

.topic-box h3 {
    margin-top: 0;
    color: blue;

}

.topic-box ol {
    margin: 0;
    padding: 0 20px;

}

.topic-box li {
    margin: 10px 0;
    font-size: 1.1em;
}

.topic-box strong {
    color: blue;
}
</style>


<hr>
<div style="border: 1px solid #ccc; padding: 20px; border-radius: 10px;margin-top: 100px;"> 
  <h1 style="text-align: left;margin-bottom: 20px;">Step-by-Step Guide: Subscription Payment Process</h1><br>

  <h3 style="text-align: left">Login to Your AGRIMATCH Account</h3>
  <p style="text-align: left;margin-bottom: 20px;">Access the AGRIMATCH website and log in using your registered email and password.</p>

  <h3 style="text-align: left">Navigate to the Subscription Page</h3>
  <p style="text-align: left;margin-bottom: 20px;">Go to the "Subscription" section in your profile to view available plans.</p>

  <h3 style="text-align: left">Select Your Subscription Plan</h3>
  <p style="text-align: left;margin-bottom: 20px;">Choose the plan that fits your needs and proceed to payment.</p>

  <h3 style="text-align: left">Choose Your Payment Method</h3>
  <p style="text-align: left;margin-bottom: 20px;">Select your preferred e-wallet (e.g., GCash or Maya) for payment.</p>

  <h3 style="text-align: left">Complete Payment</h3>
  <p style="text-align: left;margin-bottom: 20px;">Enter the required payment details on the e-wallet page and confirm.</p>

  <h3 style="text-align: left">Confirmation and Activation</h3>
  <p style="text-align: left;margin-bottom: 20px;">After successful payment, your subscription will be activated automatically. You’ll receive a confirmation message, and you can now start using AGRIMATCH features.</p>

  <h3 style="text-align: left">Start Connecting</h3>
  <p style="text-align: left;margin-bottom: 20px;">Begin posting questions or offering expertise on the platform.</p>
</div>
<style>
  .faq-container {
    width: 80%;
    margin: 0 auto;
    margin-top: 80px;
    margin-bottom: 100px;
  }

  .faq-item {
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
  }

  .faq-question {
    background-color: #f9f9f9;
    padding: 15px;
    cursor: pointer;
    border: 1px solid #ddd;
    font-weight: bold;
    position: relative; 
  }

  .faq-question::after {
    content: '\25BC'; 
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
    font-size: 12px;
  }

  .faq-question.active::after {
    content: '\25B2'; 
  }

  .faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out; /* Smooth transition for max-height */

}

.faq-answer.show {
    max-height: 200px; /* Adjust this value based on content */
}

</style>
<div class="faq-container">
  <h1>FAQs</h1>

  <div class="faq-item">
    <div class="faq-question">1. What is AGRIMATCH?</div>
    <div class="faq-answer">
      <p>AGRIMATCH is a website that connects farmers with agricultural experts. Farmers use it to get advice or help on farming issues, while experts use it to share their knowledge. </p>
    </div>
  </div>

  <div class="faq-item">
    <div class="faq-question">2. How does AGRIMATCH work?</div>
    <div class="faq-answer">
      <p>Farmers post questions or concerns, after that experts respond directly in the website, and farmers rate their service, helping to boost expert rankings. Both farmers and experts need a subscription to access these features.</p>
    </div>
  </div>

  <div class="faq-item">
    <div class="faq-question">3. Do I need a subscription to use AGRIMATCH?</div>
    <div class="faq-answer">
      <p>Yes, both farmers and experts need an active subscription to interact on AGRIMATCH. Subscriptions ensure access to all features and expert advice.</p>
    </div>
  </div>

  <div class="faq-item">
    <div class="faq-question">4. How do I pay for my AGRIMATCH subscription?</div>
    <div class="faq-answer">
      <p>You can pay through popular e-wallets like GCash and Maya. After payment, your subscription is immediately activated, allowing you to access the platform.</p>
    </div>
  </div>

  <div class="faq-item">
    <div class="faq-question">5. What happens if I don’t pay my subscription fee on time?</div>
    <div class="faq-answer">
      <p>If your subscription expires, you won’t be able to interact with experts or post questions until you renew your subscription. Once you make the payment, your access will be restored immediately.</p>
    </div>
  </div>

  <div class="faq-item">
    <div class="faq-question">6. How do I get help if I’m having trouble with AGRIMATCH?</div>
    <div class="faq-answer">
      <p>AGRIMATCH has a customer support team ready to assist you. If you have issues with payments, your subscription, or using the site, contact support through the "Help" or "Contact Us" section on the website.</p>
    </div>
  </div>
</div>

<script>
  const faqQuestions = document.querySelectorAll('.faq-question');

faqQuestions.forEach(question => {
  question.addEventListener('click', () => {
    question.classList.toggle('active');
    const answer = question.nextElementSibling;

    // Smooth transition using max-height
    if (answer.style.maxHeight) {
      answer.style.maxHeight = null;
    } else {
      // Briefly set to block to calculate scrollHeight
      answer.style.display = 'block'; 
      answer.style.maxHeight = answer.scrollHeight + 'px';
      answer.style.display = ''; // Remove inline style
    }
  });
});
</script>
<div style="background-color: #4CAF50; color: white; padding: 20px; width:50%;margin-left:auto;margin-right: auto;">
  <h1>CONTACT US</h1>
    <hr>
    <div style="text-align:left;">
  <p style="margin-top:20px"><i class='bx bxs-envelope'></i>  agrimatch@gmail.com</p>
  <p style="margin-top:10px"><i class='bx bxs-phone'></i>  09959053603</p>
</div>
</div>

</div>

<?php
include 'footer.php';
?>