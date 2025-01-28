<?php
session_start();
include 'db_connection.php';

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Get user ID if logged in

// Fetch products from the database
$result = $conn->query("SELECT * FROM products");
$products = [];

if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);

    // Ensure the price is a float and check if the product is in the user's cart
    foreach ($products as &$product) {
        $product['price'] = floatval($product['price']); // Convert to float
        
        if ($userId) {
            // Check if this product is in the user's cart
            $productId = $product['id'];
            $cartCheck = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $cartCheck->bind_param("ii", $userId, $productId);
            $cartCheck->execute();
            $cartResult = $cartCheck->get_result();
            
            // Set the 'inCart' flag and quantity if the product is in the cart
            if ($cartResult->num_rows > 0) {
                $cartRow = $cartResult->fetch_assoc();
                $product['inCart'] = true;
                $product['cartQuantity'] = intval($cartRow['quantity']);
            } else {
                $product['inCart'] = false;
                $product['cartQuantity'] = 0;
            }
            $cartCheck->close();
        } else {
            $product['inCart'] = false; // Default to not in cart if user not logged in
            $product['cartQuantity'] = 0;
        }
    }
} else {
    $response = [
        'success' => false,
        'error' => "Error: " . $conn->error
    ];
    echo json_encode($response);
    $conn->close();
    exit();
}

// Prepare the response data
$response = [
    'success' => true,
    'isLoggedIn' => isset($_SESSION['user_id']), // Check if the user is logged in
    'products' => $products,
];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
