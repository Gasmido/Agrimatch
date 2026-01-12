<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
// Check if the user has an active subscription
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND expiry_date > NOW()");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$subscriptionResult = $stmt->get_result();
$stmt->close();



// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = isset($_POST['caption']) ? trim($_POST['caption']) : null;
    $user_id = $_SESSION['user_id'];
    $media_type = 'none';
    $media_name = null;

    // Handle file uploads
    if (!empty($_FILES['media']['name'])) {
        $file = $_FILES['media'];
        $upload_dir = '';

        // Validate file type
        $file_type = mime_content_type($file['tmp_name']);
        if (str_starts_with($file_type, 'image')) {
            $media_type = 'photo';
            $upload_dir = 'uploads/photos/';
        } elseif (str_starts_with($file_type, 'video')) {
            $media_type = 'video';
            $upload_dir = 'uploads/videos/';
        } else {
            echo "<script>alert('Invalid file type. Only images and videos are allowed.')</script>";
            exit();
        }

        // Save file
        $media_name = time() . "_" . basename($file['name']);
        $target_path = $upload_dir . $media_name;
        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            echo "<p style='color: red;'>Error uploading file.</p>";
            exit();
        }
    }

    // Insert post into database
    $stmt = $conn->prepare("INSERT INTO posts (user_id, caption, media_type, media_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $caption, $media_type, $media_name);
    $stmt->execute();
    $stmt->close();
    header("Location: account_settings.php");
    exit;
}

// Fetch posts
$result = $conn->query("SELECT * FROM posts ORDER BY date_posted DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<script type="text/javascript">
    if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}
</script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Page</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center; /* Center horizontally */
            min-height: 100vh;  /* Ensure body takes full viewport height */
            margin: 0;          /* Remove default body margins */
            font-family: sans-serif; 
        }

        .container {
            max-width: 800px; 
            width: 100%;
            padding: 20px;
            background-color: #fff; 
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
        }

        .navbar {
            width: 100%;  
            padding: 10px 20px; 
            margin-bottom: 20px; 
            border-bottom: 1px solid #ddd;
        }

        .navbar-container {
            display: flex;
            justify-content: space-between; 
            align-items: center;
        }

     

     

       

    </style>
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
    <h1>Create a Post</h1>
    <?php
if ($subscriptionResult->num_rows === 0) {
    echo "<p style='text-align: center;'>You must have an active subscription to create a post.</p>";
    include 'footer.php';
    exit();
}
?>
   <form action="" method="POST" enctype="multipart/form-data">
    <textarea name="caption" placeholder="Write something..." rows="4" cols="50"></textarea><br>
    <input type="file" name="media" id="mediaInput" accept="image/*,video/*" onchange="previewMedia(event)"><br>
    <div id="mediaPreview" style="margin-top: 15px;"></div>
    <button type="submit">Post</button>
</form>

<script>
    function previewMedia(event) {
        const mediaInput = event.target;
        const mediaPreview = document.getElementById('mediaPreview');
        mediaPreview.innerHTML = ''; // Clear any previous previews

        const file = mediaInput.files[0];
        if (!file) return; // No file selected

        const fileType = file.type;
        const fileURL = URL.createObjectURL(file);

        if (fileType.startsWith('image')) {
            // Preview image
            const img = document.createElement('img');
            img.src = fileURL;
            img.alt = "Selected Image";
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            mediaPreview.appendChild(img);
        } else if (fileType.startsWith('video')) {
            // Preview video
            const video = document.createElement('video');
            video.src = fileURL;
            video.controls = true;
            video.style.maxWidth = '100%';
            video.style.height = 'auto';
            mediaPreview.appendChild(video);
        } else {
            // Handle unsupported file types (optional)
            mediaPreview.textContent = 'Unsupported file type.';
        }
    }
</script>

    
<?php
include 'footer.php';
?>  
