<?php
include 'files/db_connection.php';

// Query to get all distinct `nom_agence` values
$sql = "SELECT DISTINCT nom_agence FROM agence";

// Execute the query
$result = $conn->query($sql);

// Prepare an array to store agence names
$agences = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $agences[] = $row;
    }
    echo json_encode($agences); // Return the agences as a JSON array
} else {
    echo json_encode([]); // Return an empty array if no agencies found
}
?>
