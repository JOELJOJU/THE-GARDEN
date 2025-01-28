<?php
// Include database connection
include 'db_connection.php';

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['product_id']); // Ensure the ID is an integer
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "<script>alert('Product deleted successfully!'); window.location.href='manage_products.php';</script>";
                exit; // Stop script execution after redirecting
            } else {
                echo "<script>alert('Error deleting product: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Error preparing the statement: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Invalid product ID!');</script>";
    }
}

// Fetch existing products from the database
$result = $conn->query("SELECT * FROM products");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - THE GARDEN</title>
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        /* Add your styles here */
        .products-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .product-item {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 10px;
            background-color: #fff;
            width: 200px; /* Fixed width for product items */
            text-align: center;
        }
        .product-actions {
            margin-top: 10px;
        }
        button {
            margin: 5px;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h1>Manage Products</h1>

<?php
if ($result && $result->num_rows > 0) {
    echo "<div class='products-container'>";
    while ($product = $result->fetch_assoc()) {
        $productId = intval($product['id']);
        $productName = htmlspecialchars($product['name']);
        $productPrice = number_format($product['price'], 2);
        $productDescription = htmlspecialchars($product['description']);
        $productImage = htmlspecialchars($product['image']);

        echo "<div class='product-item'>
            <img src='uploads/{$productImage}' width='100' alt='{$productName}'>
            <h3>{$productName}</h3>
            <p>Price: $ {$productPrice}</p>
            <p>{$productDescription}</p>
            <div class='product-actions'>
                <form action='manage_products.php' method='post' style='display:inline;'>
                    <input type='hidden' name='product_id' value='{$productId}'>
                    <button type='submit' name='action' value='delete' onclick='return confirm(\"Are you sure you want to delete this product?\")'>Delete</button>
                </form>
                <form action='edit_product.php' method='get' style='display:inline;'>
                    <input type='hidden' name='id' value='{$productId}'>
                    <button type='submit'>Update</button>
                </form>
            </div>
        </div>";
    }
    echo "</div>";
} else {
    echo "<p>No products found.</p>";
}

// Close the database connection
$conn->close();
?>

</body>
</html>
