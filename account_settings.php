<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'config.php';  

// Prepare the SQL query with a placeholder
$stmt = $conn->prepare("
    SELECT posts.*, users.username, users.profile_picture 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.user_id = ? 
    ORDER BY posts.date_posted DESC
");

// Bind the session user ID to the placeholder
$stmt->bind_param("i", $_SESSION['user_id']);

// Execute the prepared statement
$stmt->execute();

// Get the result of the query
$result2 = $stmt->get_result();


$user_id = $_SESSION['user_id'];

if ($user_id) {
    // Fetch user's verification status
    $sql = "SELECT verified, topics FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $is_verified = $user['verified'];
        $selected_topic = $user['topics'];

        // Check if the form is submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_verified == "verified") {
            $topic = $_POST['topic'] ?? '';

            // Update the 'topics' column for this user
            $update_sql = "UPDATE users SET topics = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $topic, $user_id);

            if ($update_stmt->execute()) {
                echo "<script>alert('Topic updated successfully!');</script>";
                $selected_topic = $topic; // Update local variable for display
            } else {
                echo "<script>alert('Failed to update topic.');</script>";
            }
        }
    } else {
        echo "User not found.";
    }
} else {
    echo "User not logged in.";
}

// Handle Post Deletion
if (isset($_POST['delete_post'])) {
    $post_id = $_POST['delete_post'];
    
    // Check if the post belongs to the logged-in user (security measure)
    $stmt_check_user = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt_check_user->bind_param("i", $post_id);
    $stmt_check_user->execute();
    $result_check_user = $stmt_check_user->get_result();

    if ($result_check_user && $result_check_user->num_rows > 0) {
        $post_owner = $result_check_user->fetch_assoc()['user_id'];
        
        // Only allow deletion if the post belongs to the logged-in user
        if ($post_owner == $_SESSION['user_id']) {
            // Delete the post (and related media files if necessary)
            $stmt_delete_post = $conn->prepare("DELETE FROM posts WHERE id = ?");
            $stmt_delete_post->bind_param("i", $post_id);
            $stmt_delete_post->execute();
            
            // Optional: Delete related media files (e.g., photo, video) if any
            // You can add file deletion logic here based on the media type
            
            // Redirect back to the account settings page after deletion
            header("Location: account_settings.php");
            exit();
        } else {
            // If the post doesn't belong to the current user, show an error (optional)
            $error = "You cannot delete this post.";
        }
    } else {
        $error = "Post not found.";
    }
}


// Query to get user information, including profile picture path
$sql_user_info = "SELECT username, profile_picture FROM users WHERE id = '$user_id'";
$result_user_info = mysqli_query($conn, $sql_user_info);

if ($result_user_info && mysqli_num_rows($result_user_info) > 0) {
    $user = mysqli_fetch_assoc($result_user_info);
} else {
    $error = "Unable to retrieve user information.";
}

// Profile picture path handling
$profilePicSrc = "user_image/" . $user['profile_picture'];



// Query to count the number of questions asked by the user
$sql_question_count = "SELECT COUNT(*) AS question_count FROM questions WHERE user_id = '$user_id'";
$result_question_count = mysqli_query($conn, $sql_question_count);
$question_count = mysqli_fetch_assoc($result_question_count)['question_count'];

$sql_quest = "SELECT date_verified FROM users WHERE id = '$user_id'";
$result_quest = mysqli_query($conn, $sql_quest);
$datever = mysqli_fetch_assoc($result_quest);

// Access the date_verified value
$date_verified_string = $datever['date_verified']; 


// ... rest of your code
// Query to count the number of answers provided by the user
$sql_answer_count = "SELECT COUNT(*) AS answer_count FROM comments WHERE user_id = '$user_id'";
$result_answer_count = mysqli_query($conn, $sql_answer_count);
$answer_count = mysqli_fetch_assoc($result_answer_count)['answer_count'];

