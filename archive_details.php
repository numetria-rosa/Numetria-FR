<?php
// Include database connection
include 'files/db_connection.php';

// Get the agency ID from the URL
$agenceId = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
if ($agenceId == 0) {
    die("Invalid Agency ID.");
}

// SQL query to fetch the agency name (nom_agence) from the agence table
$agenceQuery = "
    SELECT nom_agence
    FROM agence
    WHERE pid = $agenceId
";
$agenceResult = $conn->query($agenceQuery);
$nomAgence = '';
if ($agenceResult && $agenceRow = $agenceResult->fetch_assoc()) {
    $nomAgence = $agenceRow['nom_agence'];
}

// Fetch reglement details for the agency
$query_reglement = "SELECT pid, reg_on, reg, currency, comment, etat_reg ,etat_payment,reg_ref  FROM reglement WHERE agence = $agenceId";
$result_reglement = $conn->query($query_reglement);
$transactions = [];
$total_credit = 0;
if ($result_reglement && $result_reglement->num_rows > 0) {
    while ($row = $result_reglement->fetch_assoc()) {
        $transactions[] = [
            'pid' => htmlspecialchars($row['pid']),
            'date' => htmlspecialchars($row['reg_on']),
            'amount' => htmlspecialchars($row['reg']) . " " . htmlspecialchars($row['currency']),
            'comment' => htmlspecialchars($row['comment']),
            'etat_reg' => htmlspecialchars($row['etat_reg']),
            'etat_payment' => htmlspecialchars($row['etat_payment']),
            'reg_ref' => htmlspecialchars($row['reg_ref']),


        ];
        if (is_numeric($row['reg'])) {
            $total_credit += (float)$row['reg'];
        }
            }
}

$payed_on = isset($_GET['payed_on']) ? $_GET['payed_on'] : '';

if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $payed_on)) {
    die("Invalid date format.");
}

$sql = "
    SELECT 
        archive.ref_booking AS reference_reservation,
        archive.res_pid AS booking_pid,
        archive.agency_name AS agence,
        archive.agency_pid AS agence_pid,
        booking.bookingservicetype,
        CASE
            WHEN booking.bookingservicetype = 'flights' THEN
                CONCAT(CONVERT(flightitinerary.DepartureLocation USING utf8mb4), ' → ', CONVERT(flightitinerary.ArrivalLocation USING utf8mb4))
            WHEN booking.bookingservicetype = 'hotelbedsactivity' THEN
                CONVERT(activitydetail.activityName USING utf8mb4)
            WHEN booking.bookingservicetype = 'mystifly' THEN
                CONCAT(
                    (SELECT CONVERT(departure_airport_location_code USING utf8mb4) FROM mystifly_reservation_item
                     WHERE airline_pnr = booking.id ORDER BY id ASC LIMIT 1),
                    ' → ',
                    (SELECT CONVERT(arrival_airport_location_code USING utf8mb4) FROM mystifly_reservation_item
                     WHERE airline_pnr = booking.id ORDER BY id ASC LIMIT 1),
                    ' / ',
                    (SELECT CONVERT(departure_airport_location_code USING utf8mb4) FROM mystifly_reservation_item
                     WHERE airline_pnr = booking.id ORDER BY id DESC LIMIT 1),
                    ' → ',
                    (SELECT CONVERT(arrival_airport_location_code USING utf8mb4) FROM mystifly_reservation_item
                     WHERE airline_pnr = booking.id ORDER BY id DESC LIMIT 1)
                )
            ELSE
                CONVERT(booking.hotelname USING utf8mb4)
        END AS destination,
        archive.date_res AS date_reservation,
        booking.grossamount AS prix,
        CASE
            WHEN booking.currentstatus = 'cancelled' THEN 'Annulée'
            WHEN booking.currentstatus = 'refused' THEN 'Annulée'
            WHEN booking.currentstatus = 'requested' THEN 'En attente'
            WHEN booking.currentstatus = 'vouchered' THEN 'Confirmée'
            ELSE booking.currentstatus
        END AS etat,
        booking.currencycode AS currency,
        CASE 
            WHEN archive.etat_res = 1 THEN 'Payée'
            ELSE 'Non Payée'
        END AS etat_reservation,
        archive.payed_on AS date_payment,
        CONCAT(archive.regRef, ' | ', COALESCE(reglement.reg, 'N/A')) AS reglement
    FROM
        archive
    INNER JOIN
        booking ON archive.res_pid = booking.pid
    LEFT JOIN
        flightitinerary ON archive.res_pid = flightitinerary.BookingId
    LEFT JOIN
        activitydetail ON booking.pid = activitydetail.pidBooking
    LEFT JOIN
        mystifly_reservation_item ON booking.id = mystifly_reservation_item.airline_pnr
    LEFT JOIN
        reglement ON archive.regRef = reglement.reg_ref
    WHERE archive.agency_pid = $agenceId 
      AND archive.payed_on = '$payed_on'  
    ORDER BY
        booking.starttime DESC
