<?php
session_start(); // Start the session

// Include your database connection file
include 'db_connection.php'; 

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You are not logged in. Please log in to access your account.'); window.location.href = 'user_login.html';</script>";
    exit(); // Exit the script after showing the alert
}

$user_id = $_SESSION['user_id']; // Retrieve user ID from the session

// Fetch user data from the existing users table
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error)); // Print error if prepare fails
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


// Initialize a variable to hold error or success messages
$message = "";

// Check if the form is submitted for account update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update'])) {
        // Update profile logic
        $username = $_POST['username'] ?? ''; // Use null coalescing to prevent undefined index notices
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';

        $update_sql = "UPDATE users SET username = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if ($update_stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error)); // Print error if prepare fails
        }
        $update_stmt->bind_param("ssssi", $username, $email, $phone, $address, $user_id);
        
        if ($update_stmt->execute()) {
            $message = "<p style='color: green;'>Account updated successfully!</p>";
            // Re-fetch the user data to reflect the changes
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc(); // Refresh user data
        } else {
            $message = "<p style='color: red;'>Error updating account: " . htmlspecialchars($conn->error) . "</p>";
        }

        $update_stmt->close();
    } elseif (isset($_POST['delete'])) {
        // Delete profile logic
        $delete_sql = "DELETE FROM users WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        if ($delete_stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error)); // Print error if prepare fails
        }
        $delete_stmt->bind_param("i", $user_id);
        
        if ($delete_stmt->execute()) {
            session_destroy(); // Destroy the session
            header("Location: account1.html"); // Redirect to login page after deletion
            exit;
        } else {
            $message = "<p style='color: red;'>Error deleting account: " . htmlspecialchars($conn->error) . "</p>";
        }

        $delete_stmt->close();
    }
}

// Close the original statement and connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - THE GARDEN</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>My Account</h1>
    </header>

    <nav id="navbar">
        <a href="1.html">Home</a>
        <a href="products.html">Products</a>
        <a href="contact.html">Contact</a>
    </nav>

    <div class="container">
        <h2>Account Details</h2>
        
        <?php if (!empty($message)): ?>
            <div><?php echo $message; ?></div> <!-- Display success or error messages -->
        <?php endif; ?>

        <?php if ($user): ?>
            <?php if (isset($_POST['edit'])): ?>
                <!-- Display edit form when "Edit" button is clicked -->
                <form method="POST">
                    <p><strong>Name:</strong></p>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    
                    <p><strong>Email:</strong></p>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    
                    <p><strong>Phone:</strong></p>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    
                    <p><strong>Address:</strong></p>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                    
                    <button type="submit" name="update">Update Account</button> <!-- Change the button name to 'update' -->
                </form>
            <?php else: ?>
                <!-- Display account details with edit and delete buttons -->
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                <form method="POST">
                    <button type="submit" name="edit">Edit Account</button>
                    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete your profile? This action cannot be undone.');">Delete Profile</button> <!-- Delete button -->
                </form>
            <?php endif; ?>
        <?php else: ?>
            <p>No user data found.</p>
        <?php endif; ?>
    </div>

    <footer id="contact">
        <p>Thank you for visiting our website.</p>
    </footer>
</body>
</html>