// Query to get the user's star balance
$sql_star_balance = "SELECT stars FROM user_stars WHERE user_id = '$user_id'";
$result_star_balance = mysqli_query($conn, $sql_star_balance);
$star_balance = mysqli_num_rows($result_star_balance) > 0 ? mysqli_fetch_assoc($result_star_balance)['stars'] : 0;

// Query to get the user's stars received
$sql_star_received = "SELECT stars_received FROM user_stars WHERE user_id = '$user_id'";
$result_star_received = mysqli_query($conn, $sql_star_received);
$star_received = mysqli_num_rows($result_star_received) > 0 ? mysqli_fetch_assoc($result_star_received)['stars_received'] : 0;

// Query to get the user's income
$sql_income = "SELECT income FROM user_stars WHERE user_id = '$user_id'";
$result_income = mysqli_query($conn, $sql_income);
$income = mysqli_num_rows($result_income) > 0 ? mysqli_fetch_assoc($result_income)['income'] : 0;  // Default to 0 income if no record

// Query to get the user's current subscription
$sql_subscription = "SELECT plan, expiry_date FROM subscriptions WHERE user_id = '$user_id' AND expiry_date > NOW() LIMIT 1";
$result_subscription = mysqli_query($conn, $sql_subscription);
$current_subscription = mysqli_fetch_assoc($result_subscription);
$subscription_status = $current_subscription ? $current_subscription['plan'] . ' (expires on ' . $current_subscription['expiry_date'] . ')' : 'No active subscription';

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];

    // Ensure the file was uploaded without errors
    if ($file['error'] === 0) {
        // Generate a random file name using uniqid() to ensure uniqueness
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION); // Get the file extension
        $randomFileName = uniqid($user_id . '_', true) . '.' . $fileExtension; // Generate unique name
        $fileDestination = 'user_image/' . $randomFileName;

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($file['tmp_name'], $fileDestination)) {
            // Update the profile_picture column in the database with the random file name
            $sql_update_picture = "UPDATE users SET profile_picture = '$randomFileName' WHERE id = '$user_id'";
            mysqli_query($conn, $sql_update_picture);
            header("Location: account_settings.php");
            exit();
        } else {
            $error = "Failed to upload profile picture.";
        }
    } else {
        $error = "There was an error uploading your file.";
    }
}
// Query to get the user's badge level
$sql_badge = "SELECT badge FROM user_stars WHERE user_id = '$user_id'";
$result_badge = mysqli_query($conn, $sql_badge);

if (!$result_badge) {
    die("Error retrieving badge: " . mysqli_error($conn)); // Debugging line to check for query errors
}

