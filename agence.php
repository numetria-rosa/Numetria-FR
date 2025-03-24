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
    <div class="main-content">
        <div class="media-list media-list-divided media-list-hover" data-provide="selectall">
            <h4 class=>Agences</h4>
            <hr>
            <a href="add_agency.php" class="ajouter-agence-btn">Ajouter Agence</a>

                <style>
                .ajouter-agence-btn {
                    display: inline-block;
                    margin-top: 5px;
                    padding: 10px 15px;
                    background-color:rgb(8, 167, 199);
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    transition: background-color 0.3s;
                    margin-bottom:10px;
                }

                .ajouter-agence-btn:hover {
                    background-color: #0056b3;
                    color:white;
                }
                </style>
            <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                <thead>
                    <tr>
                        <th>Agence</th>
                        <th>Pays</th>
                        <th>Ville</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Date de Création</th>
                        <th>Solde</th>
                        <th>Etat</th>
                        <th>Réglements</th>
                        <th>Réservations</th> <!-- Added Reservations column -->
                    </tr>
                </thead>
                                <tbody id="agenceTableBody">
                                <?php include 'fetch_agence_data.php'; ?>
                            </tbody>

            </table>
        </div>
    </div>
</main>



          <footer class="flexbox align-items-center py-20">
            <nav>
              <a class="btn btn-sm btn-square disabled" href="#"><i class="ti-angle-left"></i></a>
              <a class="btn btn-sm btn-square" href="#"><i class="ti-angle-right"></i></a>
            </nav>
          </footer>

        </div>


      </div><!--/.main-content -->


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




      




    <!-- Quickview - Add user -->
    <div id="qv-user-add" class="quickview quickview-lg">
      <header class="quickview-header">
        <p class="quickview-title lead fw-400">Add new client</p>
        <span class="close"><i class="ti-close"></i></span>
      </header>

      <div class="quickview-body">

        <div class="quickview-block form-type-material">
          <h6>Personal information</h6>
          <div class="form-group">
            <input type="text" class="form-control">
            <label>Name</label>
          </div>

          <div class="form-group">
            <input type="text" class="form-control">
            <label>Email Address</label>
          </div>

          <div class="form-group">
            <input type="text" class="form-control">
            <label>Phone Number</label>
          </div>

          <div class="form-group">
            <input type="text" class="form-control">
            <label>Website</label>
          </div>

          <div class="form-group form-type-material file-group">
            <input type="text" class="form-control file-value file-browser" readonly>
            <label>Logo</label>
            <input type="file" multiple>
          </div>

          <div class="h-40px"></div>

          <h6>Address</h6>
          <div class="form-group">
            <select class="form-control" data-provide="selectpicker">
              <option>United States</option>
              <option>Canada</option>
              <option>Mexico</option>
              <option>Japan</option>
              <option>Other</option>
            </select>
            <label>Country</label>
          </div>

          <div class="form-group">
            <input type="text" class="form-control">
            <label>City</label>
          </div>

          <div class="form-group">
            <input type="text" class="form-control">
            <label>Address</label>
          </div>
        </div>
      </div>

      <footer class="p-12 text-right">
        <button class="btn btn-flat btn-secondary" type="button" data-toggle="quickview">Cancel</button>
        <button class="btn btn-flat btn-primary" type="submit">Add client</button>
      </footer>
    </div>
    <!-- END Quickview - Add user -->




    <!-- Quickview - User detail -->
    <div id="qv-user-details" class="quickview quickview-lg">
      <div class="quickview-body">

        <div class="card card-inverse">
          <div class="flexbox px-20 pt-20">
            <label class="toggler text-white">
              <input type="checkbox">
              <i class="fa fa-star"></i>
            </label>

            <a class="text-white fs-20 lh-1" href="#"><i class="fa fa-trash"></i></a>
          </div>

          <div class="card-body text-center pb-50">
            <a href="#">
              <img class="avatar avatar-xxl avatar-bordered" src="assets/img/avatar/                     ">
            </a>
            <h4 class="mt-2 mb-0"><a class="hover-primary text-white" href="#">Maryam Amiri</a></h4>
            <span><i class="fa fa-map-marker w-20px"></i> San Fransisco</span>
          </div>
        </div>

        <div class="quickview-block form-type-material">
          <div class="form-group">
            <input type="text" class="form-control" value="Maryame Amiri">
            <label>Name</label>
          </div>

          <div class="form-group">
            <input type="text" class="form-control" value="maryam.amiri@gmail.com">
            <label>Email Address</label>
          </div>

          <div class="form-group">
            <input type="text" class="form-control" value="(123) 456-7890">
            <label>Phone Number</label>
          </div>

          <div class="form-group">
            <input type="text" class="form-control" value="thetheme.io">
            <label>Website</label>
          </div>

          <div class="h-40px"></div>
          <div class="form-group">
            <select class="form-control" data-provide="selectpicker">
              <option>United States</option>
              <option>Canada</option>
              <option>Mexico</option>
              <option>Japan</option>
              <option>Other</option>
            </select>
            <label>Country</label>
          </div>

          <div class="form-group">
            <input type="text" class="form-control" value="San Fransisco">
            <label>City</label>
          </div>

          <div class="form-group">
            <input type="text" class="form-control" value="1135, Apt 2, Main St.">
            <label>Address</label>
          </div>
        </div>
      </div>

      <footer class="p-12 text-right">
        <button class="btn btn-flat btn-secondary" type="button" data-toggle="quickview">Cancel</button>
        <button class="btn btn-flat btn-primary" type="submit">Save changes</button>
      </footer>
    </div>
    <!-- END Quickview - User detail -->

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
