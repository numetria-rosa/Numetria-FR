<?php
include 'db_connection.php'; // Your database connection

$filterAgence = $_POST['agence'] ?? '';
$filterEtat = $_POST['etat'] ?? '';

// Base query
$query = "SELECT * FROM reservations WHERE 1";

// Add filters dynamically
if (!empty($filterAgence)) {
    $query .= " AND agence = '" . $conn->real_escape_string($filterAgence) . "'";
}
if (!empty($filterEtat)) {
    $query .= " AND etat = '" . $conn->real_escape_string($filterEtat) . "'";
}

$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Generate row color based on `bookingservicetype`
        $serviceType = strtolower(trim(htmlspecialchars($row['bookingservicetype']))) ?: "default";
        $colors = [
            'hotels' => 'rgb(255, 165, 0)', 'hotelsdirect' => 'rgb(0, 128, 0)',
            'flights' => 'rgb(84, 4, 4)', 'ratehawk' => 'rgb(65, 105, 225)'
        ];
        $rowColor = $colors[$serviceType] ?? 'transparent';
        $cellStyle = 'background-color: ' . $rowColor . '; color: white;';

        // Generate row dynamically
        echo "<tr>";
        echo "<td style='$cellStyle'>" . htmlspecialchars($row['reference_reservation']) . "</td>";
        echo "<td>" . htmlspecialchars($row['agence']) . "</td>";
        echo "<td>" . htmlspecialchars($row['agent']) . "</td>";
        echo "<td>" . htmlspecialchars($row['destination']) . "</td>";
        echo "<td>" . htmlspecialchars($row['client']) . "</td>";
        echo "<td>" . htmlspecialchars($row['date_reservation']) . "</td>";
        echo "<td>" . htmlspecialchars($row['prix']) . " " . htmlspecialchars($row['currency']) . "</td>";
        echo "<td style='color: " . ($row['etat'] == 'Annulée' ? 'red' : ($row['etat'] == 'On attente' ? 'orange' : 'green')) . "; font-weight: bold;'>" . htmlspecialchars($row['etat']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8'>Aucune donnée trouvée</td></tr>";
}
?>
