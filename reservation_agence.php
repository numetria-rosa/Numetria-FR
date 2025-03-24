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


// Pagination settings
$rowsPerPage = 3;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $rowsPerPage;

// Count total records
$countQuery = "SELECT COUNT(*) AS total FROM reglement WHERE agence = $agenceId";
$countResult = $conn->query($countQuery);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $rowsPerPage);

// Fetch paginated data
$query_reglement = "SELECT pid, reg_on, reg, currency, comment, etat_reg, etat_payment, reg_ref  
                    FROM reglement 
                    WHERE agence = $agenceId 
                    ORDER BY reg_on DESC 
                    LIMIT $rowsPerPage OFFSET $offset";

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

$today = date('Y-m-d');

// Get date range from GET parameters
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : $today;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : $today;

// Updated SQL query
$sql = "
    SELECT 
        CASE 
            WHEN booking.bookingservicetype = 'flights' THEN CONCAT(booking.bookingreference, ' | ', flightitinerary.pnrNum)
            ELSE booking.bookingreference 
        END AS reference_reservation,
        booking.pid AS booking_pid,
        agence.prenom AS agent,
        agence.nom_agence AS agence,
        agence.pid AS agence_pid,
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
        
        CONCAT(CONVERT(booking.leaderfirstname USING utf8mb4), ' ', CONVERT(booking.leaderlastname USING utf8mb4)) AS client,
        booking.starttime AS date_reservation,
        booking.grossamount AS prix,
        
        CASE
            WHEN booking.currentstatus = 'cancelled' THEN 'Annulée'
            WHEN booking.currentstatus = 'refused' THEN 'Annulée'
            WHEN booking.currentstatus = 'requested' THEN 'En attente'
            WHEN booking.currentstatus = 'vouchered' THEN 'Confirmée'
            ELSE booking.currentstatus
        END AS etat,
        
        booking.currencycode AS currency,

        -- Etat Reservation Column
        CASE 
            WHEN booking.etat_res = 1 THEN 'Payée'
            ELSE 'Non Payée'
        END AS etat_reservation

    FROM
        booking
    INNER JOIN
        agence ON booking.pidagence = agence.pid
    LEFT JOIN
        flightitinerary ON booking.pid = flightitinerary.BookingId
    LEFT JOIN
        activitydetail ON booking.pid = activitydetail.pidBooking
    LEFT JOIN
        mystifly_reservation_item ON booking.id = mystifly_reservation_item.airline_pnr
    LEFT JOIN
        archive ON booking.pidagence = archive.agency_pid
    WHERE agence.pid = $agenceId 
    AND booking.starttime BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
    GROUP BY
        booking.bookingreference
    ORDER BY
        booking.starttime DESC
";





$result = $conn->query($sql);
if (!empty($startDate) && !empty($endDate)) {
   
}

// REGLEMENT 
$totalCreditQuery = "
    SELECT SUM(reg) AS total_credit
    FROM reglement
    WHERE agence = $agenceId 
    AND etat_reg = 'encaissé' 
    AND etat_payment = 0
    
";

$totalCreditResult = $conn->query($totalCreditQuery);
if ($totalCreditResult && $totalCreditRow = $totalCreditResult->fetch_assoc()) {
    $totalCredit = isset($totalCreditRow['total_credit']) ? (float)$totalCreditRow['total_credit'] : 0;
} else {
    $totalCredit = 0;
}

$totalDebitQuery = " 
    SELECT SUM(booking.grossamount) AS total_debit
    FROM booking
    LEFT JOIN archive ON booking.pid = archive.res_pid -- Left join to include missing bookings
    WHERE booking.pidagence = $agenceId 
    AND booking.currentstatus = 'vouchered' 
    AND booking.etat_res = 0 -- Check if booking.etat_res is 0
";


if (!empty($startDate) && !empty($endDate)) {
    $totalDebitQuery .= " AND booking.starttime BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'";
}

$totalDebitResult = $conn->query($totalDebitQuery);

if ($totalDebitResult) {
    $totalDebitRow = $totalDebitResult->fetch_assoc();
    $totalDebit = isset($totalDebitRow['total_debit']) ? $totalDebitRow['total_debit'] : 0;
} else {
    $totalDebit = 0;
}

$totalSolde = $totalCredit - $totalDebit;

// $totalMontant
$totalMontantQuery = "
    SELECT SUM(reg) AS total_credit
    FROM reglement
    WHERE agence = $agenceId 
    AND etat_reg = 'encaissé' 
    AND rest = 0
    
";