if (mysqli_num_rows($result_badge) > 0) {
    $badge = mysqli_fetch_assoc($result_badge)['badge'];
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


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMatch</title>
    <link rel="stylesheet" href="styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script>
        // Function to show the withdrawal pop-up
        function showWithdrawPopup() {
            document.getElementById('withdrawPopup').style.display = 'block';
        }

        // Function to hide the withdrawal pop-up
        function hideWithdrawPopup() {
            document.getElementById('withdrawPopup').style.display = 'none';
        }

        // Function to ensure the user cannot withdraw more than available income
        function validateWithdrawal(maxAmount) {
            const amount = document.getElementById('withdrawAmount').value;
            if (amount > maxAmount) {
                alert('You cannot withdraw more than your available income.');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-container">
        <img class="ree" src="image/logo.jpg" style="border-radius:50%; max-width: 70px; margin-right: 10px;">
        <a href="index.php" class="brand"><img src="image/logo.jpg" class="non" style="border-radius:50%; max-width: 35px; margin-right: 10px;">AgriMatch</a>

        <!-- Hamburger Menu Button (Mobile View) -->
        <button class="navbar-toggle" onclick="toggleMobileMenu()">☰</button>


        <!-- Regular Links for Desktop -->
         <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
                <li><a href="logout.php">Logout</a></li>
        </ul>

        <!-- Mobile Links for Mobile Navigation (with Search Form) -->
        <ul class="nav-links-mobile" id="mobileMenu">
            <li><a href="index.php">Home</a></li>
                <li><a href="logout.php">Logout</a></li>
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

    <!-- User Account Settings Body -->
    <div style="display:flex; align-items: center;justify-content: center;margin-left: auto;margin-right: auto;margin-top: 50px;margin-bottom: 100px;">
    <div class="account-container" style="text-align: center;">
        <h2>User Account Settings</h2>

        <?php if (isset($error)): ?>
    <div id="message" class="error" style="color: red"><?php echo $error; ?></div>
<?php elseif (isset($success)): ?>
    <div id="message" class="success" style="color: green"><?php echo $success; ?></div>
<?php elseif (isset($_SESSION['withdraw_error'])): ?>
    <div id="message" class="error" style="color: red"><?php echo $_SESSION['withdraw_error']; unset($_SESSION['withdraw_error']); ?></div>
<?php elseif (isset($_SESSION['withdraw_success'])): ?>
    <div id="message" class="success" style="color: green"><?php echo $_SESSION['withdraw_success']; unset($_SESSION['withdraw_success']); ?></div>
<?php endif; ?>

<!-- JavaScript to Hide Messages After 5 Seconds -->
<script>
    window.onload = function() {
        var message = document.getElementById('message');
        if (message) {
            setTimeout(function() {
                message.style.display = 'none';
            }, 5000); // 5000 milliseconds = 5 seconds
        }
    };
</script>


        <div style="position: relative; display: inline-block;">
    <!-- Profile Picture -->
    <img src="<?php echo htmlspecialchars($profilePicSrc); ?>" alt="Profile Picture" class="profile-pic" 
         style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover; position: relative; z-index: 1;"/>
    
    <!-- Display badge image if badge level is set -->
    <?php if ($badgeImageSrc): ?>
        <img src="<?php echo htmlspecialchars($badgeImageSrc); ?>" alt="Badge" class="badge-image" 
             style="width: 50px; height: 50px; position: absolute; top: 0; left: 0; transform: translate(60px, 60px); z-index: 2;">
    <?php endif; ?>
</div>

        <p>
    <strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?>
    <?php if ($is_verified == "verified"): ?>
        <img src="image/verified.png" style="width:25px; display: inline; vertical-align: middle; margin-bottom: 3px;padding-left: 0;">
    <?php endif; ?>
</p>

        <p><strong>Questions Asked:</strong> <?php echo $question_count; ?></p>
        <p><strong>Answers Given:</strong> <?php echo $answer_count; ?></p>
        <p><strong>Stars Received:</strong> <?php echo $star_received; ?></p>
        <p><strong>Subscription Status:</strong> <?php echo $subscription_status; ?></p>
        <?php if ($is_verified == "verified"): ?>
        <p><strong>Date Verified:</strong> <?php echo $date_verified_string; ?></p>
        <p style="margin-top:30px;margin-bottom: 30px;"><strong>Available Income:</strong> ₱<?php echo $income; ?> <button onclick="showWithdrawPopup()">Withdraw</button></p>
        <?php endif; ?>
<?php if ($user_id && $is_verified == "verified"): ?>
        <form method="POST" action="" style="margin-bottom: 15px;">
            <label for="topic">Select a topic you're good with:</label>
            <select name="topic" id="topic" required>
                <option value="" disabled selected>Choose a topic</option>
                <option value="Animal Science" <?= $selected_topic == "Animal Science" ? "selected" : "" ?>>Animal Science</option>
                <option value="Crop Science" <?= $selected_topic == "Crop Science" ? "selected" : "" ?>>Crop Science</option>
                <option value="Crop Protection" <?= $selected_topic == "Crop Protection" ? "selected" : "" ?>>Crop Protection</option>
                <option value="Soils" <?= $selected_topic == "Soils" ? "selected" : "" ?>>Soils</option>
                <option value="Agricultural Management" <?= $selected_topic == "Agricultural Management" ? "selected" : "" ?>>Agricultural Management</option>
                <option value="Agricultural Extension" <?= $selected_topic == "Agricultural Extension" ? "selected" : "" ?>>Agricultural Extension</option>
            </select>
            <button type="submit">Submit</button>
        </form>
    <?php elseif ($user_id): ?>

    <?php else: ?>
        <p>Please log in to select a topic.</p>
    <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" style="min-width:100%">
            <label for="topic">Edit your Profile Picture:</label>
    <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" required>
    <div id="imagePreviewContainer" style="margin-top: 10px;margin-bottom: 10px;">
    <img id="imagePreview" src="" alt="Profile Picture Preview" style="max-width: 100%; height: auto; display: none;">
    </div>
    <button type="submit" id="uploadButton">Edit Profile Picture</button>

</form>

<!-- Display the selected image here -->


<script>
    // Event listener to show the preview of the selected image
    document.getElementById('profilePictureInput').addEventListener('change', function(event) {
        const file = event.target.files[0]; // Get the selected file
        const imagePreview = document.getElementById('imagePreview');
        
        // Check if a file is selected and it's an image
        if (file && file.type.startsWith('image')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Set the src of the image preview to the loaded image data
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block'; // Display the image
            };
            reader.readAsDataURL(file); // Read the selected file as data URL
        } else {
            imagePreview.style.display = 'none'; // Hide the image preview if it's not an image
        }
    });
</script>
<div style="margin-top:30px;margin-bottom: 30px;">
    <h1>Your Posts</h1>
    <hr>

    <?php if ($result2->num_rows === 0): ?>
        <h3 style="color: grey">(There are no posts at the moment.)</h3>
        <?php
     
        echo "
        <a href='post.php' class='view-link'>Create A Post</a>";
        
        ?>
    <?php else: ?>
        <div class="posts-container"> 
            <?php while ($row2 = $result2->fetch_assoc()): ?>
                <div class="post-box"> 
                    <div class="post-content">
                        <!-- User Profile Picture and Username -->
                        <div class="post-user">
                            <img src="user_image/<?php echo htmlspecialchars($row2['profile_picture']); ?>" 
     alt="<?php echo htmlspecialchars($row2['username']); ?>'s profile picture" 
     class="profile-picture">
<span class="username"><?php echo htmlspecialchars($row2['username']); ?>  <?php if ($is_verified == "verified"): ?>
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

                    <form action="account_settings.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this post?');">
                        <button type="submit" name="delete_post" value="<?php echo $row2['id']; ?>" style="color: red; background: none; border: none;">Delete Post</button>
                    </form>


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

    </div>

</div>

<!-- Withdrawal Pop-up Form -->
<div id="withdrawPopup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background-color: white; padding: 20px; border: 1px solid #ccc; border-radius: 10px;">
    <h3>Withdraw Income</h3>
    <form method="POST" action="process_withdrawal.php" onsubmit="return validateWithdrawal(<?php echo $income; ?>)">
        <p><strong>Available Income:</strong> ₱<?php echo $income; ?></p>
        <label for="cardNumber">Credit/Debit Card Number:</label><br>
        <input type="text" id="cardNumber" name="card_number" title="4321 1234 5678 8765" placeholder="4321 1234 5678 8765" pattern="^(\d{4} \d{4} \d{4} \d{4})$" required><br><br>
        
        <label for="withdrawAmount">Amount to Withdraw (₱):</label><br>
        <input type="number" id="withdrawAmount" name="withdraw_amount" min="0.50" value="0" step="0.01" required><br><br>
        
        <button type="submit" name="withdraw">Withdraw</button>
        <button type="button" onclick="hideWithdrawPopup()">Cancel</button>
    </form>
</div>

<?php
        include 'footer.php';
?>
</body>
</html>
