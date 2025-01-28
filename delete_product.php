<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php"); // Redirect if not logged in
    exit;
}

// Check if confirmed deletion
if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    $product_id = $_GET['id'] ?? null;

    if ($product_id) {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
    }

    header("Location: admin_dashboard.php"); // Redirect to dashboard after deletion
    exit;
} elseif (isset($_POST['confirm']) && $_POST['confirm'] === 'no') {
    header("Location: admin_dashboard.php"); // Redirect to dashboard if deletion is canceled
    exit;
} else {
    // If the confirmation is not set, show the confirmation form
    $product_id = $_GET['id'] ?? null;
    if ($product_id) {
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirm Deletion</title>
            <link rel="stylesheet" href="admin_styles.css">
        </head>
        <body>
            <h2>Are you sure you want to delete this product?</h2>
            <form method="POST">
                <input type="hidden" name="confirm" value="yes">
                <button type="submit">Yes</button>
                <a href="admin_dashboard.php"><button type="button">Cancel</button></a>
            </form>
        </body>
        </html>';
    } else {
        header("Location: admin_dashboard.php"); // Redirect if no product ID is provided
        exit;
    }
}
?>
