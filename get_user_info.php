<?php
// Check if session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Start session if not already started
}

// Database connection (assuming you have a separate db_connection.php)
include 'db_connection.php'; 

function getUserInfo($userId) {
    global $conn;  // Assuming $conn is your database connection

    // Fetch user info from the users table
    $query = "SELECT username, email, address, phone FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        die(json_encode(['success' => false, 'message' => 'MySQL prepare error: ' . $conn->error]));
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists and return their details
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        return $user;  // Return user details as an associative array
    } else {
        return null;  // User not found
    }
}
?>
