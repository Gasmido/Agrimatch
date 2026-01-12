<?php
include "config.php";
session_start();

if (isset($_POST['submit'])) {
    $stars_to_give = $_POST['stars'];
    $user_id = $_POST['rid'];
    $conversation_id = $_POST['cid'];
    $giver_id = $_SESSION['user_id'];

    // Fetch user verification status and date
    $verification_check_sql = "SELECT verified, date_verified FROM users WHERE id = ?";
    $stmt = $conn->prepare($verification_check_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $verification_result = $stmt->get_result();
    $user_verification = $verification_result->fetch_assoc();

    if (!$user_verification) {
        $_SESSION['tuko'] = "User does not exist.";
        header("Location: message.php?question_owner_id=$giver_id&user_id=$user_id");
        exit();
    }

    $verified = $user_verification['verified'];
    $date_verified = $user_verification['date_verified'];
    $is_verified_eligible = false;

    if ($verified === "verified") {
        // Check if the user was verified at least 1 year ago
        $current_date = new DateTime();
        $verified_date = new DateTime($date_verified);
        $interval = $current_date->diff($verified_date);
        if ($interval->y >= 1) {
            $is_verified_eligible = true;
        }
    }

    // Check if the recipient user_id already exists in the user_stars table
    $check_recipient_sql = "SELECT stars_received FROM user_stars WHERE user_id = ?";
    $stmt = $conn->prepare($check_recipient_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $recipient_result = $stmt->get_result();

    if ($recipient_result->num_rows > 0) {
        // If user_id exists, update the stars for the recipient
        $update_sql = "UPDATE user_stars SET stars_received = stars_received + ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ii", $stars_to_give, $user_id);
    } else {
        // If user_id does not exist, insert a new record for the recipient
        $update_sql = "INSERT INTO user_stars (user_id, stars_received) VALUES (?, ?)";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ii", $user_id, $stars_to_give);
    }

    if ($stmt->execute()) {
        echo "Stars updated or added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();

    // Always update badge based on stars_received
    $check_badge_eligibility_sql = "SELECT stars_received FROM user_stars WHERE user_id = ?";
    $stmt = $conn->prepare($check_badge_eligibility_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $badge_result = $stmt->get_result();
    $recipient_row = $badge_result->fetch_assoc();

    if ($recipient_row['stars_received'] >= 30 && $recipient_row['stars_received'] < 50) {
        $update_badge_sql = "UPDATE user_stars SET badge = 3 WHERE user_id = ?";
        $stmt = $conn->prepare($update_badge_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    } elseif ($recipient_row['stars_received'] >= 51 && $recipient_row['stars_received'] < 100) {
        $update_badge_sql = "UPDATE user_stars SET badge = 2 WHERE user_id = ?";
        $stmt = $conn->prepare($update_badge_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    } elseif ($recipient_row['stars_received'] >= 100) {
        $update_badge_sql = "UPDATE user_stars SET badge = 1 WHERE user_id = ?";
        $stmt = $conn->prepare($update_badge_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    // Update income only if the user is eligible
if ($is_verified_eligible) {
    // Fetch the current value of the STAR crypto
    $crypto_value_sql = "SELECT current_value FROM cryptocurrencies WHERE symbol = 'STAR'";
    $stmt = $conn->prepare($crypto_value_sql);
    $stmt->execute();
    $crypto_result = $stmt->get_result();
    $crypto_row = $crypto_result->fetch_assoc();

    if ($crypto_row) {
        $current_star_value = $crypto_row['current_value'];

        // Calculate income based on STAR value
        if ($recipient_row['stars_received'] >= 30 && $recipient_row['stars_received'] < 50) {
            $income_to_add = (0.10 * $current_star_value) * $stars_to_give;
        } elseif ($recipient_row['stars_received'] >= 51 && $recipient_row['stars_received'] < 100) {
            $income_to_add = (0.20 * $current_star_value) * $stars_to_give;
        } elseif ($recipient_row['stars_received'] >= 100) {
            $income_to_add = (0.30 * $current_star_value) * $stars_to_give;
        } else {
            $income_to_add = 0;
        }

        if ($income_to_add > 0) {
            $update_income_sql = "UPDATE user_stars SET income = income + ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_income_sql);
            $stmt->bind_param("di", $income_to_add, $user_id);
            $stmt->execute();
            $_SESSION['tuko'] = "Stars given successfully, income and badge updated!";
        }
    } else {
        $_SESSION['tuko'] = "Error: Unable to fetch STAR cryptocurrency value.";
    }
} else {
    $_SESSION['tuko'] = "Stars given successfully, badge updated!";
}


    // Mark the conversation as ended
    $stmt = $conn->prepare("INSERT INTO conversation_end (conversation_id) VALUES (?)");
    $stmt->bind_param("i", $conversation_id);
    $stmt->execute();

    header("Location: message.php?question_owner_id=$giver_id&user_id=$user_id");
    exit();
}

?>