$totalMontantResult = $conn->query($totalMontantQuery);
if ($totalMontantResult && $totalMontantRow = $totalMontantResult->fetch_assoc()) {
    $totalMontant = isset($totalMontantRow['total_credit']) ? (float)$totalMontantRow['total_credit'] : 0;
} else {
    $totalMontant = 0;
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
          echo '<td id="agenceName" style="display: none;">' . htmlspecialchars($row['agence']) . '</td>';
          echo '<td id="refRes" style="' . $cellStyle . '">' . htmlspecialchars($row['reference_reservation']) . '</td>';
          echo '<td>' . htmlspecialchars($row['agent']) . '</td>';
          echo '<td>' . htmlspecialchars($row['destination']) . '</td>';
          echo '<td>' . htmlspecialchars($row['client']) . '</td>';
          echo '<td id="dateRes">' . htmlspecialchars($row['date_reservation']) . '</td>';
          echo '<td id="price">' . number_format($row['prix'], 2) . ' ' . htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR') . '</td>';
          echo '<td style="' . $statusStyle . '">' . htmlspecialchars($row['etat']) . '</td>';
          echo '<td>';
          if ($row['etat_reservation'] == 'Payée') {
              echo '<span style="font-weight: bold; color: green;">' . htmlspecialchars($row['etat_reservation']) . '</span>';
          } elseif ($row['etat_reservation'] == 'Non Payée') {
              echo '<span style="font-weight: bold; color: red;">' . htmlspecialchars($row['etat_reservation']) . '</span>';
          } else {
              echo htmlspecialchars($row['etat_reservation']);
          }
          echo '</td>';
                    echo '<td>' . number_format($totalCredit, 2) . ' ' . htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR') . '</td>';
          echo '<td>' . number_format($totalDebit, 2) . ' ' . htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR') . '</td>';
          echo '<td>' . number_format($totalSolde, 2) . ' ' . htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR') . '</td>';

          echo '</tr>';

     
          $currentRow++;
      }
  } else {
      echo '<tr><td colspan="10">Aucune donnée trouvée</td></tr>';
  }
  exit;
}
if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'totalCredit' => $totalCredit,
        'totalDebit' => $totalDebit,
        'totalSolde' => $totalSolde
    ]);
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

  <title>Comptabilté&mdash; Zouhour Mechergui</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
  <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Check if this is a manual reload (not a normal navigation)
        if (performance.navigation.type === 1) {  // 1 = Reload
            window.location.href = "http://localhost/reservation_agence.php?pid=<?php echo $agenceId; ?>";
        }
    });
