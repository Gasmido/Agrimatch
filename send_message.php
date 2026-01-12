<?php
require 'config.php';
session_start();
if (isset($_POST['subb'])) {
    $currentUserId = $_SESSION['user_id'];
$receiverId = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$message = $_POST['message'];
$conversation_id = $_POST['conversation_id'];

// Insert message into the private_messages table
$sql = "INSERT INTO private_messages (conversation_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiis", $conversation_id, $currentUserId, $receiverId, $message);
$stmt->execute();

// Redirect back to the conversation
header("Location: message.php?question_owner_id=$receiverId&user_id=$uid&asdf"); 
exit();
} elseif (isset($_POST['mos'])) {
    $conversation_id = $_POST['conversation_id'];
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];
    $qoid = $_POST['question_owner_id'];
    $uid = $_POST['user_id'];

    // Create a new conversation
    if ($conversation_id == 1) {
    $stmt = $conn->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $uid, $receiver_id);
    $stmt->execute();
    $conversation_id = $stmt->insert_id;
    $stmt->close();
}
    // Insert the message into the database
    $stmt = $conn->prepare("INSERT INTO private_messages (conversation_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $conversation_id, $sender_id, $receiver_id, $message);
    $stmt->execute();

    header("Location: message.php?question_owner_id=$uid&user_id=$receiver_id"); 
    exit();
}
?>
