<?php
// submit_comment.php
session_start();
include 'config.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $comment = $_POST['comment'];
    $questionId = $_POST['question_id']; // Pass the question ID from the form
    $userId = $_SESSION['user_id'];

    if (!empty($comment) && !empty($questionId)) {
        // Insert the comment into the database
        $stmt = $conn->prepare("INSERT INTO comments (question_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $questionId, $userId, $comment);
        if ($stmt->execute()) {
            $_SESSION['tuko'] = "Your answer was submitted successfully!";
        } else {
            $_SESSION['tuko'] = "Error: Could not submit your answer.";
        }
        $stmt->close();
    } else {
        $_SESSION['tuko'] = "Error: Your comment cannot be empty.";
    }

    // Redirect back to the question page
    header("Location: view_question.php?id=" . $questionId);
    exit();
} else {
    // If the user is not logged in or method is not POST, redirect to login page
    header("Location: login.php");
    exit();
}
