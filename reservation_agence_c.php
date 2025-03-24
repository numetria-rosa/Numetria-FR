<?php
// Include database connection
include 'files/db_connection.php';


$agenceFilter = isset($_GET['agence_filter']) ? $_GET['agence_filter'] : '';


// Get date range from GET parameters
$startDate = $_GET['startDate'] ?? '';
$endDate   = $_GET['endDate'] ?? '';
$nomAgence = $_GET['nom_agence'] ?? '';


$nomAgence = isset($_GET['nom_agence']) && !empty($_GET['nom_agence']) ? $_GET['nom_agence'] : null;
var_dump($_GET); // This will display the entire query string array

// SQL query to retrieve the list of agencies
$sql = "SELECT pid, nom_agence FROM agence";
$agenceResult = $conn->query($sql);

if ($agenceResult->num_rows == 0) {
    die("Error: No agencies found!");
}
// Updated SQL query for all agencies
$sql = "
SELECT DISTINCT
    booking.bookingreference AS reference_reservation,
    agence.prenom AS agent,
    agence.nom_agence AS agence,
    booking.bookingservicetype,
    CASE
        WHEN booking.bookingservicetype = 'flights' THEN
            CONCAT(
                CONVERT(flightitinerary.DepartureLocation USING utf8mb4), ' → ', 
                CONVERT(flightitinerary.ArrivalLocation USING utf8mb4)
            )
        WHEN booking.bookingservicetype = 'hotelbedsactivity' THEN
            CONVERT(activitydetail.activityName USING utf8mb4)
        WHEN booking.bookingservicetype = 'mystifly' THEN
            CONCAT(
                CONVERT(
                    (SELECT departure_airport_location_code FROM mystifly_reservation_item
                     WHERE airline_pnr = booking.id ORDER BY id ASC LIMIT 1) 
                    USING utf8mb4),
                ' → ',
                CONVERT(
                    (SELECT arrival_airport_location_code FROM mystifly_reservation_item
                     WHERE airline_pnr = booking.id ORDER BY id ASC LIMIT 1) 
                    USING utf8mb4),
                ' / ',
                CONVERT(
                    (SELECT departure_airport_location_code FROM mystifly_reservation_item
                     WHERE airline_pnr = booking.id ORDER BY id DESC LIMIT 1) 
                    USING utf8mb4),
                ' → ',
                CONVERT(
                    (SELECT arrival_airport_location_code FROM mystifly_reservation_item
                     WHERE airline_pnr = booking.id ORDER BY id DESC LIMIT 1) 
                    USING utf8mb4)
            )
        ELSE
            CONVERT(booking.hotelname USING utf8mb4)
    END AS destination,
    CONCAT(
        CONVERT(booking.leaderfirstname USING utf8mb4), ' ', 
        CONVERT(booking.leaderlastname USING utf8mb4)
    ) AS client,
    booking.starttime AS date_reservation,
    booking.grossamount AS prix,
    CASE
        WHEN booking.currentstatus = 'cancelled' THEN 'Annulée'
        WHEN booking.currentstatus = 'refused' THEN 'Annulée'
        WHEN booking.currentstatus = 'requested' THEN 'En attente'
        WHEN booking.currentstatus = 'vouchered' THEN 'Confirmée'
        ELSE booking.currentstatus
    END AS etat,
    booking.currencycode AS currency
FROM
    booking
INNER JOIN
    agence ON booking.pidagence = agence.pid
LEFT JOIN
    flightitinerary ON booking.pid = flightitinerary.BookingId
LEFT JOIN
    activitydetail ON booking.pid = activitydetail.pidBooking

       
";




// Add date range filter if provided
// Add date range filter if provided

if ($startDate && $endDate) {
    $sql .= " AND booking.starttime BETWEEN '".$startDate." 00:00:00' AND '".$endDate." 23:59:59'";
}
if ($nomAgence) {
    $sql .= " AND agence.nom_agence = '".$nomAgence."'";
}
if ($agenceFilter) {
    $sql .= " AND agence.pid = '".$agenceFilter."'";
}
$result = $conn->query($sql);

