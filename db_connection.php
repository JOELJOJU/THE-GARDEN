<?php
// Database connection setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root"; // Default username for XAMPP is 'root'
$password = ""; // Default password for XAMPP is an empty string
$dbname = "garden_db"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Displays the error and stops execution
}

// Optional: Set character set to UTF-8 (optional but recommended)
$conn->set_charset("utf8");

// Now you can run your queries using $conn

?>
