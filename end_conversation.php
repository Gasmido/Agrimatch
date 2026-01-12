<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conversation_id = $_POST['conversation_id'];

    // Mark the conversation as ended
    $stmt = $conn->prepare("INSERT INTO conversation_end (conversation_id) VALUES (?)");
    $stmt->bind_param("i", $conversation_id);
    $stmt->execute();

    echo "Conversation has been ended.";
    header("Location: index.php");
    exit();
}
?>
