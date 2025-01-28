<?php
session_start();
include 'db_connection.php'; // Include your database connection file

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL statement
    $sql = "SELECT id, username, email, address, phone, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    // Check if the statement was prepared successfully
    if ($stmt === false) {
        die("Error preparing statement: " . htmlspecialchars($conn->error));
    }

    // Bind the username to the prepared statement
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a user with the given username exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Debugging: Log the entered and stored password for checking
        error_log("Entered password: " . $password); // Logs the entered password
        error_log("Hashed password from DB: " . $user['password']); // Logs the hashed password from the database

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['user_id'] = $user['id']; // Set user id in session
            $_SESSION['username'] = $user['username']; // Set username in session
            $_SESSION['email'] = $user['email']; // Set email in session
            $_SESSION['address'] = $user['address']; // Set address in session
            $_SESSION['phone'] = $user['phone']; // Set phone number in session
            $_SESSION['role'] = $user['role']; // Set user role in session

            // Check user role and redirect accordingly
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.html"); // Redirect to admin dashboard
            } else {
                header("Location: 1.html"); // Redirect to user home page
            }
            exit();
        } else {
            // Password is incorrect
            error_log("Password verification failed");
            header("Location: user_login.html?error=password");
            exit();
        }
    } else {
        // Username not found in the database
        header("Location: user_login.html?error=username");
        exit();
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // If the form was not submitted via POST, redirect to the login page
    header("Location: user_login.html");
    exit();
}
?>
