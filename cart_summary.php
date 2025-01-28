<?php
session_start();
include 'db_connection.php'; // Include your database connection

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Query to get cart items for the user
    $query = "SELECT p.name, p.price, c.quantity 
              FROM cart c
              JOIN products p ON c.product_id = p.id
              WHERE c.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $cartItems = [];
    $totalPrice = 0;

    while ($row = $result->fetch_assoc()) {
        $row['total'] = $row['price'] * $row['quantity']; // Calculate item total
        $totalPrice += $row['total'];
        $cartItems[] = $row;
    }

    echo json_encode(['cart_items' => $cartItems, 'total_price' => $totalPrice]);
    $stmt->close();
} else {
    echo json_encode(['error' => 'User not logged in']);
}
$conn->close();
?>
