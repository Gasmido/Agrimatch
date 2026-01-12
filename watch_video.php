<?php
include 'head.php';
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to access this page.";
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Reduce question count by 1
    $update_question_count = $conn->prepare("UPDATE users SET question_asked = question_asked - 1, ads_watched = ads_watched + 1 WHERE id = ? AND question_asked > 0");
    $update_question_count->bind_param("i", $user_id);

    if ($update_question_count->execute()) {
        header("Location: post_question.php");
        exit();
    } else {
        echo "Error updating question count or ads watched.";
    }
} else {
?>
<body onload="scrollToVideo()" style="height: 130vh;">
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
                <li><a href="post_question.php">Back</a></li>
            </ul>

            <!-- Mobile Links for Mobile Navigation (with Search Form) -->
            <ul class="nav-links-mobile" id="mobileMenu">
                <li><a href="post_question.php">Back</a></li>
                
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

    function scrollToVideo() {
        // Automatically scroll to the video element
        const video = document.getElementById("asdf");
        video.scrollIntoView({ behavior: "smooth" });
    }
    function scrollToVideos() {
        // Automatically scroll to the video element
        const videos = document.getElementById("videoForm");
        videos.scrollIntoView({ behavior: "smooth" });
    }
    </script>

    <h2 id="asdf" style="text-align: center;">Watch this 30-second video to ask another question.</h2>
    <video id="promoVideo" style="width:70%; margin-right:auto;margin-left: auto;" autoplay>
        <source src="ads/adss.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <form id="videoForm" method="POST" action="" style="text-align: center; margin-left:auto;margin-right: auto;">
        <button type="submit" style="display: none;">Return to Ask a Question</button>
    </form>
    <script>
        const video = document.getElementById('promoVideo');
        const form = document.getElementById('videoForm');

        // Show the button only after the video ends
        video.addEventListener('ended', () => {
            form.querySelector('button').style.display = 'block';
            scrollToVideos();
        });
    </script>

<?php
include "footer.php";
}
?>
