<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $post_id = intval($_POST['post_id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT * FROM post_hearts WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM post_hearts WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE posts SET hearts_received = hearts_received - 1 WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();

        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        $stmt = $conn->prepare("INSERT INTO post_hearts (user_id, post_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE posts SET hearts_received = hearts_received + 1 WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();

        echo json_encode(['success' => true, 'action' => 'added']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
