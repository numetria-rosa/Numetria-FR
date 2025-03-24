<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
  
<!-- Mirrored from thetheme.io/theadmin/samples/invoicer/agence.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 13 Jan 2025 09:38:52 GMT -->
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="TheAdmin - Responsive admin and web application ui kit">
  <meta name="keywords" content="admin, dashboard, web app, sass, ui kit, ui framework, bootstrap">

  <title>Comptabilité &mdash; DMCBOOKING</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Styles -->
  <link href="assets/css/core.min.css" rel="stylesheet">
  <link href="assets/css/core.min1.css" rel="stylesheet">

  <link href="assets/css/app.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- Bootstrap Css -->
  <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Bootstrap Select CSS -->

<!-- Bootstrap Select JS -->


  <!-- Icons Css -->
  <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

  <!-- DataTables CSS -->
  <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
  <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
  <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

  <!-- Favicons -->
  <link rel="shortcut icon" href="assets/img/favicon.ico">
  <link rel="apple-touch-icon" href="assets/img/apple-touch-user.png">
  <link rel="icon" href="assets/img/favuser.png">

  <!-- DataTables CDN (Optional, remove if you're using the above local files) -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css"> 
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Ensure vertical divider is visible only on larger screens */
        @media (max-width: 768px) {
            .vertical-divider {
                display: none;
            }
        }
    </style>
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

      <header class="sidebar-header bg-info" style="background-color : #303719    !important;>
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

          <li class="menu-item active">
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
    <div class="container-fluid">
        <div class="card shadow mt-4">
            <div class="card-body">
                <h5 class="text-center"><b>Flight Information</b></h5>
                <hr>

                <div class="row">
                    <div class="col-md-5">
                        <!-- Agency Selection with Autocomplete -->
                        <div class="mb-3">
                            <label for="agency-select" class="form-label"><b>Selectionner Agence*</b></label>
                            <input type="text" class="form-control" id="agency-select" placeholder="Selectionner agence" autocomplete="off">
                            <ul id="agency-list" class="list-group" style="position: absolute; width: 100%; z-index: 10; display: none;"></ul>
                        </div>

                        <!-- Other Fields -->
                        <div class="mb-3">
                            <label for="client-name" class="form-label"><b>Client Name*</b></label>
                            <input type="text" class="form-control" id="client-name">
                        </div>
                        <div class="mb-3">
                            <label for="pnr-number" class="form-label"><b>PNR Number*</b></label>
                            <input type="text" class="form-control" id="pnr-number">
                        </div>
                        <div class="mb-3">
                            <label for="pnr-company" class="form-label"><b>Compagnie Aérienne*</b></label>
                            <input type="text" input="example : TU , LH etc ..  "class="form-control" id="pnr-company">
                        </div>
                      
                        <div class="mb-3">
                            <label for="agency-price" class="form-label"><b>Prix d'Agence (EUR)*</b></label>
                            <input type="number" class="form-control" id="agency-price">
                        </div>
                        <div class="mb-3">
                            <label for="total-price" class="form-label"><b>Prix à Payer (EUR)*</b></label>
                            <input type="number" class="form-control" id="total-price">
                        </div>
                    </div>

                    <div class="col-md-1 d-flex justify-content-center align-items-center vertical-divider">
                        <div style="border-left: 2px solid #ddd; height: 100%;"></div>
                    </div>

                    <div class="col-md-5">
                     
                        <div class="mb-3">
                            <label for="departure-location" class="form-label"><b>Departure Location*</b></label>
                            <input type="text" input="example : TUN " class="form-control" id="departure-location">
                        </div>
                        <div class="mb-3">
                            <label for="arrival-location" class="form-label"><b>Arrival Location*</b></label>
                            <input type="text" placeholder="example : FRA " class="form-control" id="arrival-location">
                        </div>
                        <div class="mb-3">
                            <label for="departure-date" class="form-label"><b>Departure Date*</b></label>
                            <input type="date" class="form-control" id="departure-date">
                        </div>
                        <div class="mb-3">
                            <label for="arrival-date" class="form-label"><b>Arrival Date*</b></label>
                            <input type="date" class="form-control" id="arrival-date">
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button class="btn btn-primary" id="submit-flight">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Autocomplete for Agency Selection
            $("#agency-select").on("input", function () {
                let query = $(this).val();

                if (query.length >= 4) {
                    $.ajax({
                        url: "fetch_agencies.php",
                        type: "GET",
                        data: { query: query },  // Send the input as query
                        success: function (response) {
                            let agencies = JSON.parse(response);
                            let agencyList = $("#agency-list");
                            agencyList.empty();  // Clear previous results
                            
                            if (agencies.length > 0) {
                                agencies.forEach(function (agency) {
                                    agencyList.append(`
                                        <li class="list-group-item" data-id="${agency.pid}">${agency.nom_agence}</li>
                                    `);
                                });
                                agencyList.show();  // Show the list
                            } else {
                                agencyList.hide();  // Hide if no match
                            }
                        },
                        error: function () {
                            alert("Error fetching agencies.");
                        }
                    });
                } else {
                    $("#agency-list").hide();  // Hide list if less than 4 characters
                }
            });

            // Select agency from the list
            $(document).on("click", "#agency-list li", function () {
                    let selectedAgency = $(this).text();
                    let agencyId = $(this).data("id");

                    $("#agency-select").val(selectedAgency);
                    $("#agency-select").attr("data-id", agencyId);  // Store the ID properly
                    $("#agency-list").hide();  
                });



            // Handle form submission
            $("#submit-flight").click(function () {
                let agencyId = $("#agency-select").attr("data-id");  // Retrieve stored ID
                            if (!agencyId) {
                                alert("Please select an agency.");
                                return;
                            }     
               let clientName = $("#client-name").val();
                let pnrNumber = $("#pnr-number").val();
                let pnrCompany = $("#pnr-company").val();
                let flightPrice = $("#flight-price").val();
                let agencyPrice = $("#agency-price").val();
                let totalPrice = $("#total-price").val();
                let departureLocation = $("#departure-location").val();
                let arrivalLocation = $("#arrival-location").val();
                let departureDate = $("#departure-date").val();
                let arrivalDate = $("#arrival-date").val();

                if (agencyId === "") {
                    alert("Please select an agency.");
                    return;
                }

                $.ajax({
                    url: "insert_flight.php",
                    type: "POST",
                    data: {
                        agency_id: agencyId,
                        client_name: clientName,
                        pnr_number: pnrNumber,
                        pnr_company: pnrCompany,
                        flight_price: flightPrice,
                        agency_price: agencyPrice,
                        total_price: totalPrice,
                        departure_location: departureLocation,
                        arrival_location: arrivalLocation,
                        departure_date: departureDate,
                        arrival_date: arrivalDate
                    },
                    success: function (response) {
                        alert(response);
                    },
                    error: function () {
                        alert("Error while inserting flight information.");
                    }
                });
            });
        });
    </script>
</main>

    <!-- END Main container -->






<!-- JAVASCRIPT -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

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

<!-- Datatable init js -->
<script src="assets/js/pages/datatables.init.js"></script>


  </body>

<!-- Mirrored from thetheme.io/theadmin/samples/invoicer/agence.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 13 Jan 2025 09:38:55 GMT -->
</html>
