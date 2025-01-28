<?php
// Database connection setup
include 'db_connection.php'; 

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Regular expressions for validation
    $username_regex = '/^[a-zA-Z0-9]+$/';
    $email_regex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    $password_regex = '/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}/';

    // Validate username
    if (!preg_match($username_regex, $username)) {
        echo "<script>alert('Username must be alphanumeric only!');</script>";
        exit();
    }

    // Validate email
    if (!preg_match($email_regex, $email)) {
        echo "<script>alert('Invalid email format!');</script>";
        exit();
    }

    // Validate password
    if (!preg_match($password_regex, $password)) {
        echo "<script>alert('Password must be at least 8 characters long and include one uppercase letter, one lowercase letter, one number, and one special character.');</script>";
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the user data into the database
    $sql = "INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())";

    // Prepare and execute the statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        if ($stmt->execute()) {
            echo "<script>alert('Signup successful! Redirecting to login page.');</script>";
            echo "<script>window.location.href = 'user_login.html';</script>";
        } else {
            echo "<script>alert('Error: Could not register user.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error: Could not prepare statement.');</script>";
    }

    // Close the connection
    $conn->close();
}
?>
