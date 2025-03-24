<?php
require 'files/db_connection.php'; // Include your database connection

// Check if query is set
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Prepare the SQL query with a LIKE statement and filtering by active agencies (etat = 1)
$sql = "SELECT pid, nom_agence FROM agence WHERE nom_agence LIKE ? AND etat = 1";
$stmt = $conn->prepare($sql);

// Bind the parameter with % around the query string
$searchTerm = "%" . $query . "%";
$stmt->bind_param("s", $searchTerm);

$stmt->execute();
$result = $stmt->get_result();

$agencies = [];
while ($row = $result->fetch_assoc()) {
    $agencies[] = $row;
}

// Return the results as JSON
echo json_encode($agencies);
?>
