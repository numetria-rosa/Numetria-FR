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
        'FR' => 'France',
        'US' => 'United States',
        'DE' => 'Germany',
        'IT' => 'Italy',
        'ES' => 'Spain',
        'TN' => 'Tunisia',
        'CA' => 'Canada',
        'UK' => 'United Kingdom',
        'AU' => 'Australia',
        'IN' => 'India',
        'JP' => 'Japan',
        'CN' => 'China',
        'BR' => 'Brazil',
        'MX' => 'Mexico',
        'ZA' => 'South Africa',
        'RU' => 'Russia',
        'KR' => 'South Korea',
        'AR' => 'Argentina',
        'EG' => 'Egypt',
        'NG' => 'Nigeria',
        'SA' => 'Saudi Arabia',
        'SE' => 'Sweden',
        'NO' => 'Norway',
        'FI' => 'Finland',
        'NL' => 'Netherlands',
        'BE' => 'Belgium',
        'CH' => 'Switzerland',
        'AT' => 'Austria',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'GR' => 'Greece',
        'CZ' => 'Czech Republic',
        'HU' => 'Hungary',
        'TR' => 'Turkey',
        'TH' => 'Thailand',
        'MY' => 'Malaysia',
        'pid' => 'Indonesia',
        'PH' => 'Philippines',
        'NZ' => 'New Zealand',
        'DK' => 'Denmark',
        'IE' => 'Ireland',
        // Add more country codes and names as needed
    ];
    return isset($countries[$code_pays]) ? $countries[$code_pays] : $code_pays;

}

// Fetch data from the `agence` table
$query = "SELECT pid, nom_agence, code_pays, ville, tel, email, created_on, credit, etat FROM agence";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Check if the query was successful
if ($result && $result->num_rows > 0) {
    // Loop through the data and output it as table rows
    while ($row = $result->fetch_assoc()) {
        $pid = $row['pid'];
        $nom_agence = htmlspecialchars($row['nom_agence']);
        $code_pays = getCountryName($row['code_pays']);
        $ville = htmlspecialchars($row['ville']);
        $tel = htmlspecialchars($row['tel']);
        $email = htmlspecialchars($row['email']);
        $created_on = htmlspecialchars($row['created_on']);
        $credit = htmlspecialchars($row['credit']);
        $etat = htmlspecialchars($row['etat']);

        // Define button text and action based on 'etat' value
        if ($etat == 0) {
            $etatText = 'Désactivé'; // If etat is 0, display 'Désactivé'
            $btnClass = 'btn btn-success';
            $btnText = 'Activate';
            $btnAction = 'activate';
        } else {
            $etatText = 'Activé'; // If etat is 1, display 'Activé'
            $btnClass = 'btn btn-danger';
            $btnText = 'Deactivate';
            $btnAction = 'deactivate';
        }

        echo "<tr>
            <td>{$nom_agence}</td>
            <td>{$code_pays}</td>
            <td>{$ville}</td>
            <td>{$tel}</td>
            <td>{$email}</td>
            <td>{$created_on}</td>
            <td><button class='btn btn-danger' onclick=\"location.href='archive_list.php?pid={$pid}'\">Archives</button></td>
        </tr>";
    }
} else {
    // No data found
    echo "<tr><td colspan='11'>No records found</td></tr>";
}



?>

<script>
    // Add click event listener for the "Details" button
    $(document).on('click', '.details-btn', function() {
        var pid = $(this).data('pid');  // Get the agency pid
        window.location.href = 'agence_details.php?pid=' + pid;  // Redirect to the details page with the pid
    });
</script>