";








$result = $conn->query($sql);
if (!empty($startDate) && !empty($endDate)) {
   
}

// Check if the request is for AJAX filtering
if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
  if ($result && $result->num_rows > 0) {
      $rowCount = $result->num_rows;
      $currentRow = 0;
      while ($row = $result->fetch_assoc()) {
          // Preparing the data for the booking row
        
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
              'itropikahotels' => 'rgb(144, 181, 7)', // Forest Green
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
          // Get the color for this service type, or default to transparent
          $rowColor = isset($colors[$serviceType]) ? $colors[$serviceType] : 'transparent';

          // Debug: Show the selected color for the service type (optional)
          // echo 'Service Type: ' . htmlspecialchars($serviceType) . ' Color: ' . $rowColor . '<br>';

          // Apply color styles for all columns in the row
          $cellStyle = $rowColor ? 'background-color: ' . $rowColor . '; color: white;' : '';
          $statusStyle = '';
          if ($row['etat'] == 'Annulée') {
              $statusStyle = 'color: red; font-weight: bold;';
          } elseif ($row['etat'] == 'En attente') {
              $statusStyle = 'color: orange; font-weight: bold;';
          } elseif ($row['etat'] == 'Confirmée') {
              $statusStyle = 'color: green; font-weight: bold;';
          }
          echo '<tr>';
          echo '<td id="agencePid" style="display: none;">' . htmlspecialchars($row['agence_pid']) . '</td>';
          echo '<td id="bookingPid" style="display: none;">' . htmlspecialchars($row['booking_pid']) . '</td>';
          echo '<td id="refRes" style="' . $cellStyle . '">' . htmlspecialchars($row['reference_reservation']) . '</td>';
          echo '<td>' . htmlspecialchars($row['agence']) . '</td>';
          echo '<td id="dateRes">' . htmlspecialchars($row['date_reservation']) . '</td>';
          echo '<td id="price">' . number_format($row['prix'], 2) . ' ' . htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR') . '</td>';
          echo '<td style="' . $statusStyle . '">' . htmlspecialchars($row['etat']) . '</td>';
          
          // New fields added:
          echo '<td>' . htmlspecialchars($row['etat_reservation']) . '</td>'; // Displays "Payée" if etat_res = 1
          echo '<td>' . htmlspecialchars($row['date_payment']) . '</td>'; // Payment date
          echo '<td>' . htmlspecialchars($row['reglement']) . '</td>'; // Payment method
          
          echo '</tr>';
          

     
          $currentRow++;
      }
  } else {
      echo '<tr><td colspan="10">Aucune donnée trouvée</td></tr>';
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

  <title>Comptabilté&mdash; </title>
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
  <script>
        document.addEventListener("contextmenu", (event) => event.preventDefault());
        document.onkeydown = function (e) {
            if (e.keyCode == 123) return false; // Prevent F12 (DevTools)
            if (e.ctrlKey && e.shiftKey && e.keyCode == 73) return false; // Prevent Ctrl+Shift+I
            if (e.ctrlKey && e.keyCode == 85) return false; // Prevent Ctrl+U (View Source)
        };
    </script>

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

          <li class="menu-item ">
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
          <li class="menu-item ">
            <a class="menu-link" href="etat.php">
              <span class="icon ti-agenda"></span>
              <span class="title">Etat</span>
            </a>
          </li>
          <li class="menu-item active">
            <a class="menu-link" href="archive.php">
              <span class="icon ti-cloud-down"></span>
              <span class="title">Archive</span>
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
        <h4 class="card-title" style="color:#b20000;font-weight: bold;">ARCHIVE | <?= htmlspecialchars($nomAgence); ?></h4>
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



    <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
    <thead>
        <tr>
            <th>Référence Réservation | PNR </th>
            <th>Agence</th>
            <th>Date de Réservation</th>
            <th>Prix</th>
            <th>État</th>
            <th>Date de Payment</th>
            <th>Reference Règlement | Montant </th>
        </tr>
    </thead>
    <tbody>
    <?php
    $result = $conn->query($sql);
    if (!$result) {
        die("SQL Error: " . $conn->error);
    }
    if ($result && $result->num_rows > 0): ?>
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
                'itropikahotels' => 'rgb(144, 181, 7)', // Forest Green
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
            // Get the color for this service type, or default to transparent
            $rowColor = isset($colors[$serviceType]) ? $colors[$serviceType] : 'transparent';

            // Debug: Show the selected color for the service type (optional)
            // echo 'Service Type: ' . htmlspecialchars($serviceType) . ' Color: ' . $rowColor . '<br>';

            // Apply color styles for all columns in the row
            $cellStyle = $rowColor ? 'background-color: ' . $rowColor . '; color: white;' : '';
        ?>

        <tr>
            <td style="<?= $cellStyle ?>"><?= htmlspecialchars($row['reference_reservation']); ?></td>
            <td style="font-weight: bold;"><?= htmlspecialchars($row['agence']); ?></td>
            <td style="font-weight: bold;"><?= htmlspecialchars($row['date_reservation']); ?></td>
            <td style=" color: orange; font-weight: bold;"><?= number_format($row['prix'], 2) . ' ' . htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR'); ?></td>
            <td style="color: green;font-weight: bold;"><?= htmlspecialchars($row['etat_reservation']); ?></td>
            <td style="font-weight: bold;"><?= htmlspecialchars($row['date_payment']); ?></td>
            <td style="color: red;font-weight: bold;"><?= htmlspecialchars($row['reglement']); ?> EUR</td>




        </tr>
    <?php
        endwhile;
    else:
    ?>
        <tr><td colspan="10">Aucune donnée trouvée</td></tr>
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
    // Extract 'id' from URL parameters
    var urlParams = new URLSearchParams(window.location.search);
    var agenceId = urlParams.get('pid'); // Extract 'id' from the URL

    if (agenceId) {
        $('#reservation-daterange').daterangepicker({
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY',
            },
        }, function (start, end, label) {
            console.log("Selected range: " + start.format('DD/MM/YYYY') + ' to ' + end.format('DD/MM/YYYY'));

            // Get the selected start and end dates
            var startDate = start.format('YYYY-MM-DD');
            var endDate = end.format('YYYY-MM-DD');

            // Update URL parameters to include startDate and endDate
            updateURLParams(agenceId, startDate, endDate);

            // Trigger AJAX request to update the table
            filterReservations(agenceId, startDate, endDate);
        });
    } else {
        console.error("No agency ID found in URL.");
    }

    // Function to update URL parameters
    function updateURLParams(agenceId, startDate, endDate) {
        let newUrl = new URL(window.location.href);
        newUrl.searchParams.set('pid', agenceId);
        newUrl.searchParams.set('startDate', startDate);
        newUrl.searchParams.set('endDate', endDate);

        // Update the browser's URL without refreshing the page
        window.history.pushState(null, '', newUrl);
    }

    // Function to filter reservations
    function filterReservations(agenceId, startDate, endDate) {
        $.ajax({
            url: 'reservation_agence.php',
            type: 'GET',
            data: {
                pid: agenceId,
                startDate: startDate,
                endDate: endDate
            },
            success: function (response) {
                console.log("AJAX Response: ", response);
                $('table#datatable-buttons tbody').html(response); // Update table body

                // Reinitialize DataTable to reflect the filtered data
                var tableButtons = $('#datatable-buttons').DataTable();
                tableButtons.clear(); // Clear old data
                tableButtons.rows.add($('#datatable-buttons tbody tr')); // Add new rows
                tableButtons.draw(); // Redraw table
            },
            error: function (xhr, status, error) {
                console.error('Error filtering reservations: ' + error);
            },
        });
    }
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
