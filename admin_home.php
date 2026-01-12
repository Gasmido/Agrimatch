<?php
session_start();

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require 'config.php'; // Include database connection

// Update verification status if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_verification'])) {
    $user_id = $_POST['user_id'];
    $new_status = $_POST['verification_status'];
    $current_date = date('Y-m-d H:i:s'); // Get the current date and time

    // If status is 'not_verified', set date_verified to NULL
    if ($new_status == 'not_verified') {
        $sql = "UPDATE users SET verified = ?, date_verified = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $user_id);
    } else {
        // If status is 'verified', set date_verified to current date
        $sql = "UPDATE users SET verified = ?, date_verified = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $new_status, $current_date, $user_id);
    }
    
    if ($stmt->execute()) {
        $message = "User verification updated successfully.";
    } else {
        $message = "Error updating user verification.";
    }
}



try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if 24 hours have passed since the last update
    $stmt = $pdo->prepare("
        SELECT current_value, TIMESTAMPDIFF(HOUR, last_updated, NOW()) AS hours_since_update 
        FROM cryptocurrencies 
        WHERE symbol = :symbol
    ");
    $stmt->execute(['symbol' => 'STAR']);
    $crypto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($crypto) {
        $hoursSinceUpdate = $crypto['hours_since_update'];

        if ($hoursSinceUpdate >= 24) {
            // Generate a random percentage change between -10% and +10%
            $percentageChange = mt_rand(-1000, 1000) / 10000; // Random value between -0.10 and 0.10
            $newValue = $crypto['current_value'] * (1 + $percentageChange);

            // Update the cryptocurrency value
            $updateStmt = $pdo->prepare("
                UPDATE cryptocurrencies 
                SET current_value = :new_value, 
                    change_percentage = :change_percentage, 
                    last_updated = NOW()
                WHERE symbol = :symbol
            ");
            $updateStmt->execute([
                'new_value' => round($newValue, 4), // Round to 4 decimal places
                'change_percentage' => round($percentageChange * 100, 2), // Convert to percentage
                'symbol' => 'STAR'
            ]);

           
        } else {
            
        }
    } else {

    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


// Fetch all users, their subscription status, stars_received, badges, and date_verified
$sql = "SELECT u.id, u.username, u.created_at, u.profile_picture, u.role, u.verified, u.date_verified,
       EXISTS (SELECT 1 FROM subscriptions WHERE subscriptions.user_id = u.id) AS is_subscribed,
       COALESCE(us.stars_received, 0) AS stars_received,
       CASE us.badge
           WHEN 1 THEN 'Gold'
           WHEN 2 THEN 'Silver'
           WHEN 3 THEN 'Bronze'
           ELSE 'None'
       END AS badge
       FROM users u
       LEFT JOIN user_stars us ON u.id = us.user_id
       WHERE u.role = 'user'";


$result = $conn->query($sql);
?>
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .admin-container table {
            width: 100%;
            border-collapse: collapse;
            font-family: sans-serif;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 40px;
        }
        .admin-container table {
    overflow-x: auto; /* Enable horizontal scrolling */
    display: block; /* Make the table a block element */
}

        .admin-container th, .admin-container td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .admin-container th {
            background-color: #f0f0f5;
            color: #333;
        }

        .admin-container tr:hover {
            background-color: #f5f5f5;
        }

        .not-subscribed {
            color: red;
            font-weight: bold;
        }

        .disabled-button {
            background-color: #ddd;
            cursor: not-allowed;
        }
        /* Styles for smaller screens (e.g., mobile phones) */
@media (max-width: 768px) {
    .admin-container table {
        font-size: 14px; /* Reduce font size */
    }

    .admin-container th, .admin-container td {
        padding: 10px; /* Reduce padding */
    }

    /* Hide some columns if necessary */
    .admin-container th:nth-child(4), 
    .admin-container td:nth-child(4) { 
        display: none; 
    } 
}
    </style>
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
                <li><a href="logout.php">Logout</a></li>
        </ul>

        <!-- Mobile Links for Mobile Navigation (with Search Form) -->
        <ul class="nav-links-mobile" id="mobileMenu">
                <li><a href="logout.php">Logout</a></li>
            <!-- Mobile Search Form -->
        </ul>
    </div>
</nav>
<script>
 function toggleMobileMenu() {
    var mobileMenu = document.getElementById("mobileMenu");
    mobileMenu.classList.toggle("show"); // Toggle the mobile menu visibility
}
</script>
<div class="admin-container">
    <div style="text-align:center;">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    
    
    <?php if (isset($message)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php
try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the current value of the cryptocurrency
    $stmt = $pdo->prepare("SELECT name, symbol, current_value, change_percentage, last_updated FROM cryptocurrencies WHERE symbol = :symbol");
    $stmt->execute(['symbol' => 'STAR']);
    $crypto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($crypto) {
        // Display the cryptocurrency value
        echo "<h2>AgriMatch Star: </h2>";
        echo "<p>Current Value: PHP " . number_format($crypto['current_value'], 4) . "</p>";
        echo "<p>Change Percentage: " . number_format($crypto['change_percentage'], 2) . "%</p>";
    } else {
        echo "<p>Cryptocurrency not found.</p>";
    }
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
</div>
<h2>Manage User Verification</h2>
    <table border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Profile Picture</th>
            <th>Username</th>
            <th>Created At</th>
            <th>Stars Received</th>
            <th>Badge</th>
            <th>Verification Status</th>
            <th>Date Verified</th>
            <th>Subscription Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td>
                    <?php if (!empty($row['profile_picture'])): ?>
                         <a href='message.php?question_owner_id=<?= $_SESSION['user_id'] ?>&user_id=<?= htmlspecialchars($row['id']) ?>'>
                        <img src="user_image/<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile Picture" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                         </a>
                    <?php else: ?>
                        <a href='message.php?question_owner_id=<?= $_SESSION['user_id'] ?>&user_id=<?= htmlspecialchars($row['id']) ?>'>
                        <img src="user_image/default.jpg" alt="Default Profile Picture" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                        </a>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                <td><?php echo htmlspecialchars($row['stars_received']); ?></td>
                <td><?php echo htmlspecialchars($row['badge']); ?></td>
                <td><?php echo htmlspecialchars($row['verified']); ?></td>
                <td>
                    <?php 
                    // Display the date_verified or 'N/A' if it's NULL
                    echo $row['date_verified'] ? htmlspecialchars($row['date_verified']) : 'N/A'; 
                    ?>
                </td>
                <td>
                    <?php 
                    // Display subscription status
                    if ($row['is_subscribed'] == 1) {
                        echo 'Subscribed';
                    } else {
                        echo 'Not Subscribed';
                    }
                    ?>
                </td>
                <td>
                    <?php if ($row['is_subscribed']): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <select name="verification_status" required>
                                <option value="verified" <?php if ($row['verified'] == 'verified') echo 'selected disabled'; ?>>Verified</option>
                                <option value="not_verified" <?php if ($row['verified'] == 'not_verified') echo 'selected'; ?>>Not Verified</option>
                            </select>
                            <button type="submit" name="update_verification">Update</button>
                        </form>
                    <?php else: ?>
                        <span class="not-subscribed">User not subscribed</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</div>

<?php include 'footer.php'; ?>
</body>
</html>
