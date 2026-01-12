<?php
require 'config.php';

// Check if the required parameters are set
if (!isset($_GET['conversation_id'], $_GET['user_id'], $_GET['other_user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}

$conversation_id = intval($_GET['conversation_id']);
$user_id = intval($_GET['user_id']);
$other_user_id = intval($_GET['other_user_id']);

// Check if the user has an active subscription
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND expiry_date > NOW()");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$subscriptionResult = $stmt->get_result();
$stmt->close();

if ($subscriptionResult->num_rows === 0) {
    // User does not have an active subscription, send error response
    echo json_encode(['status' => 'error', 'message' => 'You must have an active subscription to view messages.']);
    exit();
}

// Fetch all messages for the conversation
$stmt = $conn->prepare("SELECT pm.*, u1.username AS sender_name, u1.profile_picture AS sender_picture, 
                         u2.username AS receiver_name, u2.profile_picture AS receiver_picture
                         FROM private_messages pm
                         LEFT JOIN users u1 ON pm.sender_id = u1.id
                         LEFT JOIN users u2 ON pm.receiver_id = u2.id
                         WHERE pm.conversation_id = ? AND 
                         ((pm.sender_id = ? AND pm.receiver_id = ?) OR 
                          (pm.sender_id = ? AND pm.receiver_id = ?))
                         ORDER BY pm.sent_at ASC");
$stmt->bind_param("iiiii", $conversation_id, $user_id, $other_user_id, $other_user_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result();

$messageList = [];
while ($row = $messages->fetch_assoc()) {
    $row['sent_at'] = date('Y-m-d H:i', strtotime($row['sent_at']));
    $messageList[] = $row;
}

// Return the messages as a JSON response
echo json_encode(['status' => 'success', 'messages' => $messageList]);
exit();
?>
