<?php
session_start();
include 'db_connection.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_user.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Prepare and execute SQL query to fetch cart items for the logged-in user
$sql = "SELECT cart.cart_id, cart.quantity, cart.date_added, products.name, products.price, products.image 
        FROM cart 
        JOIN products ON cart.product_id = products.id 
        WHERE cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_price = 0.0;

// Fetch each item and calculate the total price
while ($row = $result->fetch_assoc()) {
    $row['price'] = floatval($row['price']); // Ensure price is a float
    $row['quantity'] = intval($row['quantity']); // Ensure quantity is an integer
    $row['cart_id'] = intval($row['cart_id']); // Ensure cart_id is an integer
    $cart_items[] = $row;
    $total_price += $row['price'] * $row['quantity'];
}

$stmt->close();
$conn->close();

// Send cart data in JSON format with 2 decimal places for total price
header('Content-Type: application/json');
echo json_encode([
    'cart_items' => $cart_items,
    'total_price' => round($total_price, 2)
]);
?>
