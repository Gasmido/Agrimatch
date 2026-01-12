<?php
require 'config.php';
include 'head.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to post a question.";
    exit();  
}

$rid = $_POST['receivere_id'];
$id = $_POST['user_id'];
$cid = $_POST['conversation_id'];   
if (!isset($_POST['conversation_id'])) {
    header('Location: index.php');
    exit();
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

<!-- Form to Give Stars -->
<div style="display:flex; align-items: center; justify-content: center; margin-top: 50px; margin-bottom: 100px;">
    <div style="display: block; text-align: center;">
        <div class="form-container" style="min-width:100%">
            <h2>Select how many stars you will give</h2>
            
            <form action="give_stars2.php" method="POST">
                <div class="star-rating">
                    <!-- Hidden input to store the star rating -->
                    <input type="hidden" name="stars" id="starRatingInput">
                    <input type="text" name="cid" value="<?= $cid ?>" hidden>
                    <input type="text" name="rid" value="<?= $rid ?>" hidden>
                    <!-- Star icons -->
                    <span class="star" data-value="1" onclick="setStarRating(1)">★</span>
                    <span class="star" data-value="2" onclick="setStarRating(2)">★</span>
                    <span class="star" data-value="3" onclick="setStarRating(3)">★</span>
                    <span class="star" data-value="4" onclick="setStarRating(4)">★</span>
                    <span class="star" data-value="5" onclick="setStarRating(5)">★</span>
                </div>
                <button type="submit" name="submit">Submit</button>
            </form>
        </div>
    </div>
</div>

<style>
    .star-rating .star {
        font-size: 2em;
        color: gray;
        cursor: pointer;
    }
    .star-rating .star.selected {
        color: gold;
    }
</style>
<script type="text/javascript">
    if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

</script>
<script>
function setStarRating(rating) {
    const stars = document.querySelectorAll('.star-rating .star');
    const ratingInput = document.getElementById('starRatingInput');

    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('selected');
        } else {
            star.classList.remove('selected');
        }
    });

    // Set the value in the hidden input field
    ratingInput.value = rating;
}
</script>

<?php
include 'footer.php';
?>
