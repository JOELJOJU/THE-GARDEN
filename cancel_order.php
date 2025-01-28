<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order_id']) && isset($_SESSION['user_id'])) {
    $orderId = $_POST['order_id'];
    $userId = $_SESSION['user_id'];

    // Fetch the payment method of the order
    $paymentQuery = $conn->prepare("SELECT payment_method FROM orders WHERE order_id = ? AND user_id = ?");
    $paymentQuery->bind_param("ii", $orderId, $userId);
    $paymentQuery->execute();
    $paymentResult = $paymentQuery->get_result();

    if ($paymentResult->num_rows > 0) {
        $paymentData = $paymentResult->fetch_assoc();
        $paymentMethod = $paymentData['payment_method'];

        // Prepare a statement to delete the order
        $deleteQuery = $conn->prepare("DELETE FROM orders WHERE order_id = ? AND user_id = ?");
        $deleteQuery->bind_param("ii", $orderId, $userId);

        if ($deleteQuery->execute()) {
            // Check if the payment method is UPI or Credit/Debit Card
            if (strtolower($paymentMethod) === 'upi' || strtolower($paymentMethod) === 'credit/debit card') {
                // Redirect to the refund notification page
                header("Location: refund_notification.php?refund=true");
                exit();
            } else {
                // Redirect back to the orders page with a success message
                header("Location: orders.php?message=Order cancelled successfully");
                exit();
            }
        } else {
            // Redirect back to the orders page with an error message
            header("Location: orders.php?error=Failed to cancel order");
            exit();
        }

        $deleteQuery->close();
    } else {
        // Redirect if the order is not found
        header("Location: orders.php?error=Order not found");
        exit();
    }

    $paymentQuery->close();
} else {
    // Redirect if accessed directly
    header("Location: orders.php");
    exit();
}

$conn->close();
?>
