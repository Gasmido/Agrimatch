<?php
session_start();  

session_unset();
session_destroy();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <link rel="stylesheet" href="styles.css">
    <script>
   
        window.onload = function() {
            alert("Logged out successfully!");
      
            window.location.href = "login.php";
        };
    </script>
</head>
<body>
    <p>You are being logged out...</p>
</body>
</html>