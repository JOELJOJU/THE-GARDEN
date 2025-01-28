<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - THE GARDEN</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <header>
        <h1>Add New Product</h1>
        <nav id="navbar">
            <a href="admin_dashboard.html">Dashboard</a>
            
            <a href="manage_products.html">Manage Products</a>
            <a href="admin_logout.html">Logout</a>
        </nav>
    </header>

    <main>
        <h2>Add New Product</h2>
        
        <!-- Add New Product Form -->
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product-name">Product Name:</label>
                <input type="text" name="product_name" id="product-name" placeholder="Enter product name" required>
            </div>

            <div class="form-group">
                <label for="product-price">Product Price:</label>
                <input type="number" step="0.01" name="product_price" id="product-price" placeholder="Enter product price" required>
            </div>

            <div class="form-group">
                <label for="product-description">Description:</label>
                <textarea name="product_description" id="product-description" placeholder="Enter product description" required></textarea>
            </div>

            <div class="form-group">
                <label for="product-image">Product Image:</label>
                <input type="file" name="product_image" id="product-image" accept=".jpg, .jpeg, .png, .gif" required>
            </div>

            <button type="submit" class="submit-btn">Add Product</button>
        </form>

        <?php
        // Include database connection
        include 'db_connection.php';

        // Check if the form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Initialize variables and error message holder
            $errors = [];
            $name = htmlspecialchars(trim($_POST['product_name']));
            $price = filter_var($_POST['product_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $description = htmlspecialchars(trim($_POST['product_description']));
            $image = 'default.jpg'; // Set a default image

            // Validate product name and price
            if (empty($name) || strlen($name) > 255) {
                $errors[] = "Product name must not be empty and should be less than 255 characters.";
            }

            if ($price <= 0) {
                $errors[] = "Price must be a valid number greater than 0.";
            }

            // Check if an image is uploaded and handle file upload
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $targetDir = 'uploads/';
                $image = basename($_FILES['product_image']['name']);
                $targetFilePath = $targetDir . $image;
                $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

                // Allow only certain file formats for the image
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($fileType, $allowedTypes)) {
                    $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
                } else {
                    // Attempt to move the uploaded file to the target directory
                    if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $targetFilePath)) {
                        $errors[] = "Error uploading image.";
                    }
                }
            }

            // If no errors, proceed with inserting the product into the database
            if (empty($errors)) {
                $sql = "INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("sdss", $name, $price, $description, $image);
                    if ($stmt->execute()) {
                        echo "<p style='color:green;'>Product added successfully.</p>";
                    } else {
                        echo "<p style='color:red;'>Error adding product: " . $stmt->error . "</p>";
                    }
                    $stmt->close();
                } else {
                    echo "<p style='color:red;'>Error preparing statement: " . $conn->error . "</p>";
                }
            } else {
                // Display errors if there are any validation issues
                foreach ($errors as $error) {
                    echo "<p style='color:red;'>$error</p>";
                }
            }
        }

        // Close the database connection
        $conn->close();
        ?>

    </main>

    <footer>
        <p>Thank you for adding new products.</p>
    </footer>
</body>
</html>
