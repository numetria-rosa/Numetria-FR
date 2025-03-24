<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once 'files/db_connection.php'; // Database connection file

// Get the agency ID from the query string
$agency_id = isset($_GET['id']) ? $_GET['id'] : null;

$agency = null;  // Initialize the $agency variable

if ($agency_id) {
    // Fetch agency details from the database using mysqli
    $stmt = $conn->prepare("SELECT * FROM agence WHERE id = ?");
    $stmt->bind_param("i", $agency_id); // Bind the parameter (assuming it's an integer)
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any record was found
    if ($result->num_rows > 0) {
        $agency = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="TheAdmin - Responsive admin and web application ui kit">
  <meta name="keywords" content="admin, dashboard, web app, sass, ui kit, ui framework, bootstrap">

  <title>Clients &mdash; TheAdmin Invoicer</title>
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



        </ul>
      </nav>

    </aside>
    <!-- END Sidebar -->



    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
          <a class="logo d-lg-none" href="index.php"><img src="assets/img/                    " alt="logo"></a>

        <ul class="topbar-btns">

          <!-- Notifications -->
          <li class="dropdown d-none d-lg-block">


              <div class="media-list media-list-hover media-list-divided media-list-xs">
                <a class="media media-new" href="#">
                  <span class="avatar bg-success"><i class="ti-user"></i></span>
                  <div class="media-body">
                    <p>New user registered</p>
                    <time datetime="2018-07-14 20:00">Just now</time>
                  </div>
                </a>

                <a class="media" href="#">
                  <span class="avatar bg-info"><i class="ti-shopping-cart"></i></span>
                  <div class="media-body">
                    <p>New order received</p>
                    <time datetime="2018-07-14 20:00">2 min ago</time>
                  </div>
                </a>

                <a class="media" href="#">
                  <span class="avatar bg-warning"><i class="ti-face-sad"></i></span>
                  <div class="media-body">
                    <p>Refund request from <b>Ashlyn Culotta</b></p>
                    <time datetime="2018-07-14 20:00">24 min ago</time>
                  </div>
                </a>

                <a class="media" href="#">
                  <span class="avatar bg-primary"><i class="ti-money"></i></span>
                  <div class="media-body">
                    <p>New payment has made through PayPal</p>
                    <time datetime="2018-07-14 20:00">53 min ago</time>
                  </div>
                </a>
              </div>

              <div class="dropdown-footer">
                <div class="left">
                  <a href="#">Read all notifications</a>
                </div>

                <div class="right">
                  <a href="#" data-provide="tooltip" title="Mark all as read"><i class="fa fa-circle-o"></i></a>
                  <a href="#" data-provide="tooltip" title="Update"><i class="fa fa-repeat"></i></a>
                  <a href="#" data-provide="tooltip" title="Settings"><i class="fa fa-gear"></i></a>
                </div>
              </div>

            </div>
          </li>
          <!-- END Notifications -->



              <div class="media-list media-list-divided media-list-hover media-list-xs scrollable" style="height: 290px">
                <a class="media media-new1" href="#">
                  <span class="avatar status-success">
                    <img src="../../assets/img/avatar/                     " alt="...">
                  </span>

                  <div class="media-body">
                    <p><strong>Maryam Amiri</strong> <time class="float-right" datetime="2018-07-14 20:00">23 min ago</time></p>
                    <p class="text-truncate">Authoritatively exploit resource maximizing technologies before technically.</p>
                  </div>
                </a>

                <a class="media" href="#">
                  <span class="avatar status-warning">
                    <img src="../../assets/img/avatar/2.jpg" alt="...">
                  </span>

                  <div class="media-body">
                    <p><strong>Hossein Shams</strong> <time class="float-right" datetime="2018-07-14 20:00">48 min ago</time></p>
                    <p class="text-truncate">Continually plagiarize efficient interfaces after bricks-and-clicks niches.</p>
                  </div>
                </a>

                <a class="media" href="http://thetheme.io/theadmin/page-app/mailbox-single.html">
                  <span class="avatar status-dark">
                    <img src="../../assets/img/avatar/3.jpg" alt="...">
                  </span>

                  <div class="media-body">
                    <p><strong>Helen Bennett</strong> <time class="float-right" datetime="2018-07-14 20:00">3 hours ago</time></p>
                    <p class="text-truncate">Objectively underwhelm cross-unit web-readiness before sticky outsourcing.</p>
                  </div>
                </a>

                <a class="media" href="http://thetheme.io/theadmin/page-app/mailbox-single.html">
                  <span class="avatar status-success bg-purple">FT</span>

                  <div class="media-body">
                    <p><strong>Fidel Tonn</strong> <time class="float-right" datetime="2018-07-14 20:00">21 hours ago</time></p>
                    <p class="text-truncate">Interactively innovate transparent relationships with holistic infrastructures.</p>
                  </div>
                </a>

                <a class="media" href="http://thetheme.io/theadmin/page-app/mailbox-single.html">
                  <span class="avatar status-danger">
                    <img src="../../assets/img/avatar/4.jpg" alt="...">
                  </span>

                  <div class="media-body">
                    <p><strong>Freddie Arends</strong> <time class="float-right" datetime="2018-07-14 20:00">Yesterday</time></p>
                    <p class="text-truncate">Collaboratively visualize corporate initiatives for web-enabled value.</p>
                  </div>
                </a>

                <a class="media" href="http://thetheme.io/theadmin/page-app/mailbox-single.html">
                  <span class="avatar status-success">
                    <img src="../../assets/img/avatar/5.jpg" alt="...">
                  </span>

                  <div class="media-body">
                    <p><strong>Freddie Arends</strong> <time class="float-right" datetime="2018-07-14 20:00">Yesterday</time></p>
                    <p class="text-truncate">Interactively reinvent standards compliant supply chains through next-generation bandwidth.</p>
                  </div>
                </a>
              </div>

              <div class="dropdown-footer">
                <div class="left">
                  <a href="#">Read all messages</a>
                </div>

                <div class="right">
                  <a href="#" data-provide="tooltip" title="Mark all as read"><i class="fa fa-circle-o"></i></a>
                  <a href="#" data-provide="tooltip" title="Settings"><i class="fa fa-gear"></i></a>
                </div>
              </div>

            </div>
          </li>
          <!-- END Messages -->

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


          <h4 class=>nom d'agence</h4>
         <hr>





<div class="col-12">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Données Agence</h4>
            <div class="table-responsive">
                <table class="table table-editable table-nowrap align-middle table-edits">
                    <thead>
                        <tr>
                            <th>Nom Agence :</th>
                            <th>Email Agence</th>
                            <th>Login Agence:</th>
                            <th>Télephone:</th>
                            <th>Fax :</th>
                            <th>Adress :</th>
                            <th>Code Postal :</th>
                            <th>Pays :</th>
                            <th>Ville :</th>
                            <th>N° Registre de Commerce :</th>
                            <th>N° Matricule Fiscale :</th>
                            <th>N° IATA :</th>
                            <th>Licence:</th>
                            <th>État :</th>
                            <th>MARKUP :</th>
                            <th>Password :</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($agency): ?>
                            <tr data-id="<?php echo $agency['id']; ?>">
                                <td data-field="nom"><?php echo htmlspecialchars($agency['nom_agence']); ?></td>
                                <td data-field="email"><?php echo htmlspecialchars($agency['email']); ?></td>
                                <td data-field="login"><?php echo htmlspecialchars($agency['login']); ?></td>
                                <td data-field="tel"><?php echo htmlspecialchars($agency['tel']); ?></td>
                                <td data-field="fax"><?php echo htmlspecialchars($agency['fax']); ?></td>
                                <td data-field="adress"><?php echo htmlspecialchars($agency['adresse']); ?></td>
                                <td data-field="codep"><?php echo htmlspecialchars($agency['code_postal']); ?></td>
                                <td data-field="pays"><?php echo htmlspecialchars($agency['code_pays']); ?></td>
                                <td data-field="ville"><?php echo htmlspecialchars($agency['ville']); ?></td>
                                <td data-field="com"><?php echo htmlspecialchars($agency['reg_commerce']); ?></td>
                                <td data-field="fis"><?php echo htmlspecialchars($agency['num_fiscal']); ?></td>
                                <td data-field="iata"><?php echo htmlspecialchars($agency['code_iata']); ?></td>
                                <td data-field="License"><?php echo htmlspecialchars($agency['num_licence']); ?></td>
                                <td data-field="etat"><?php echo $agency['etat'] == 1 ? 'Activé' : 'Deactivé'; ?></td>
                                <td data-field="Markup"><?php echo htmlspecialchars($agency['markup']); ?></td>
                                <td data-field="New Password">[Password Hidden]</td>
                                <td>
                                    <a class="btn btn-outline-secondary btn-sm edit" title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="17">No agency found with the given ID.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> <!-- end col -->

<script>
$(document).ready(function() {


    // Move the buttons container
    tableButtons.buttons().container().appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)");

    // Style the select input for table length
    $(".dataTables_length select").addClass("form-select form-select-sm");

    // Redirect to agence_details.php on clicking the Details button
    $(document).on('click', '.details-btn', function() {
        // Get the id of the clicked agency (you can customize this according to your data structure)
        var agencyId = $(this).data('id');  // Make sure to set data-id attribute for the Details button in your HTML
        window.location.href = "agence_details.php?id=" + agencyId;  // Redirect with the agency id
    });

    // Add a callback function to refresh the table after a form submission (post-action)
    $(document).on('submit', 'form', function(e) {
        var form = $(this);

        // Prevent the default form submission behavior to handle it with AJAX
        e.preventDefault();

        // Handle the form submission via AJAX
        $.ajax({
            url: form.attr('action'),  // Get the action URL of the form
            type: 'POST',
            data: form.serialize(),  // Serialize the form data
            success: function(response) {
              console.log(response); // Log the response from the server

                // Reload the DataTable after the action (Activate/Deactivate)
                table.ajax.reload();  // You can also use table.draw(); if you're not using AJAX

                // Optional: Reload the page (if necessary for other purposes)
                // location.reload();
            },
            error: function(xhr, status, error) {
                // Handle any errors (e.g., log them or display an error message)
                console.error("Error: " + error);
            }
        });
    });
});
<\script>

          <footer class="flexbox align-items-center py-20">
            <span class="flex-grow text-right text-lighter pr-2">1-10 of 1,853</span>
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
        <script src="assets/libs/jquery/jquery.min.js"></script>
        <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/libs/metismenu/metisMenu.min.js"></script>
        <script src="assets/libs/simplebar/simplebar.min.js"></script>
        <script src="assets/libs/node-waves/waves.min.js"></script>
        <script src="assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>

        <!-- Table Editable plugin -->
        <script src="assets/libs/table-edits/build/table-edits.min.js"></script>

        <script src="assets/js/pages/table-editable.int.js"></script>

        <!-- App js -->
        <script src="assets/js/app.js"></script>
  </body>

<!-- Mirrored from thetheme.io/theadmin/samples/invoicer/agence.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 13 Jan 2025 09:38:55 GMT -->
</html>




