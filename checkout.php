<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login_user.html');
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user details
$userInfoQuery = $conn->prepare("SELECT username, email, address, phone FROM users WHERE id = ?");
if ($userInfoQuery === false) {
    die("Error preparing the SQL statement: " . $conn->error);
}

$userInfoQuery->bind_param("i", $userId);
$userInfoQuery->execute();
$userInfoResult = $userInfoQuery->get_result();

if ($userInfoResult->num_rows > 0) {
    $userInfo = $userInfoResult->fetch_assoc();
    $username = htmlspecialchars($userInfo['username']);
    $email = htmlspecialchars($userInfo['email']);
    $address = htmlspecialchars($userInfo['address']);
    $phone = htmlspecialchars($userInfo['phone']);
} else {
    echo "<p>Error: User details not found. Please try again later.</p>";
    exit();
}

// Fetch cart items
$cartItemsQuery = $conn->prepare("SELECT p.name, p.price, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
if ($cartItemsQuery === false) {
    die("Error preparing the SQL statement: " . $conn->error);
}

$cartItemsQuery->bind_param("i", $userId);
$cartItemsQuery->execute();
$cartItemsResult = $cartItemsQuery->get_result();

// Calculate total price
$totalPrice = 0;
$cartItems = [];
while ($row = $cartItemsResult->fetch_assoc()) {
    $cartItems[] = $row;
    $totalPrice += $row['price'] * $row['quantity'];
}

// Order processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_address = $_POST['address'];
    $user_phone = $_POST['phone'];
    $payment_method = $_POST['payment_method'];

    // Insert order into the database
    $orderInsertQuery = $conn->prepare("INSERT INTO orders (user_id, order_date, total_price, payment_method, address, phone) VALUES (?, NOW(), ?, ?, ?, ?)");
    if ($orderInsertQuery === false) {
        die("Error preparing the SQL statement: " . $conn->error);
    }

    // Bind the parameters to the query
    $orderInsertQuery->bind_param("idsss", $userId, $totalPrice, $payment_method, $user_address, $user_phone);

    if ($orderInsertQuery->execute()) {
        // Redirect to payment success page or order success page based on payment method
        if ($payment_method === "credit_debit_card" || $payment_method === "upi") {
            header('Location: payment_success.html');
        } else {
            header('Location: order_success.html');
        }
        exit();
    } else {
        echo "<p>Error: Order could not be placed. Please try again later.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - THE GARDEN</title>
    <link rel="stylesheet" href="stylepro.css">
    <style>
        #checkout-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 8px;
        }
        .form-group, .payment-details {
            margin-bottom: 15px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
        }
        #order-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .total-price {
            font-weight: bold;
        }
        button {
            padding: 10px;
            background-color: #2a7f2a;
            color: #fff;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
<header>
    <h1>CHECKOUT</h1>
</header>
<nav id="navbar">
    <a href="1.html">Home</a>
    <a href="products.html">Products</a>
    <a href="cart.html">Cart</a>
    <a href="contact.html">Contact</a>
</nav>
<section id="checkout-container">
    <h2>Billing Address</h2>
    <form id="checkout-form" method="POST" action="checkout.php">
        <div class="form-group">
            <label for="username">Name</label>
            <input type="text" id="username" name="username" value="<?php echo $username; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo $email; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo $address; ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>" required>
        </div>

        <h2>Order Summary</h2>
        <div id="order-summary">
            <table>
                <tr><th>Product</th><th>Quantity</th><th>Price</th></tr>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="2" class="total-price">Total:</td>
                    <td class="total-price">₹<?php echo number_format($totalPrice, 2); ?></td>
                </tr>
            </table>
        </div>

        <h2>Payment Options</h2>
        <select id="payment-method" name="payment_method" required onchange="updatePaymentDetails()">
            <option value="credit_debit_card">Credit/Debit Card</option>
            <option value="upi">UPI</option>
            <option value="cod">Cash on Delivery</option>
        </select>

        <div id="payment-details" class="payment-details"></div>
        <button type="button" id="payment-button" onclick="validateForm()">Pay Now</button>
    </form>
</section>

<script>
    function updatePaymentDetails() {
        const paymentMethod = document.getElementById("payment-method").value;
        const paymentDetails = document.getElementById("payment-details");
        const paymentButton = document.getElementById("payment-button");

        paymentDetails.innerHTML = "";

        if (paymentMethod === "credit_debit_card") {
            paymentDetails.innerHTML = `
                <input type="text" id="card-number" placeholder="Card Number" required><br>
                <input type="text" id="card-holder" placeholder="Card Holder Name" required><br>
                <input type="text" id="expiry-date" placeholder="Expiry Date (MM/YY)" required><br>
                <input type="text" id="cvv" placeholder="CVV" required><br>
            `;
            paymentButton.textContent = "Pay Now";
        } else if (paymentMethod === "upi") {
            paymentDetails.innerHTML = `<input type="text" id="upi-id" placeholder="Enter UPI ID" required><br>`;
            paymentButton.textContent = "Pay Now";
        } else if (paymentMethod === "cod") {
            paymentButton.textContent = "Place Order";
        }
    }

    function validateForm() {
    const paymentMethod = document.getElementById("payment-method").value;

    if (paymentMethod === "credit_debit_card") {
        const cardNumber = document.getElementById("card-number").value.trim();
        const cardHolder = document.getElementById("card-holder").value.trim();
        const expiryDate = document.getElementById("expiry-date").value.trim();
        const cvv = document.getElementById("cvv").value.trim();
        if (!cardNumber || !cardHolder || !expiryDate || !cvv) {
            alert("Please fill in all the card details.");
            return;
        }

        // Show alert only for Credit/Debit Card or UPI
        alert("Payment Processing...");

        // Wait for 2 seconds before submitting the form
        setTimeout(function() {
            document.getElementById("checkout-form").submit();
        }, 2000);  // 2000 milliseconds = 2 seconds
    } 
    else if (paymentMethod === "upi") {
        const upiId = document.getElementById("upi-id").value.trim();
        if (!upiId) {
            alert("Please enter your UPI ID.");
            return;
        }

        // Show alert only for Credit/Debit Card or UPI
        alert("Payment Processing...");

        // Wait for 2 seconds before submitting the form
        setTimeout(function() {
            document.getElementById("checkout-form").submit();
        }, 2000);  // 2000 milliseconds = 2 seconds
    }
    else if (paymentMethod === "cod") {
        // For Cash on Delivery, just submit the form without showing the alert
        document.getElementById("checkout-form").submit();
    }
}


    updatePaymentDetails();
</script>
</body>
</html>
