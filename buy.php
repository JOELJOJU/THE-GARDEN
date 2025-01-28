<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login_user.html');
    exit();
}

// Check if product ID is in the URL
if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    // Connect to the database
    $conn = new mysqli('localhost', 'username', 'password', 'garden_db');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch product details from the database
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if product exists
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();

        // Add product to the user's cart (this is a basic example, you can modify as needed)
        $userId = $_SESSION['user_id'];
        $sql = "INSERT INTO cart (user_id, product_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();

        echo "Product added to cart successfully!";
    } else {
        echo "Product not found.";
    }

    $conn->close();
} else {
    echo "No product selected.";
}
?>
