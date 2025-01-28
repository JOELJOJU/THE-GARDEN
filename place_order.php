<?php
session_start();
include 'db_connection.php'; // Include your database connection

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $data = json_decode(file_get_contents("php://input"), true);
    $paymentMethod = $data['payment_method'];

    // Insert new order
    $orderQuery = "INSERT INTO orders (user_id, payment_method, order_date) VALUES (?, ?, NOW())";
    $orderStmt = $conn->prepare($orderQuery);
    $orderStmt->bind_param("is", $userId, $paymentMethod);

    if ($orderStmt->execute()) {
        $orderId = $orderStmt->insert_id;

        // Insert ordered items from cart
        $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                      SELECT ?, product_id, quantity, price
                      FROM cart WHERE user_id = ?";
        $itemStmt = $conn->prepare($itemQuery);
        $itemStmt->bind_param("ii", $orderId, $userId);

        if ($itemStmt->execute()) {
            // Clear the user's cart
            $clearCartQuery = "DELETE FROM cart WHERE user_id = ?";
            $clearCartStmt = $conn->prepare($clearCartQuery);
            $clearCartStmt->bind_param("i", $userId);
            $clearCartStmt->execute();

            echo json_encode(['success' => true, 'message' => 'Order placed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add items to order']);
        }
        $itemStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create order']);
    }
    $orderStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
}
$conn->close();
?>
