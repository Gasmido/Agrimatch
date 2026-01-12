<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if username already exists
    $check_username_query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $check_username_query);

    if (mysqli_num_rows($result) > 0) {
        $error = "Username already exists. Please choose a different username.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // If no errors, hash the password and insert the new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Query to insert a new user
        // Query to insert a new user with the default role "user"
$sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', 'user')";


        if (mysqli_query($conn, $sql)) {
            // Registration successful, redirect to login
            $_SESSION['register_success'] = true;
            header("Location: login.php");
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMatch</title>
    <link rel="stylesheet" href="styles.css">
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
                           <li><a href="login.php">Login</a></li>
                <li><a href="index.php">Home</a></li>
        </ul>

        <!-- Mobile Links for Mobile Navigation (with Search Form) -->
        <ul class="nav-links-mobile" id="mobileMenu">
                          <li><a href="login.php">Login</a></li>
                <li><a href="index.php">Home</a></li>
        </ul>
    </div>
</nav>

<script>
 function toggleMobileMenu() {
    var mobileMenu = document.getElementById("mobileMenu");
    mobileMenu.classList.toggle("show"); // Toggle the mobile menu visibility
}
</script>

    <!-- Form Body -->
    <div style="display:flex; align-items: center;justify-content: center;margin-left: auto;margin-right: auto;">
    <div class="form-container" style="text-align: center;">
        <h1>Register</h1>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" style="align-items: center;margin-left: auto;margin-right: auto;text-align: left;">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Register</button>
        </form>
    </div>
</div>
<?php
        include 'footer.php';
?>
</body>
</html>