// Debugging output
if (!empty($startDate) && !empty($endDate) || !empty($nomAgence)) {
    echo "SQL Query: " . $sql;
    echo "Start Date: " . $startDate . " End Date: " . $endDate;
    echo " Agency: " . ($nomAgence ? $nomAgence : "No agency selected");
    echo " PID Agence : " . ($agenceFilter ? $agenceFilter : "No pid selected");

}


// Check if the request is for AJAX filtering
if (isset($_GET['startDate']) && isset($_GET['endDate'])&& isset($_GET['nom_agence']) ) {
    if ($result && $result->num_rows > 0) {
        $rowCount = $result->num_rows;
        $currentRow = 0;
        while ($row = $result->fetch_assoc()) {
            // Get the booking service type
            $serviceType = !empty($row['bookingservicetype']) ? strtolower(trim(htmlspecialchars($row['bookingservicetype']))) : "default";

            // Define colors for each booking service type
            $colors = [
                'hotels' => 'rgb(255, 165, 0)', // Orange
                'hotelsdirect' => 'rgb(0, 128, 0)', // Green
                'hotelsbeds' => 'rgb(0, 0, 255)', // Blue
                'elmouradihotels' => 'rgb(255, 0, 0)', // Red
                'hotelbedsactivity' => 'rgb(128, 0, 128)', // Purple
                'flights' => 'rgb(84, 4, 4)', // Dark Red
                'medinahotels' => 'rgb(255, 99, 71)', // Tomato
                'itropikahotels' => 'rgb(34, 139, 34)', // Forest Green
                'carthagehotels' => 'rgb(255, 69, 0)', // Orange Red
                'TTShotels' => 'rgb(75, 0, 130)', // Indigo
                'SUPPLIER_5hotels' => 'rgb(255, 20, 147)', // Deep Pink
                'SUPPLIER_7hotels' => 'rgb(255, 105, 180)', // Hot Pink
                'SUPPLIER_6hotels' => 'rgb(69, 120, 215)', // Cornflower Blue
                'tunisiabedshotels' => 'rgb(210, 105, 30)', // Chocolate
                'sultanhotels' => 'rgb(139, 69, 19)', // SaddleBrown
                'badirahotels' => 'rgb(208, 163, 158)', // MistyRose
                'lightresahotels' => 'rgb(240, 230, 140)', // Khaki
                'Mediterraneehotels' => 'rgb(240, 128, 128)', // Light Coral
                'ratehawk' => 'rgb(65, 105, 225)', // Royal Blue
                'mystifly' => 'rgb(225, 107, 125)', // Light Pink
                'soleilABhotels' => 'rgb(29, 181, 181)', // Cyan
                'caravelhotels' => 'rgb(255, 215, 0)', // Gold
                'soleilBVhotels' => 'rgb(173, 216, 230)', // Light Blue
                'kantahotel' => 'rgb(255, 140, 0)', // Dark Orange
                'Sentidohotels' => 'rgb(238, 130, 238)', // Violet
            ];

            // Set the background color for the row based on the booking service type
            $rowColor = isset($colors[$serviceType]) ? $colors[$serviceType] : 'transparent';

            // Apply color styles for all columns in the row
            $cellStyle = $rowColor ? 'background-color: ' . $rowColor . '; color: white;' : '';
            
            // Set row color based on booking status `etat`
            $statusStyle = '';
            if ($row['etat'] == 'Annulée') {
                $statusStyle = 'color: red; font-weight: bold;';
            } elseif ($row['etat'] == 'En attente') {
                $statusStyle = 'color: orange; font-weight: bold;';
            } elseif ($row['etat'] == 'Confirmée') {
                $statusStyle = 'color: green; font-weight: bold;';
            }

            echo '<tr>';
            echo '<td  style="' . $cellStyle . '">' . htmlspecialchars($row['reference_reservation']) . '</td>';
            echo '<td>' . htmlspecialchars($row['agence']) . '</td>';
            echo '<td>' . htmlspecialchars($row['agent']) . '</td>';
            echo '<td>' . htmlspecialchars($row['destination']) . '</td>';
            echo '<td>' . htmlspecialchars($row['client']) . '</td>';
            echo '<td>' . htmlspecialchars($row['date_reservation']) . '</td>';
            echo '<td>' . htmlspecialchars($row['prix']) . ' EUR</td>';
            echo '<td style="' . $statusStyle . '">' . htmlspecialchars($row['etat']) . '</td>';
            echo '</tr>';

            $currentRow++;
        }
    } else {
        echo "No results found for the selected date range.";  // Debugging message
        echo '<tr><td colspan="8">Aucune donnée trouvée</td></tr>'; // Adjusted the colspan to 8 after removing 3 columns
    }
    exit;
}


