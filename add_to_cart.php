<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

// Ensure product_id is set
if (!isset($data['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID not provided']);
    exit();
}

$product_id = $data['product_id'];
$quantity = 1; // Default quantity, can be adjusted

// Check if the product is already in the cart
$checkCart = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
$checkCart->bind_param("ii", $user_id, $product_id);
$checkCart->execute();
$result = $checkCart->get_result();

if ($result->num_rows > 0) {
    // If product exists in the cart, update quantity
    $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
} else {
    // If product is not in the cart, insert new entry
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, date_added) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
}

// Execute statement and check for success
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Product added to cart']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
}

// Close statements and database connection
$stmt->close();
$checkCart->close();
$conn->close();
?>
