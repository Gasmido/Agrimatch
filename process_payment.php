<?php
include 'config.php';
session_start();

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in.']);
    exit();
}

// Get the plan, purchase type, and card number from the POST request
$plan = $_POST['plan'];
$purchaseType = $_POST['purchaseType'];
$cardNumber = $_POST['cardNumber'];
$userId = $_SESSION['user_id'];

$prices = [
    'basic' => 169,
    'premium' => 1416,
    '50stars-btn' => 49,
    '110stars-btn' => 99,
    '220stars-btn' => 199,
    '500stars-btn' => 399,
    '999stars-btn' => 599
];

// Validate plan
if (!array_key_exists($plan, $prices)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid plan.']);
    exit();
}

// Simulate payment processing
$paymentSuccess = simulatePayment($cardNumber, $prices[$plan]);

if ($paymentSuccess) {
    if ($purchaseType === 'subscription') {
        // Subscription logic
        $expiryDate = date('Y-m-d', strtotime('+1 year'));
        
        // Get the price for the selected plan
        $price = $prices[$plan]; // Fetch the price based on the selected plan

        // Check if the user already has an active subscription
        $stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND expiry_date > NOW()");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'You already have an active subscription.']);
            exit();
        }

        $stmt->close();

        // Insert new subscription with price
        $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, plan, expiry_date, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issd", $userId, $plan, $expiryDate, $price); // Added price here
        $stmt->execute();
        $stmt->close();
    }
    // (Remaining code unchanged)

 else if ($purchaseType === 'stars') {
        // Check if user has an active subscription before allowing star purchase
        $stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND expiry_date > NOW()");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            http_response_code(400);
            echo json_encode(['error' => 'You must have an active subscription to purchase stars.']);
            exit();
        }

        // Handle star purchases
        $stars = 0;
        switch ($plan) {
            case '50stars-btn':
                $stars = 50;
                break;
            case '110stars-btn':
                $stars = 110;
                break;
            case '220stars-btn':
                $stars = 220;
                break;
            case '500stars-btn':
                $stars = 500;
                break;
            case '999stars-btn':
                $stars = 999;
                break;
        }

        // Check if user already has a star balance
        $stmt = $conn->prepare("SELECT stars FROM user_stars WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userStars = $result->fetch_assoc();
        $stmt->close();

        if ($userStars) {
            // Update existing star balance
            $newStarBalance = $userStars['stars'] + $stars;
            $stmt = $conn->prepare("UPDATE user_stars SET stars = ? WHERE user_id = ?");
            $stmt->bind_param("ii", $newStarBalance, $userId);
        } else {
            // Insert new star balance
            $stmt = $conn->prepare("INSERT INTO user_stars (user_id, stars) VALUES (?, ?)");
            $stmt->bind_param("ii", $userId, $stars);
        }
        $stmt->execute();
        $stmt->close();
    }

    echo json_encode(['success' => 'Payment successful!']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Payment failed.']);
}

// Simulate payment processing function
function simulatePayment($cardNumber, $amount) {
    // Here, you would typically use a payment API to process the payment.
    // For now, we will simulate success or failure based on the card number.
    
    if ($cardNumber != "") {
        // Simulate a successful payment (e.g., by checking the last digit)
        return true;
    }

    // Simulate a failed payment for any other case
    return false;
}
?>