</script>


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
          <li class="menu-item">
            <a class="menu-link" href="etat.php">
              <span class="icon ti-agenda"></span>
              <span class="title">Etat</span>
            </a>
          </li>
          <li class="menu-item">
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
    <h4 class="card-title">Réservations | <?= htmlspecialchars($nomAgence); ?></h4>
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

  <!--  the Date Range Picker here -->
  <div id="daterange-container" class="mb-3">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text" id="calendar-icon">
                    <i class="fa fa-calendar"></i>
                </span>
            </div>
            <input type="text" id="reservation-daterange" class="form-control daterange-input" placeholder="Sélectionner une période" style="margin-button:100px;">
        </div>
    </div>

    <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
    <thead>
        <tr>
            <th>Référence Réservation | PNR </th>
            <th>Agent</th>
            <th>Destination</th>
            <th>Client</th>
            <th>Date de Réservation</th>
            <th>Prix</th>
            <th>État</th>
            <th>État Payment</th>
            <th>Total Crédit (Réglement)</th>
            <th>Total Débit</th>
            <th>Total Solde</th>
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
            <td><?= htmlspecialchars($row['agent']); ?></td>
            <td><?= htmlspecialchars($row['destination']); ?></td>
            <td><?= htmlspecialchars($row['client']); ?></td>
            <td><?= htmlspecialchars($row['date_reservation']); ?></td>
            <td style=" color: orange; font-weight: bold;"><?= number_format($row['prix'], 2) . ' ' . htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR'); ?></td>
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
            <td>
                        <?php 
                            $etat_reservation = htmlspecialchars($row['etat_reservation']);
                            if ($etat_reservation == 'Payée') {
                                echo "<span style='font-weight: bold; color: green;'>$etat_reservation</span>";
                            } elseif ($etat_reservation == 'Non Payée') {
                                echo "<span style='font-weight: bold; color: red;'>$etat_reservation</span>";
                            } else {
                                echo $etat_reservation;
                            }
                        ?>
                        </td>
            <td style="font-weight: bold;"><?= number_format($totalCredit, 2) . ' ' . htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR'); ?></td>
            <td style="font-weight: bold;"><?= number_format($totalDebit, 2) . ' ' . htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR'); ?></td>
            <td style="font-weight: bold;"><?= number_format($totalSolde, 2) . ' ' . htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR'); ?></td>

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
    // Extract 'pid', 'startDate', 'endDate' from URL parameters
    var urlParams = new URLSearchParams(window.location.search);
    var agenceId = urlParams.get('pid'); // Extract 'pid' from the URL
    var startDate = urlParams.get('startDate');
    var endDate = urlParams.get('endDate');

    // Detect manual page reload
    if (performance.navigation.type === 1) {  // 1 = Reload
        if (agenceId && !startDate && !endDate) {
            // If there's 'pid' but no 'startDate' or 'endDate', remove them from URL
            var newUrl = "http://localhost/reservation_agence.php?pid=" + agenceId;
            window.history.replaceState(null, '', newUrl); // Only set pid
        }
    }

    // If 'pid' exists in the URL, initialize the daterange picker
    if (agenceId) {
        $('#reservation-daterange').daterangepicker({
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY',
            },
        }, function (start, end, label) {
            console.log("Selected range: " + start.format('DD/MM/YYYY') + ' to ' + end.format('DD/MM/YYYY'));

            // Get the selected start and end dates
            var selectedStartDate = start.format('YYYY-MM-DD');
            var selectedEndDate = end.format('YYYY-MM-DD');

            // Update URL parameters to include startDate and endDate
            updateURLParams(agenceId, selectedStartDate, selectedEndDate);

            // Trigger AJAX request to update the table
            filterReservations(agenceId, selectedStartDate, selectedEndDate);
        });
    } else {
        console.error("No agency ID found in URL.");
    }

    // Function to update URL parameters when filtering
    function updateURLParams(agenceId, startDate, endDate) {
        let newUrl = new URL(window.location.href);
        newUrl.searchParams.set('pid', encodeURIComponent(agenceId));
        newUrl.searchParams.set('startDate', encodeURIComponent(startDate));
        newUrl.searchParams.set('endDate', encodeURIComponent(endDate));


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
<!-- Règlement Table -->
<div class="row mt-5">
    <!-- Règlement Section -->
<!-- Règlement Section -->
<div class="col-lg-6">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><strong>Montant Déposé:</strong>
                <span class="text-success fw-bold">
                    <?= number_format($totalMontant, 2) ?>
                    <?= htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR') ?>
                </span>
            </h5>
        </div>
        <div class="card-body">
            <h5 class="card-title"><strong>Les règlements</strong> récents</h5>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date de Réglement</th>
                        <th>Réference</th> <!-- New Etat Paiement column -->
                        <th>Montant</th>
                        <th>Commentaire</th>
                        <th>Etat</th> <!-- Added Etat column -->
                        <th>Etat Paiement</th> <!-- New Etat Paiement column -->

                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr id="transaction-<?= htmlspecialchars($transaction['pid']) ?>">
                        <td><?= htmlspecialchars($transaction['date']) ?></td>
                        <td style="color: red;font-weight: bold;"><?= htmlspecialchars($transaction['reg_ref']) ?></td>
                        <?php
                    // Extract the numeric value from the amount string
                    $amount_value = (float)str_replace(['+', ' ', 'EUR'], '', $transaction['amount']);
                    ?>
                    <td class="<?= ($amount_value < 0) ? 'text-danger' : 'text-success' ?> fw-500 fw-bold">
                        <?= ($amount_value < 0) ? htmlspecialchars($transaction['amount']) : '+' . htmlspecialchars($transaction['amount']) ?>
                    </td>    
                    <td><?= htmlspecialchars($transaction['comment']) ?></td>
                    <td>
    <!-- Debug: Check the etat_reg value -->
    <span class="etat-reg" style="font-weight: bold; color: black;"><?= ucfirst(strtolower(htmlspecialchars($transaction['etat_reg']))) ?></span>

    <!-- Toggle Button to Update Etat -->
    <button
        class="btn btn-sm btn-<?= $transaction['etat_reg'] == 'encaissé' ? 'danger' : 'success' ?>"
        data-id="<?= $transaction['pid'] ?>" data-etat="<?= $transaction['etat_reg'] ?>"
        style="margin-left: 10px;"
        data-bs-toggle="tooltip"
        title="<?= $transaction['etat_reg'] == 'encaissé' ? 'Cliquez ici si vous voulez NON Encaissée cette règlement' : 'Cliquez ici si vous voulez encaisser cette règlement' ?>"
        data-tooltip-color="<?= $transaction['etat_reg'] == 'encaissé' ? 'red' : 'green' ?>"
        data-bs-placement="top">
        <?= $transaction['etat_reg'] == 'encaissé' ? 'Non Encaissé' : 'Encaissé' ?>
    </button>
</td>


                        <!-- Etat Paiement Button based on 'etat_payment' value -->
                        <td>

                        <button class="insertDataButton btn btn-sm btn-<?= $transaction['etat_payment'] == 0 ? 'warning' : 'success' ?>"
                                data-id="<?= $transaction['pid'] ?>"
                                data-etat-payment="<?= $transaction['etat_payment'] ?>" 
                                data-reg-ref="<?= $transaction['reg_ref'] ?>"
                                data-tooltip="<?= $transaction['etat_payment'] == 0 ? 'Cliquez ici si vous voulez consommer cette règlement' : 'Règlement déjà consommé' ?>"
                                data-tooltip-color="<?= $transaction['etat_payment'] == 0 ? 'red' : 'green' ?>"
                                >
                            <?= $transaction['etat_payment'] == 0 ? 'Non Consommé' : 'Consommé' ?>
                        </button>


                        </td>

                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5">Aucun règlement récent trouvé.</td> <!-- Adjusted colspan to 5 -->
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a style="color : black; font-weight : bold;" class="page-link" href="?pid=<?= $agenceId ?>&page=<?= $page - 1 ?>">Précédent</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li  class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a style="color : black; font-weight : bold;" class="page-link" href="?pid=<?= $agenceId ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a style="color : black; font-weight : bold;" class="page-link" href="?pid=<?= $agenceId ?>&page=<?= $page + 1 ?>">Suivant</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

        </div>
    </div>
</div>



<script>
    console.log("JavaScript loaded successfully!");
</script>


<script>
$(document).ready(function() { 
    $('.insertDataButton').each(function() {
        if ($(this).data('etat-payment') == 0) { // Only select "Non Consommé" buttons
            $(this).on('click', function(event) {
                event.preventDefault(); // Prevent unwanted reloads

                const urlParams = new URLSearchParams(window.location.search);
                const startDate = urlParams.get('startDate');
                const endDate = urlParams.get('endDate');

                let regRef = $(this).data('reg-ref');
                let rowData = [];

                $('table#datatable-buttons tbody tr').each(function() {
                    let row = $(this);
                    rowData.push({
                        agencePid: row.find('#agencePid').text().trim(),
                        bookingPid: row.find('#bookingPid').text().trim(),
                        agenceName: row.find('#agenceName').text().trim(),
                        refRes: row.find('#refRes').text().trim(),
                        dateRes: row.find('#dateRes').text().trim(),
                        price: row.find('#price').text().trim()
                    });
                });

                $.ajax({
                    url: 'insert_archive.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        startDate: startDate,
                        endDate: endDate,
                        regRef: regRef,
                        rows: rowData
                    }),
                    success: function(response) {
                        if (response.status === "success") {
                            alert('Data successfully inserted!');
                            window.location.href = 'http://localhost/reservation_agence.php?pid=' + response.agencePid;
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        console.log(xhr.responseText);
                    }
                });

                console.log('Data sent:', {
                    startDate: startDate,
                    endDate: endDate,
                    regRef: regRef,
                    rows: rowData
                });
            });
        }
    });
});

