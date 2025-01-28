<?php
// save_user_info.php

session_start();
header('Content-Type: application/json');

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include the database connection file
    include 'db_connection.php';

    // Decode JSON data from the request
    $data = json_decode(file_get_contents('php://input'), true);
    $address = isset($data['address']) ? trim($data['address']) : null;
    $phone = isset($data['phone']) ? trim($data['phone']) : null;

    // Check for a valid session
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Prepare the update statement with address and phone
        $stmt = $conn->prepare("UPDATE users SET address = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssi", $address, $phone, $user_id);

        // Execute the update and check for success
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Address and phone number updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update address and phone number.']);
        }

        $stmt->close();
    } else {
        // If the user is not logged in
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    }

    // Close the database connection
    $conn->close();
} else {
    // If the request method is not POST
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
