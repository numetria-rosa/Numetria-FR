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

    <!-- start page title -->
    <div class="row">
        <div class="col-sm-6">
            <div class="page-title-box">
                <h4></h4>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="state-information d-none d-sm-block">
                <div class="state-graph">
                    <div id="header-chart-1" data-colors='["--bs-primary"]'></div>
                    <div class="info"></div>
                </div>
                <div class="state-graph">
                    <div id="header-chart-2" data-colors='["--bs-info"]'></div>
                    <div class="info"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <h5><b>Informations D'agence</b></h5>
                    <hr>
                    <div class="mb-3 row">
                        <label for="nom-agence" class="col-md-2 col-form-label"><b>Nom Agence*</b></label>
                        <div class="col-md-10">
                            <input class="form-control" type="text" id="nom-agence">
                        </div>
                    </div><!-- end row -->

                    <div class="mb-3 row">
                        <label for="email-agence" class="col-md-2 col-form-label"><b>Email Agence*</b></label>
                        <div class="col-md-10">
                            <input class="form-control" type="email" id="email-agence">
                        </div>
                    </div><!-- end row -->

                    <div class="mb-3 row">
                        <label for="telephone-agence" class="col-md-2 col-form-label"><b>Téléphone*</b></label>
                        <div class="col-md-10">
                            <input class="form-control" type="tel" id="telephone-agence">
                        </div>
                    </div><!-- end row -->

                    <div class="mb-3 row">
                        <label for="ville-agence" class="col-md-2 col-form-label"><b>Ville*</b></label>
                        <div class="col-md-10">
                            <input class="form-control" type="text" id="ville-agence">
                        </div>
                    </div><!-- end row -->

                    <h5>Informations Personnelles</h5>
                    <hr>

                    <div class="mb-3 row">
                        <label for="nom-personnel" class="col-md-2 col-form-label"><b>Nom*</b></label>
                        <div class="col-md-10">
                            <input class="form-control" type="text" id="nom-personnel">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label for="prenom-personnel" class="col-md-2 col-form-label"><b>Prénom*</b></label>
                        <div class="col-md-10">
                            <input class="form-control" type="text" id="prenom-personnel">
                        </div>
                    </div><!-- end row -->

                    <div class="mb-3 row">
                        <label for="email-personnel" class="col-md-2 col-form-label"><b>Email*</b></label>
                        <div class="col-md-10">
                            <input class="form-control" type="email" id="email-personnel">
                        </div>
                    </div><!-- end row -->

                    <div class="mb-3 row">
                        <label for="telephone-personnel" class="col-md-2 col-form-label"><b>Téléphone*</b></label>
                        <div class="col-md-10">
                            <input class="form-control" type="tel" id="telephone-personnel">
                        </div>
                    </div><!-- end row -->

                    <div class="text-end">
                        <button class="btn btn-primary" id="ajouter">Ajouter</button>
                    </div>

                </div>
            </div>
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->
</div>
<!-- container-fluid -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $("#ajouter").click(function () {
            let nomAgence = $("#nom-agence").val();
            let emailAgence = $("#email-agence").val();
            let telAgence = $("#telephone-agence").val();
            let villeAgence = $("#ville-agence").val();
            let nomPersonnel = $("#nom-personnel").val();
            let prenomPersonnel = $("#prenom-personnel").val();
            let emailPersonnel = $("#email-personnel").val();
            let telPersonnel = $("#telephone-personnel").val();

            $.ajax({
                url: "insert_agence.php",
                type: "POST",
                data: {
                    nom_agence: nomAgence,
                    email: emailAgence,
                    tel: telAgence,
                    ville: villeAgence,
                    nom: nomPersonnel,
                    prenom: prenomPersonnel,
                    email_client: emailPersonnel,
                    tel_client: telPersonnel
                },
                success: function (response) {
                    alert(response);
                },
                error: function () {
                    alert("Erreur lors de l'insertion.");
                }
            });
        });
    });
</script>


</div>
</main>

      <!-- Footer -->
      <footer class="site-footer">
        <div class="row">
          <div class="col-md-6">
            <p class="text-center text-md-left">Copyright © 2025 Zouhour Mechergui</a>. All rights reserved.</p>
          </div>

                    
        </div>
      </footer>
      <!-- END Footer -->

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
