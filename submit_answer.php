<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $question_id = $_POST['question_id'];
    $answer = $_POST['answer'];

    $sql = "INSERT INTO answers (question_id, user_id, answer) VALUES ('$question_id', '$user_id', '$answer')";

    if (mysqli_query($conn, $sql)) {
        header("Location: view_question.php?id=$question_id");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