</script>













<style>
   /* Tooltip Styling */
button[data-tooltip]:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    background-color: white;
    color: #333;
    border: 2px solid #ccc;
    padding: 10px; /* Reduced padding */
    font-size: 12px; /* Smaller font size */
    border-radius: 5px; /* Slightly smaller border radius */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    width: auto;
    max-width: 500px; /* Reduced max-width */
    top: -35px; /* Adjusted position of tooltip */
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    white-space: nowrap;
}

/* Tooltip Color Based on Etat */
button[data-tooltip-color="red"]:hover::after {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

button[data-tooltip-color="green"]:hover::after {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

/* Add positioning for the buttons */
button {
    position: relative;
}


</style>
    <script>
document.querySelectorAll('.btn').forEach(button => {
    button.addEventListener('click', function () {
        const button = this;
        const transactionId = button.getAttribute('data-id');
        const currentEtat = button.getAttribute('data-etat');
        const newEtat = (currentEtat === 'encaissé') ? 'non encaissé' : 'encaissé';

        // Send AJAX request to update the database
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update-etat.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                // After updating the etat_reg in the database, reload the page
                window.location.reload();
            } else {
                alert("Error updating status.");
            }
        };
        xhr.send('transaction_id=' + encodeURIComponent(transactionId) + '&new_etat=' + encodeURIComponent(newEtat));
    });
});
</script>





