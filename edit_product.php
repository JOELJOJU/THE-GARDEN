<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit;
}

$product_id = $_GET['id'] ?? null;

if ($product_id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $image = $_FILES['image']['name']; // Assuming you're uploading a new image

    // Add your image upload handling here if necessary

    // Update the product in the database
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sdssi", $name, $price, $description, $image, $product_id);
    $stmt->execute();

    header("Location: manage_products.php"); // Redirect to Manage Products page after update
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - THE GARDEN</title>
</head>
<body>
    <h1>Edit Product</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="name">Product Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

        <label for="price">Price:</label>
        <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>

        <label for="description">Description:</label>
        <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea>

        <label for="image">Image:</label>
        <input type="file" name="image">

        <button type="submit">Update Product</button>
    </form>
</body>
</html>
