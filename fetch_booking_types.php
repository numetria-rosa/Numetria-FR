<?php
// Include the database connection file
include 'files/db_connection.php';

// Query to fetch distinct booking service types
$sql = "SELECT DISTINCT bookingservicetype FROM booking";
$result = $conn->query($sql);

// Check for errors
if (!$result) {
    die("Query Error: " . $conn->error);
}

// Display the unique booking service types
echo "<h2>Unique Booking Service Types:</h2>";
echo "<ul>";

while ($row = $result->fetch_assoc()) {
    echo "<li>" . htmlspecialchars($row['bookingservicetype']) . "</li>";
}

echo "</ul>";

// Close the connection
$conn->close();
?>
