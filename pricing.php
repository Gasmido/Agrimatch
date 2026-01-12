<?php
include 'head.php';
include 'config.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user's current subscription, if any
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND expiry_date > NOW() LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$currentSubscription = $result->fetch_assoc();
$stmt->close();

// Query to get the user's star balance
$sql_star_balance = "SELECT stars FROM user_stars WHERE user_id = '$userId'";
$result_star_balance = mysqli_query($conn, $sql_star_balance);
$star_balance = mysqli_num_rows($result_star_balance) > 0 ? mysqli_fetch_assoc($result_star_balance)['stars'] : 0;  // Default to 0 stars if no record
?>
<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-container">
        <img class="ree" src="image/logo.jpg" style="border-radius:50%; max-width: 70px; margin-right: 10px;">
        <a href="index.php" class="brand"><img src="image/logo.jpg" class="non" style="border-radius:50%; max-width: 35px; margin-right: 10px;">AgriMatch</a>

        <!-- Hamburger Menu Button (Mobile View) -->
        <button class="navbar-toggle" onclick="toggleMobileMenu()">☰</button>

        <!-- Regular Links for Desktop -->
         <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
        </ul>

        <!-- Mobile Links for Mobile Navigation (with Search Form) -->
        <ul class="nav-links-mobile" id="mobileMenu">
            <li><a href="index.php">Home</a></li>
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

<div class="pricing-container" style="display: flex; justify-content: center; align-items: center; flex-wrap: wrap; padding: 50px;">

    <?php 
    // Subscription options
   $plans = [
    ['title' => 'Basic', 'price' => 169, 'benefits' => ['Access to purchase stars', 'Question Posting'], 'button_id' => 'basic'],
    ['title' => 'Premium', 'price' => 1416, 'benefits' => ['Access to purchase stars', 'Question Posting', 'Cheaper overall option'], 'button_id' => 'premium']
];


    // Star options
    $star_plans = [
        ['title' => '50 Stars', 'price' => 49, 'stars' => 50, 'button_id' => '50stars-btn'],
        ['title' => '110 Stars', 'price' => 99, 'stars' => 110, 'button_id' => '110stars-btn'],
        ['title' => '220 Stars', 'price' => 199, 'stars' => 220, 'button_id' => '220stars-btn'],
        ['title' => '500 Stars', 'price' => 399, 'stars' => 500, 'button_id' => '500stars-btn'],
        ['title' => '999 Stars', 'price' => 599, 'stars' => 999, 'button_id' => '999stars-btn']
    ];

    // If the user has an active subscription, display a message
    if ($currentSubscription) {
        echo "<p>You already have an active subscription: <strong>{$currentSubscription['plan']}</strong> until <strong>{$currentSubscription['expiry_date']}</strong>.</p>";
    } else {
        // Loop through and display the subscription plans
        foreach ($plans as $plan): ?>
        <div class="pricing-box" style="border: 1px solid #ccc; padding: 20px; width: 300px; margin: 20px; text-align: center; border-radius: 10px;">
            <h2><?php echo $plan['title']; ?></h2>
            <h3>₱<?php echo $plan['price']; if ($plan['title'] == "Basic") { echo "/month"; } else { echo "/year"; } ?></h3>
            <ul>
                <?php foreach ($plan['benefits'] as $benefit): ?>
                    <li><?php echo $benefit; ?></li>
                <?php endforeach; ?>
            </ul>
            <input type="text" name="sprice" id="sprice" value="<?= $plan['price']; ?>" hidden>
            <button id="<?php echo $plan['button_id']; ?>" class="buy-btn" style="background-color: #007BFF; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;" onclick="showPaymentModal('<?php echo $plan['button_id']; ?>', 'subscription', '<?php echo $plan['price']; ?>')">Buy</button>
        </div>
        <?php endforeach; ?>
    <?php 
    }
    /*
    // Always display star purchase options regardless of subscription
    foreach ($star_plans as $star_plan): ?>
        <div class="pricing-box" style="border: 1px solid #ccc; padding: 20px; width: 300px; margin: 20px; text-align: center; border-radius: 10px;">
            <h2><?php echo $star_plan['title']; ?></h2>
            <h3>₱<?php echo $star_plan['price']; ?></h3>
            <input type="text" name="price" id="price" value="<?= $star_plan['price']; ?>" hidden>
            <button id="<?php echo $star_plan['button_id']; ?>" class="buy-stars-btn" style="background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;" onclick="showPaymentModal('<?php echo $star_plan['button_id']; ?>', 'stars', '<?php echo $star_plan['price']; ?>')">Buy Stars</button>
        </div>
    <?php endforeach; */ ?>

</div>

<!-- Pop-up modal for payment -->
<div id="payment-modal" style="display:none; position:fixed; left:0; top:0; width:100%; height:100%; background-color: rgba(0, 0, 0, 0.5); justify-content:center; align-items:center;">
    <div style="background:white; padding:20px; width:400px; border-radius:10px; text-align:center;">
        <h2>Enter Debit Card Details</h2>
        <form id="payment-form">
            <div class="form-group">
                <label for="card-number">Debit Card Number</label>
                <input type="text" id="card-number" placeholder="0000 0000 0000 0000" required pattern="^(\d{4} \d{4} \d{4} \d{4})$" title="0000 0000 0000 0000" />
            </div>
            <input type="text" id="pricing" disabled style="font-size: 24px;text-align: center;" />
            <input type="hidden" id="selected-plan" />
            <input type="hidden" id="purchase-type" />
            <button type="submit" style="background-color: green; color: white; padding: 10px 20px; border: none; border-radius: 5px;">Pay</button>
        </form>
        <button id="close-modal" style="margin-top:10px; background-color: red; color: white; padding: 10px 20px; border: none; border-radius: 5px;" onclick="closeModal()">Cancel</button>
    </div>
</div>

<script>
// Function to show payment modal
function showPaymentModal(planId, purchaseType, price) {
    document.getElementById('payment-modal').style.display = 'flex';
    document.getElementById('selected-plan').value = planId;
    document.getElementById('purchase-type').value = purchaseType;
    document.getElementById('pricing').value = price + " Pesos";
}

// Function to close modal
function closeModal() {
    document.getElementById('payment-modal').style.display = 'none';
}

// Handle payment form submission
document.getElementById('payment-form').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const cardNumber = document.getElementById('card-number').value;
    const selectedPlan = document.getElementById('selected-plan').value;
    const purchaseType = document.getElementById('purchase-type').value;

    // Send AJAX request to process payment
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'process_payment.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        const response = JSON.parse(xhr.responseText);
        if (xhr.status === 200 && response.success) {
            alert('Payment successful! Your purchase has been processed.');
            document.getElementById('payment-modal').style.display = 'none';
            window.location.reload();
        } else {
            alert('Payment failed: ' + (response.error || 'Unknown error occurred.'));
        }
    };

    xhr.onerror = function() {
        alert('Request failed. Please try again later.');
    };

    xhr.send(`plan=${selectedPlan}&cardNumber=${cardNumber}&purchaseType=${purchaseType}`);
});

</script>

<?php
include 'footer.php';
?>
