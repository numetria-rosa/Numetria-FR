<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection file
require_once 'files/db_connection.php';
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Define a function to get the country name based on the country code
function getCountryName($code_pays) {
    $countries = [
        // Your country code mappings...
    ];
    return isset($countries[$code_pays]) ? $countries[$code_pays] : $code_pays;
}

// Fetch data from the `agence` table
$query = "
    SELECT a.pid, a.nom_agence, a.code_pays, a.ville, a.credit, a.etat, a.email
    FROM agence a
    WHERE a.etat = 1
";


$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pid = $row['pid'];
        $nom_agence = htmlspecialchars($row['nom_agence']);
        $code_pays = getCountryName($row['code_pays']);
        $ville = htmlspecialchars($row['ville']);
        $credit = htmlspecialchars($row['credit']);
        $etat = htmlspecialchars($row['etat']);
        $email = htmlspecialchars($row['email']);

    // Fetch total réglements (credits)
            $totalReglementQuery = "
            SELECT SUM(reg) AS total_reglement
            FROM reglement
            WHERE agence = $pid AND etat_reg = 'encaissé' AND rest = 0
            ";
            $totalReglementResult = $conn->query($totalReglementQuery);
            if ($totalReglementResult && $totalReglementRow = $totalReglementResult->fetch_assoc()) {
            $totalReglement = isset($totalReglementRow['total_reglement']) ? (float)$totalReglementRow['total_reglement'] : 0;
            } else {
            $totalReglement = 0;
            }


        // Fetch total debit (grossamount)
        $totalDebitQuery = "
            SELECT SUM(grossamount) AS total_debit
            FROM booking
            WHERE pidagence = $pid AND currentstatus = 'vouchered'
        ";
        $totalDebitResult = $conn->query($totalDebitQuery);
        if ($totalDebitResult && $totalDebitRow = $totalDebitResult->fetch_assoc()) {
            $totalDebit = isset($totalDebitRow['total_debit']) ? (float)$totalDebitRow['total_debit'] : 0;
        } else {
            $totalDebit = 0;
        }

        // Fetch total guarantee (gar) from the garantie table
        $totalGrantiQuery = "
            SELECT SUM(gar) AS total_granti
            FROM garantie
            WHERE agence = $pid
        ";
        $totalGrantiResult = $conn->query($totalGrantiQuery);
        if ($totalGrantiResult && $totalGrantiRow = $totalGrantiResult->fetch_assoc()) {
            $totalGranti = isset($totalGrantiRow['total_granti']) ? (float)$totalGrantiRow['total_granti'] : 0;
        } else {
            $totalGranti = 0;
        }

        // Calculate Total Solde
        $totalSolde = $totalReglement - $totalDebit;

        // Skip agency with totalSolde of 0
        if ($totalSolde == 0) {
            echo "<script>console.log('Agency skipped: PID = $pid, Total Solde = 0');</script>";
            continue;
        }
        

        // Etat display logic
        if ($etat == 0) {
            $etatText = 'Désactivé';
            $btnClass = 'btn btn-success';
            $btnText = 'Activate';
            $btnAction = 'activate';
        } else {
            $etatText = 'Activé';
            $btnClass = 'btn btn-danger';
            $btnText = 'Deactivate';
            $btnAction = 'deactivate';
        }

        echo "<tr>
        <td>{$nom_agence}</td>
        <td>{$email}</td>
        <td>{$ville}</td>
        <td style='font-weight: bold; color: green;'>{$totalReglement}</td>
        <td style='font-weight: bold; color: red;'>{$totalDebit}</td>
        <td style='font-weight: bold; color: orange;'>{$totalGranti}</td>
        <td style='font-weight: bold;'>{$credit}</td>
        <td style='font-weight: bold; color:rgb(5, 126, 151);'>{$totalSolde}</td>
    </tr>";
    
    }
} else {
    echo "<tr><td colspan='6'>No records found</td></tr>";
}
?>



