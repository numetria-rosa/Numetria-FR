<?php
// Database configuration
$host = "localhost";      // Replace with your database host
$username = "root";       // Replace with your database username
$password = "";           // Replace with your database password
$database = "demo";        // Replace with your database name



// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Uncomment the following line to confirm connection (for testing only)
//echo "Database connection successful!";
?>