<script>
$(document).ready(function () {
    $(".toggle-etat").click(function () {
        var button = $(this);
        var pid = button.data("pid");
        var currentEtat = button.data("etat");

        $.ajax({
            url: "update-etat.php",
            type: "POST",
            data: { pid: pid, etat_reg: currentEtat },
            success: function (response) {
                if (response === "success") {
                    // After a successful update, reload the page
                    window.location.reload();
                } else {
                    alert("Error updating status.");
                }
            }
        });
    });
});
</script>




<?php
// Include database connection
include 'files/db_connection.php';

// Get the agency ID from the URL
$agencyId = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
if ($agencyId == 0) {
    die("Invalid Agency ID.");
}

// Fetch agency name
$agencyQuery = "SELECT nom_agence FROM agence WHERE pid = $agencyId";
$agencyResult = $conn->query($agencyQuery);
$agencyName = '';
if ($agencyResult && $agencyRow = $agencyResult->fetch_assoc()) {
    $agencyName = $agencyRow['nom_agence'];
}

// Fetch transactions for the agency
$queryTransactions = "SELECT pid, reg, currency, comment, etat_reg , etat_payment FROM reglement WHERE agence = $agencyId";
$resultTransactions = $conn->query($queryTransactions);
$transactions = [];
$totalIncome = 0;

if ($resultTransactions && $resultTransactions->num_rows > 0) {
    while ($row = $resultTransactions->fetch_assoc()) {
        $transactions[] = [
            'pid' => htmlspecialchars($row['pid']),
            'amount' => htmlspecialchars($row['reg']) . " " . htmlspecialchars($row['currency']),
            'comment' => htmlspecialchars($row['comment']),
            'status' => htmlspecialchars($row['etat_reg']),
        ];
        if (is_numeric($row['reg'])) {
            $totalIncome += (float)$row['reg'];
        }
    }
}

// Get total credited amount (income)
$totalIncomeQuery = "SELECT SUM(reg) AS total_income FROM reglement WHERE agence = $agencyId AND etat_reg = 'encaissé' AND rest = 0";
$totalIncomeResult = $conn->query($totalIncomeQuery);
$totalIncome = ($totalIncomeResult && $totalIncomeRow = $totalIncomeResult->fetch_assoc()) ? (float)$totalIncomeRow['total_income'] : 0;

// Get total debited amount (expenses)
$totalExpensesQuery = "SELECT SUM(grossamount) AS total_expenses FROM booking WHERE pidagence = $agencyId AND currentstatus = 'vouchered'";
$totalExpensesResult = $conn->query($totalExpensesQuery);
$totalExpenses = ($totalExpensesResult && $totalExpensesRow = $totalExpensesResult->fetch_assoc()) ? (float)$totalExpensesRow['total_expenses'] : 0;

// Calculate balance
$totalBalance = $totalIncome - $totalExpenses;



?>





    <!-- Totals Section -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><strong>Totaux</strong></h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered" style="height: 156px;">
                    <tbody>
                        <tr>
                            <th>Total Crédit (Réglement)</th>
                            <td class="text-success fw-bold">

                                <?= number_format($totalIncome, 2) ?>
                                <?= htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR') ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Total Débit</th>
                            <td class="text-danger fw-bold">
                                <?= number_format($totalExpenses, 2) ?>
                                <?= htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR') ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Total Solde</th>
                            <td class="text-primary fw-bold">
                                <?= number_format($totalBalance, 2) ?>
                                <?= htmlspecialchars(isset($transactions[0]['currency']) ? $transactions[0]['currency'] : 'EUR') ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

</div>

<!--/.main-content -->


      <!-- Footer -->
      <footer class="site-footer">
        <div class="row">
          <div class="col-md-6">
            <p class="text-center text-md-left">Copyright © 2025 Zouhour Mechergui</a> . All rights reserved.</p>
          </div>
          <script>
    window.onload = function() {
        console.log("JavaScript loaded"); // Check if this shows up in the console

        // Select the table by its class or ID
        var table = document.querySelector('table');
        var rows = table.querySelectorAll('tr');
        
        rows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            if (cells.length > 0) {
                var referenceReservation = cells[0].innerText;
                console.log(referenceReservation); // Check if this logs the values
            }
        });
    };
</script>

        </div>
      </footer>
      <!-- END Footer -->

    </main>
    <!-- END Main container -->

















   <!-- JAVASCRIPT -->
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
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>



  </body>

<!-- Mirrored from thetheme.io/theadmin/samples/invoicer/agence.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 13 Jan 2025 09:38:55 GMT -->
</html>
