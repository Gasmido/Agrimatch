<?php
session_start(); 

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query to find the user with the provided username
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
    // Password is correct, create session
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['role'] = $row['role'];
    $_SESSION['type'] = $row['types'];

    // Redirect based on role
    if ($row['role'] == 'admin') {
        header("Location: admin_home.php");
    } else {
        header("Location: index.php");
    }
    exit();
}
 else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that username.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMatch</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<script>
    window.onload = function() {
        <?php if (isset($_SESSION['register_success'])): ?>
            alert("Registration successful!");
            <?php unset($_SESSION['register_success']); ?>
        <?php endif; ?>
    };
</script>
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
           <li><a href="register.php">Register</a></li>
                <li><a href="index.php">Home</a></li>
        </ul>

        <!-- Mobile Links for Mobile Navigation (with Search Form) -->
        <ul class="nav-links-mobile" id="mobileMenu">
           <li><a href="register.php">Register</a></li>
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
        <h1>Login</h1>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" style="align-items: center;margin-left: auto;margin-right: auto;text-align: left;">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Login</button>
        </form>
    </div>
</div>
<?php
        include 'footer.php';
?>