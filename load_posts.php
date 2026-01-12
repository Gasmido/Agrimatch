<?php
session_start();
include 'config.php'; // Ensure you include your database connection

$query = "
    SELECT posts.*, users.username, users.profile_picture 
    FROM posts 
    INNER JOIN users ON posts.user_id = users.id 
    ORDER BY posts.date_posted DESC
";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    echo '<h3 style="color: grey">(There are no posts at the moment.)</h3>';
} else {
    while ($row = $result->fetch_assoc()) {
        ?>
        <div class="post-box">
            <div class="post-content">
                <!-- User Profile Picture and Username -->
                <div class="post-user">
                    <img src="user_image/<?php echo htmlspecialchars($row['profile_picture']); ?>" 
                         alt="<?php echo htmlspecialchars($row['username']); ?>'s profile picture" 
                         class="profile-picture">
                    <span class="username"><?php echo htmlspecialchars($row['username']); ?></span>
                </div>

                <!-- Post Content -->
                <p style="color:grey"><strong>Posted on:</strong> <?php echo htmlspecialchars($row['date_posted']); ?></p>
                <?php if (!empty($row['caption'])): ?>
                    <p><?php echo htmlspecialchars($row['caption']); ?></p>
                <?php endif; ?>
                <?php if ($row['media_type'] === 'photo'): ?>
                    <img src="uploads/photos/<?php echo htmlspecialchars($row['media_name']); ?>" alt="Photo" style="max-width: 100%; height: auto;">
                <?php elseif ($row['media_type'] === 'video'): ?>
                    <video controls style="max-width: 100%; height: auto;">
                        <source src="uploads/videos/<?php echo htmlspecialchars($row['media_name']); ?>" type="video/mp4">
                    </video>
                <?php endif; ?>

                <!-- Heart Button -->
                <?php
                $stmt = $conn->prepare("SELECT * FROM post_hearts WHERE user_id = ? AND post_id = ?");
                $stmt->bind_param("ii", $_SESSION['user_id'], $row['id']);
                $stmt->execute();
                $hearted = $stmt->get_result()->num_rows > 0;
                ?>
                <div class="heart-container">
                    <button class="heart-button" data-post-id="<?php echo $row['id']; ?>" style="width: 10%;"> 
                        <?php if ($hearted): ?>
                            <i class='bx bxs-heart' style="color: red;"></i> 
                        <?php else: ?>
                            <i class='bx bx-heart' style="color: black;"></i> 
                        <?php endif; ?>
                    </button>
                    <span class="heart-count"><?php echo htmlspecialchars($row['hearts_received']); ?></span>
                </div>
            </div>
        </div>
        <?php
    }
}
?>
