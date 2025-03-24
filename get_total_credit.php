<?php
// Include the database connection
include 'files/db_connection.php';

// Get the agency ID from the request
$agenceId = isset($_GET['agenceId']) ? intval($_GET['agenceId']) : 0;

if ($agenceId == 0) {
    die("Invalid Agency ID.");
}

// Query to get the total credit based on the "encaissé" state
$totalCreditQuery = "
    SELECT SUM(reg) AS total_credit 
    FROM reglement 
    WHERE agence = $agenceId AND etat_reg = 'encaissé'
";

$totalCreditResult = $conn->query($totalCreditQuery);

// Fetch the result
$totalCredit = $totalCreditResult->fetch_assoc()['total_credit'] ?? 0;

// Return the total credit in a JSON format
echo json_encode(['total_credit' => number_format($totalCredit, 2)]);
?>
