<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw'])) {
    $withdraw_amount = $_POST['withdraw_amount'];
    $card_number = $_POST['card_number'];

    // Get the user's available income
    $sql_income = "SELECT income FROM user_stars WHERE user_id = '$user_id'";
    $result_income = mysqli_query($conn, $sql_income);
    $income = mysqli_num_rows($result_income) > 0 ? mysqli_fetch_assoc($result_income)['income'] : 0;

    // Ensure the user cannot withdraw more than available income
    if ($withdraw_amount > $income) {
        $_SESSION['withdraw_error'] = "You cannot withdraw more than your available income.";
        header("Location: account_settings.php");
        exit();
    } else {
        // Subtract the withdrawn amount from the user's income
        $new_income = $income - $withdraw_amount;
        $sql_update_income = "UPDATE user_stars SET income = '$new_income' WHERE user_id = '$user_id'";

        if (mysqli_query($conn, $sql_update_income)) {
            $_SESSION['withdraw_success'] = "Withdrawal successful! Amount withdrawn: ₱" . $withdraw_amount;
            header("Location: account_settings.php");
            exit();
        } else {
            $_SESSION['withdraw_error'] = "Error processing withdrawal: " . mysqli_error($conn);
            header("Location: account_settings.php");
            exit();
        }
    }
}
?>