?>





<!DOCTYPE html>
<html lang="en">
  
<!-- Mirrored from thetheme.io/theadmin/samples/invoicer/agence.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 13 Jan 2025 09:38:52 GMT -->
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="TheAdmin - Responsive admin and web application ui kit">
  <meta name="keywords" content="admin, dashboard, web app, sass, ui kit, ui framework, bootstrap">

  <title>Comptabilté&mdash; DMCBOOKING</title>
<!-- Include jQuery (required for daterangepicker) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<!-- Include DateRangePicker CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.0.5/daterangepicker.css" />
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.0.5/daterangepicker.min.js"></script>

<!-- Include DateRangePicker JS -->
  <!-- Styles -->
  <link href="assets/css/core.min.css" rel="stylesheet">
  <link href="assets/css/core.min1.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet">
  <link href="assets/css/app.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">


  <!-- Icons Css -->
  <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

 
<!-- DataTables -->
<link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />


 <!-- Responsive datatable examples -->
 <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" /> 
       
 
  <!-- Bootstrap Css -->
  <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
  <!-- Icons Css -->
  <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
  <!-- App Css-->
  <link href="assets/css/app.min1.css" id="app-style" rel="stylesheet" type="text/css" />

  <!-- Favicons -->
  <link rel="shortcut icon" href="assets/img/favicon.ico">
  <link rel="apple-touch-icon" href="assets/img/apple-touch-user.png">
  <link rel="icon" href="assets/img/favuser.png">

  <!-- DataTables CDN (Optional, remove if you're using the above local files) -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css"> 
  <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css" />


</head>


  <body>


    <!-- Preloader
    <div class="preloader">
      <div class="spinner-dots">
        <span class="dot1"></span>
        <span class="dot2"></span>
        <span class="dot3"></span>
      </div>
    </div>-->


    <!-- Sidebar -->
    <aside class="sidebar sidebar-expand-lg sidebar-light sidebar-sm sidebar-color-info">

      <header class="sidebar-header bg-info">
        <span class="logo" style="margin-bottom: 11px;">
            <a href=""><img src="assets/img/numetria3.png                        " alt="logo"></a>
        </span>
        <span class="sidebar-toggle-fold"></span>
      </header>

      <nav class="sidebar-navigation">
        <ul class="menu menu-sm menu-bordery">

                    <li class="menu-item">
            <a class="menu-link" href="dashboard.php">
              <span class="icon ti-home"></span>
              <span class="title">Dashboard</span>
            </a>
          </li>

          <li class="menu-item active">
            <a class="menu-link" href="agence.php">
              <span class="icon ti-user"></span>
              <span class="title">Agences</span>
            </a>
          </li>

          <li class="menu-item">
            <a class="menu-link" href="reservations.php">
              <span class="icon ti-briefcase"></span>
              <span class="title">Reservations</span>
            </a>
          </li>
          <li class="menu-item active">
            <a class="menu-link" href="etat.php">
              <span class="icon ti-agenda"></span>
              <span class="title">Etat</span>
            </a>
          </li>
        </ul>
      </nav>

    </aside>
    <!-- END Sidebar -->



    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
          <a class="logo d-lg-none" href="index.php"><img src="assets/img/                    " alt="logo"></a>

        <ul class="topbar-btns">


        </ul>
      </div>

      <div class="topbar-right">

        <ul class="topbar-btns">
          <li class="dropdown">
            <span class="topbar-btn" data-toggle="dropdown"><img class="avatar" src="assets/img/user.png" alt="..."></span>
            <div class="dropdown-menu dropdown-menu-right">

<a class="dropdown-item" href="index.php"><i class="ti-power-off"></i> Se déconnecter</a>
            </div>
          </li>
        </ul>

        </form>

      </div>
    </header>
    <!-- END Topbar -->



    <!-- Main container -->
    <main class="main-container">


      <div class="main-content">

        <div class="media-list media-list-divided media-list-hover" data-provide="selectall">
        </div>

        <div class="card-body">
    <h4 class="card-title">Réservations</h4>
    <!-- Booking Service Type Guide -->
    <div class="color-guide">
        <div class="color-box" style="background-color: rgb(255, 165, 0);">Hotels</div>
        <div class="color-box" style="background-color: rgb(0, 128, 0);">Hotels Direct</div>
        <div class="color-box" style="background-color: rgb(0, 0, 255);">Hotelsbeds</div>
        <div class="color-box" style="background-color: rgb(255, 0, 0);">El Mouradi Hotels</div>
        <div class="color-box" style="background-color: rgb(128, 0, 128);color:white;">Hotelbeds Activity</div>
        <div class="color-box" style="background-color: rgb(84, 4, 4); color:white;">Flights</div>
        <div class="color-box" style="background-color: rgb(255, 99, 71);">Medina Hotels</div>
        <div class="color-box" style="background-color: rgb(144, 181, 7);">Itropika Hotels</div>
        <div class="color-box" style="background-color: rgb(255, 69, 0);">Carthage Hotels</div>
        <div class="color-box" style="background-color: rgb(75, 0, 130);color:white;">TTS Hotels</div>
        <div class="color-box" style="background-color: rgb(255, 20, 147);">SUPPLIER 5 Hotels</div>
        <div class="color-box" style="background-color: rgb(255, 105, 180);">SUPPLIER 7 Hotels</div>
        <div class="color-box" style="background-color: rgb(100, 149, 237);">SUPPLIER 6 Hotels</div>
        <div class="color-box" style="background-color: rgb(210, 105, 30);">Tunisia Beds Hotels</div>
        <div class="color-box" style="background-color: rgb(139, 69, 19);">Sultan Hotels</div>
        <div class="color-box" style="background-color: rgb(208, 163, 158);">Badira Hotels</div>
        <div class="color-box" style="background-color: rgb(240, 230, 140);">Lightresa Hotels</div>
        <div class="color-box" style="background-color: rgb(240, 128, 128);">Mediterranee Hotels</div>
        <div class="color-box" style="background-color: rgb(65, 105, 225);">Ratehawk</div>
        <div class="color-box" style="background-color: rgb(255, 182, 193);">Mystifly</div>
        <div class="color-box" style="background-color: rgb(29, 181, 181);">Soleil AB Hotels</div>
        <div class="color-box" style="background-color: rgb(255, 215, 0);">Caravel Hotels</div>
        <div class="color-box" style="background-color: rgb(173, 216, 230);">Soleil BV Hotels</div>
        <div class="color-box" style="background-color: rgb(255, 140, 0);">Kanta Hotel</div>
        <div class="color-box" style="background-color: rgb(238, 130, 238);">Sentido Hotels</div>
    </div>
<hr>
<!-- NEW Agency filter-->
<!-- NEW Agency filter-->
<form id="agencyFilterForm" method="GET" action="reservation_agence_c.php"> 
    <label for="agence_filter">Agences :</label>
    <select name="agence_filter" id="agence_filter">
        <option value="">--Toutes les agences--</option>
        <?php while ($agence = $agenceResult->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($agence['pid']) ?>" 
                    data-name="<?= htmlspecialchars($agence['nom_agence']) ?>" 
                    <?= $agence['pid'] == $agenceFilter ? 'selected' : '' ?>>
                <?= htmlspecialchars($agence['nom_agence']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>


<!-- JavaScript to log the selected agency name -->






<!-- Modern CSS for styling -->
<style>

</style>
    
<!-- Date Range Picker -->
<div id="daterange-container" class="mb-3">
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text" id="calendar-icon">
                <i class="fa fa-calendar"></i>
            </span>
        </div>
        <input type="text" id="reservation-daterange" class="form-control daterange-input" placeholder="Sélectionner une période">
    </div>
</div>
<hr>
<!-- Booking Table -->
<table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" 
    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
    <thead>
        <tr>
            <th>Référence Réservation</th>
            <th>Agence</th>
            <th>Agent</th>
            <th>Destination</th>
            <th>Client</th>
            <th>Date de Réservation</th>
            <th>Prix</th>
            <th>État</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    // Get the booking service type
                    $serviceType = !empty($row['bookingservicetype']) ? strtolower(trim(htmlspecialchars($row['bookingservicetype']))) : "default";

                    // Define colors for each booking service type
                    $colors = [
                        'hotels' => 'rgb(255, 165, 0)', // Orange
                        'hotelsdirect' => 'rgb(0, 128, 0)', // Green
                        'hotelsbeds' => 'rgb(0, 0, 255)', // Blue
                        'elmouradihotels' => 'rgb(255, 0, 0)', // Red
                        'hotelbedsactivity' => 'rgb(128, 0, 128)', // Purple
                        'flights' => 'rgb(84, 4, 4)', // Dark Red
                        'medinahotels' => 'rgb(255, 99, 71)', // Tomato
                        'itropikahotels' => 'rgb(34, 139, 34)', // Forest Green
                        'carthagehotels' => 'rgb(255, 69, 0)', // Orange Red
                        'TTShotels' => 'rgb(75, 0, 130)', // Indigo
                        'SUPPLIER_5hotels' => 'rgb(255, 20, 147)', // Deep Pink
                        'SUPPLIER_7hotels' => 'rgb(255, 105, 180)', // Hot Pink
                        'SUPPLIER_6hotels' => 'rgb(69, 120, 215)', // Cornflower Blue
                        'tunisiabedshotels' => 'rgb(210, 105, 30)', // Chocolate
                        'sultanhotels' => 'rgb(139, 69, 19)', // SaddleBrown
                        'badirahotels' => 'rgb(208, 163, 158)', // MistyRose
                        'lightresahotels' => 'rgb(240, 230, 140)', // Khaki
                        'Mediterraneehotels' => 'rgb(240, 128, 128)', // Light Coral
                        'ratehawk' => 'rgb(65, 105, 225)', // Royal Blue
                        'mystifly' => 'rgb(225, 107, 125)', // Light Pink
                        'soleilABhotels' => 'rgb(29, 181, 181)', // Cyan
                        'caravelhotels' => 'rgb(255, 215, 0)', // Gold
                        'soleilBVhotels' => 'rgb(173, 216, 230)', // Light Blue
                        'kantahotel' => 'rgb(255, 140, 0)', // Dark Orange
                        'Sentidohotels' => 'rgb(238, 130, 238)', // Violet
                    ];

                    // Set the background color for the row based on the booking service type
                    $rowColor = isset($colors[$serviceType]) ? $colors[$serviceType] : 'transparent';

                    // Apply color styles for all columns in the row
                    $cellStyle = $rowColor ? 'background-color: ' . $rowColor . '; color: white;' : '';
                ?>

                <tr>
                    <td style="<?= $cellStyle ?>"><?= htmlspecialchars($row['reference_reservation']); ?></td>
                    <td ><?= htmlspecialchars($row['agence']); ?></td>
                    <td ><?= htmlspecialchars($row['agent']); ?></td>
                    <td ><?= htmlspecialchars($row['destination']); ?></td>
                    <td ><?= htmlspecialchars($row['client']); ?></td>
                    <td ><?= htmlspecialchars($row['date_reservation']); ?></td>
                    <td ><?= htmlspecialchars($row['prix']); ?> <?= 'EUR'; ?></td>
                    <td 
                        style="
                            <?php 
                                // Set background color based on the `etat`
                                if ($row['etat'] == 'Annulée') {
                                    echo 'color: red;font-weight: bold;';
                                } elseif ($row['etat'] == 'On attente') {
                                    echo 'color: orange;font-weight: bold;';
                                } elseif ($row['etat'] == 'Confirmée') {
                                    echo ' color: green;font-weight: bold;';
                                }
                            ?>
                        "
                    >
                        <?= htmlspecialchars($row['etat']); ?>
                    </td>

                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">Aucune donnée trouvée</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>



<style>
.color-guide {
  display: flex;
  flex-wrap: wrap; /* Allow the boxes to wrap to the next line */
  justify-content: flex-start; /* Align the color boxes to the left */
  gap: 10px; /* Space between the boxes */
  margin-bottom: 15px; /* Space between the color guide and the table */
}

.color-box {
  padding: 5px 10px; /* Adjust padding to give some space inside the boxes */
  color: black;
  font-weight: bold;
  border-radius: 5px;
  text-align: center;
  font-size: 12px; /* Smaller font size */
  display: inline-block; /* Allow the box to adjust its width based on content */
  box-sizing: border-box; /* Ensure padding is included in the box size */
  white-space: nowrap; /* Prevent text from wrapping */
}

</style>
<!-- Add JS for Export Functionality -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>

<script>
$(document).ready(function () {
    // Global variables to store the selected filters
    var global_startDate = "";
    var global_endDate = "";
    var global_nomAgence = "";

    // Initialize the date range picker
    $('#reservation-daterange').daterangepicker({
        opens: 'left',
        locale: {
            format: 'DD/MM/YYYY',
        },
    }, function (start, end, label) {
        console.log("Selected range: " + start.format('DD/MM/YYYY') + ' to ' + end.format('DD/MM/YYYY'));
        
        // Update global date values
        global_startDate = start.format('YYYY-MM-DD');
        global_endDate = end.format('YYYY-MM-DD');
        
        // Trigger AJAX request with both filters (if agency already selected)
        filterReservations(global_startDate, global_endDate, global_nomAgence);
    });

    // Initialize DataTable once
    var tableButtons = $('#datatable-buttons').DataTable();

    function filterReservations(startDate, endDate, nomAgence, agenceFilter) {
        // Prepare data object with the filters that exist
        let requestData = {};
        if (startDate && endDate) {
            requestData.startDate = startDate;
            requestData.endDate = endDate;
        }
        if (nomAgence) {
            requestData.nom_agence = nomAgence;
        }
        if (agenceFilter) {
            requestData.agence_filter = agenceFilter;
        }

        $.ajax({
            url: 'reservation_agence_c.php',
            type: 'GET',
            data: requestData, // Data now includes both the date, agency, and the agence.pid filter if available
            success: function (response) {
                console.log("AJAX Response: ", response);
                $('table#datatable-buttons tbody').html(response); // Update table body

                // Clear and add new rows to the existing DataTable instance
                tableButtons.clear(); // Clear old data
                tableButtons.rows.add($('#datatable-buttons tbody tr')); // Add new rows
                tableButtons.draw(); // Redraw table
            },
            error: function (xhr, status, error) {
                console.error('Error filtering reservations: ' + error);
            },
        });
    }

    // When the agency select option is changed
    $('#agence_filter').on('change', function () {
        // Get the selected <option>
        let selectedOption = $(this).find('option:selected');
        let selectedPid = selectedOption.val(); // This will give you the pid of the selected agency
        global_nomAgence = selectedOption.data('name'); // Update global agency value

        console.log("Selected Agency Filter (PID):", selectedPid);
        console.log("Selected Agency Name:", global_nomAgence);
        console.log("Selected Start Date:", global_startDate);
        console.log("Selected End Date:", global_endDate);

        // Trigger AJAX request with both filters (agency, start date, and end date)
        filterReservations(global_startDate, global_endDate, global_nomAgence, selectedPid);
    });

    // Export filtered data to Excel (your existing code)
    $('#export-filtered').click(function () {
        var rows = [];
        $('#datatable-buttons tbody tr').each(function () {
            var row = [];
            $(this).find('td').each(function () {
                row.push($(this).text().trim());
            });
            rows.push(row);
        });

        // Add headers
        var headers = [
            'Référence Réservation',
            'Agent',
            'Agence',
            'Destination',
            'Client',
            'Date de Réservation',
            'Prix',
            'État',
        ];
        rows.unshift(headers); // Add headers at the top

        // Convert to worksheet
        var ws = XLSX.utils.aoa_to_sheet(rows);

        // Apply styling (your existing styling code)
        var range = XLSX.utils.decode_range(ws['!ref']);
        for (var row = range.s.r; row <= range.e.r; row++) {
            for (var col = range.s.c; col <= range.e.c; col++) {
                var cell = ws[XLSX.utils.encode_cell({ r: row, c: col })];
                if (cell) {
                    if (!cell.s) cell.s = {};
                    cell.s.font = {
                        name: 'Calibri',
                        sz: 11,
                        bold: row === 0, // Header bold
                    };
                    if (row === 0) {
                        cell.s.fill = {
                            fgColor: { rgb: 'D9E1F2' }, // Light blue header
                        };
                    }
                }
            }
        }

        // Adjust column widths (your existing code)
        var colWidths = [];
        for (var rowIndex = range.s.r; rowIndex <= range.e.r; rowIndex++) {
            for (var colIndex = range.s.c; colIndex <= range.e.c; colIndex++) {
                var cell = ws[XLSX.utils.encode_cell({ r: rowIndex, c: colIndex })];
                if (cell && cell.v) {
                    var cellValue = cell.v.toString();
                    colWidths[colIndex] = Math.max(colWidths[colIndex] || 10, cellValue.length);
                }
            }
        }
        ws['!cols'] = colWidths.map(function (width) {
            return { width: width + 2 }; // Padding
        });

        // Create workbook and export
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Filtered Data');
        XLSX.writeFile(wb, 'Filtered_Data.xlsx');
    });
});

</script>








<style>
    .daterange-input {
        max-width: 300px; /* Adjust the width as needed */
        margin: 0 auto; /* Optional: Center it horizontally */
        background-color: white; /* Dark grey background */
        color: #949292; /* White text color */
    }

    /* Icon styling */
    #calendar-icon {
        background-color: White; /* Dark grey background for the icon container */
        color:  #949292; /* White icon */
    }

    /* Adjust the icon inside the input group */
    .input-group-text i {
        font-size: 16px; /* Adjust the icon size */
    }
</style>
























   <!-- JAVASCRIPT -->
   <script src="assets/libs/jquery/jquery.min.js"></script>
   <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
   <script src="assets/libs/metismenu/metisMenu.min.js"></script>
   <script src="assets/libs/simplebar/simplebar.min.js"></script>
   <script src="assets/libs/node-waves/waves.min.js"></script>
   <script src="assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>
<!-- Required datatable js -->
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- Buttons examples -->
<script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
<script src="assets/libs/jszip/jszip.min.js"></script>
<script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>
<!-- Responsive examples -->
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<!-- Datatable init js -->
<script src="assets/js/pages/datatables.init.js"></script>

<!-- App js -->
<script src="assets/js/app.js"></script>
<!-- DataTables Custom Scripts (Local JS) -->
<script src="assets/js/core.min.js"></script>
<script src="assets/js/app.min.js"></script>
<script src="assets/js/script.js"></script>

<!-- Additional Libraries -->
<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/metismenu/metisMenu.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>
<script src="assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>

<!-- Required datatable js -->
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<!-- Buttons examples -->
<script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
<script src="assets/libs/jszip/jszip.min.js"></script>
<script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

<!-- Responsive examples -->
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<script src="assets/vendor/bootstrap-select/js/bootstrap-select.min.js
"></script>

<!-- Datatable init js -->
<script src="assets/js/pages/datatables.init.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>

  </body>
</html>
