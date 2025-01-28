<?php
session_start(); // Start the session to access session variables
include 'db_connection.php';

// Fetch products from the database
$result = $conn->query("SELECT * FROM products");
$products = [];

if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
    
    // Ensure the price is a float
    foreach ($products as &$product) {
        $product['price'] = floatval($product['price']); // Convert to float
    }
} else {
    echo "Error: " . $conn->error; // Handle any errors
}

// Prepare the response data
$response = [
    'isLoggedIn' => isset($_SESSION['user_id']), // Check if the user is logged in
    'products' => $products,
];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
