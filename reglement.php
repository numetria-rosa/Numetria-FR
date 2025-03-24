<?php
require_once 'files/db_connection.php';

if (!isset($_GET['pid'])) {
    die("Agency ID not provided.");
}

$id = intval($_GET['pid']);

// Fetch agency details
$query_agence = "SELECT nom_agence FROM agence WHERE pid = $id";
$result_agence = $conn->query($query_agence);
if ($result_agence && $result_agence->num_rows > 0) {
    $agency = $result_agence->fetch_assoc();
    $nom_agence = htmlspecialchars($agency['nom_agence']);
} else {
    die("Agency not found.");
}

// Pagination logic
$rows_per_page = 4; // Number of rows per page
$page_reg = isset($_GET['page_reg']) ? intval($_GET['page_reg']) : 1; // Current page for reglement
$page_gar = isset($_GET['page_gar']) ? intval($_GET['page_gar']) : 1; // Current page for garantie

// Calculate offset for pagination
$offset_reg = ($page_reg - 1) * $rows_per_page;
$offset_gar = ($page_gar - 1) * $rows_per_page;

// Fetch reglement details with pagination and ORDER BY reg_on DESC
$query_reglement = "SELECT reg_on, reg, currency, comment, etat_payment FROM reglement WHERE agence = $id ORDER BY reg_on DESC LIMIT $rows_per_page OFFSET $offset_reg";
$result_reglement = $conn->query($query_reglement);

$transactions_reg = [];  // Array for reglement
$total_deposit = 0;

// Calculate total deposit for reglement where rest = 0
$query_total_deposit = "SELECT SUM(reg) as total_deposit FROM reglement WHERE agence = $id AND rest = 0";
$result_total_deposit = $conn->query($query_total_deposit);
if ($result_total_deposit && $result_total_deposit->num_rows > 0) {
    $total_deposit = $result_total_deposit->fetch_assoc()['total_deposit'];
}

// Reg
if ($result_reglement && $result_reglement->num_rows > 0) {
    while ($row = $result_reglement->fetch_assoc()) {
        $transactions_reg[] = [
            'date' => htmlspecialchars($row['reg_on']),
            'amount' => htmlspecialchars($row['reg']) . " " . htmlspecialchars($row['currency']),
            'comment' => htmlspecialchars($row['comment']),
        ];
    }
}

// Fetch total number of reglement rows for pagination
$query_total_reg = "SELECT COUNT(*) as total FROM reglement WHERE agence = $id";
$result_total_reg = $conn->query($query_total_reg);
$total_rows_reg = $result_total_reg->fetch_assoc()['total'];
$total_pages_reg = ceil($total_rows_reg / $rows_per_page);

// Fetch garantie details with pagination and ORDER BY gar_on DESC
$query_gar = "SELECT gar_on, gar, currency, comment FROM garantie WHERE agence = $id ORDER BY gar_on DESC LIMIT $rows_per_page OFFSET $offset_gar";
$result_gar = $conn->query($query_gar);

$transactions_gar = [];  // Array for garantie
$total_deposit_gar = 0;

// Calculate total deposit for garantie
$query_total_deposit_gar = "SELECT SUM(gar) as total_deposit_gar FROM garantie WHERE agence = $id";
$result_total_deposit_gar = $conn->query($query_total_deposit_gar);
if ($result_total_deposit_gar && $result_total_deposit_gar->num_rows > 0) {
    $total_deposit_gar = $result_total_deposit_gar->fetch_assoc()['total_deposit_gar'];
}

// Gar
if ($result_gar && $result_gar->num_rows > 0) {
    while ($row = $result_gar->fetch_assoc()) {
        $transactions_gar[] = [
            'date' => htmlspecialchars($row['gar_on']),
            'amount' => htmlspecialchars($row['gar']) . " " . htmlspecialchars($row['currency']),
            'comment' => htmlspecialchars($row['comment']),
        ];
    }
}

// Fetch total number of garantie rows for pagination
$query_total_gar = "SELECT COUNT(*) as total FROM garantie WHERE agence = $id";
$result_total_gar = $conn->query($query_total_gar);
$total_rows_gar = $result_total_gar->fetch_assoc()['total'];
$total_pages_gar = ceil($total_rows_gar / $rows_per_page);
?>


<!DOCTYPE html>
<html lang="en">
  
<!-- Mirrored from thetheme.io/theadmin/samples/invoicer/index.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 13 Jan 2025 09:38:43 GMT -->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="TheAdmin - Responsive admin and web application ui kit">
    <meta name="description" content="TheAdmin - Responsive admin and web application ui kit">
    <meta name="keywords" content="admin, dashboard, web app, sass, ui kit, ui framework, bootstrap">

<title>Comptabilité &mdash; </title>
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

          <li class="menu-item  active">
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
          <a class="logo d-lg-none" href=""><img src="assets/img/                    " alt="logo"></a>

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
      <div class="row">
      <div class="row">
  <div class="col-12 col-md-5">
    <div class="card">
      <div class="card-header reglement-header">
        <h5 class="card-title"><strong>Réglments | <?= $nom_agence ?></strong></h5>
      </div>
      <div class="card-body">
        <h6><strong>Nouveau Règlement</strong></h6>
        <hr>
        <div class="form-group">
          <label for="montant">Montant</label>
          <input type="number" class="form-control" id="montant" placeholder="Montant">
        </div>
        <div class="form-group">
          <label for="commentaire">Commentaire</label>
          <textarea class="form-control" id="commentaire" rows="3" placeholder="Libellé"></textarea>
        </div>
        <button type="button" id="add-reglement" class="btn btn-primary">Ajouter</button>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-5">
    <div class="card">
      <div class="card-header garantie-header">
        <h5 class="card-title"><strong>Garanties | <?= $nom_agence ?></strong></h5>
      </div>
      <div class="card-body">
        <h6><strong>Nouveau Garantie</strong></h6>
        <hr>
        <div class="form-group">
          <label for="montant-garantie">Montant</label>
          <input type="number" class="form-control" id="montant-garantie" placeholder="Montant">
        </div>
        <div class="form-group">
          <label for="commentaire-garantie">Commentaire</label>
          <textarea class="form-control" id="commentaire-garantie" rows="3" placeholder="Libellé"></textarea>
        </div>
        <button type="button" id="add-garantie" class="btn btn-primary">Ajouter</button>
      </div>
    </div>
  </div>
</div>

<style>
.reglement-header {
    background-color: #ae0000;
    color: white;
    font-weight: bold; /* Make the text bold */
}

.garantie-header {
    background-color: green;
    color: white;
    font-weight: bold; /* Make the text bold */
}


</style>
<script>
    document.getElementById('add-reglement').addEventListener('click', function () {
        const montant = document.getElementById('montant').value;
        const commentaire = document.getElementById('commentaire').value;
        const agenceId = <?= $id ?>;

        if (!montant || !commentaire) {
            alert('Veuillez remplir tous les champs.');
            return;
        }

        // Send data to the server using AJAX
        fetch('add_reg.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ montant, commentaire, agenceId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Règlement ajouté avec succès.');
                // Reload the page or update the table dynamically
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>
<script>
    document.getElementById('add-garantie').addEventListener('click', function () {
        const montantGarantie = document.getElementById('montant-garantie').value;
        const commentaireGarantie = document.getElementById('commentaire-garantie').value;
        const agenceId = <?= $id ?>;

        if (!montantGarantie || !commentaireGarantie) {
            alert('Veuillez remplir tous les champs.');
            return;
        }

        // Send data to the server using AJAX
        fetch('add_gar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 'montant-garantie': montantGarantie, 'commentaire-garantie': commentaireGarantie, agenceId })
          })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Garantie ajoutée avec succès.');
                // Reload the page or update the table dynamically
                location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>



<!-- Reglements Section -->
<div class="col-lg-5">
    <div class="card">
        <div class="card-header garantie-header-2">
            <h5 class="card-title"><strong>Montant Déposé:</strong> 
                <span class="text-success fw-bold">
                <?= number_format($total_deposit, 2) ?> <?= htmlspecialchars(isset($transactions_reg[0]['currency']) ? $transactions_reg[0]['currency'] : 'EUR') ?>
              </span>
            </h5>
        </div>
        <div class="card-body">
            <h5 class="card-title"><strong>Les règlements</strong> récents</h5>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date de Réglement</th>
                        <th>Montant</th>
                        <th>Commentaire</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions_reg) > 0): ?>
                        <?php foreach ($transactions_reg as $transaction): ?>
                            <tr>
                                <td><?= $transaction['date'] ?></td>
                                <td class="text-success fw-500">+ <?= $transaction['amount'] ?></td>
                                <td><?= $transaction['comment'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                        <td colspan="3">Aucune transaction récente trouvée.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <!-- Pagination for Reglement -->
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages_reg; $i++): ?>
                        <li class="page-item <?= ($i == $page_reg) ? 'active' : '' ?>">
                            <a style="color : black; font-weight : bold;" class="page-link" href="?pid=<?= $id ?>&page_reg=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Garanties Section -->
<div class="col-lg-5">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><strong>Montant Garanti:</strong> 
                <span class="text-success fw-bold">
                <?= number_format($total_deposit_gar, 2) ?> <?= htmlspecialchars(isset($transactions_gar[0]['currency']) ? $transactions_gar[0]['currency'] : 'EUR') ?>
              </span>
            </h5>
        </div>
        <div class="card-body">
            <h5 class="card-title"><strong>Les Garanties</strong> récents</h5>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date de Deposition</th>
                        <th>Montant</th>
                        <th>Commentaire</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions_gar) > 0): ?>
                        <?php foreach ($transactions_gar as $transaction): ?>
                            <tr>
                                <td><?= $transaction['date'] ?></td>
                                <td class="text-success fw-500">+ <?= $transaction['amount'] ?></td>
                                <td><?= $transaction['comment'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                        <td colspan="3">Aucune transaction récente trouvée.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <!-- Pagination for Garantie -->
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages_gar; $i++): ?>
                        <li class="page-item <?= ($i == $page_gar) ? 'active' : '' ?>">
                            <a style="color : black; font-weight : bold;" class="page-link" href="?pid=<?= $id ?>&page_gar=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>
<!--/.main-content -->

                    </div>

     
    </main>
    <!-- END Main container -->



    <!-- Global quickview -->
    <div id="qv-global" class="quickview" data-url="http://thetheme.io/theadmin/samples/assets/data/quickview-global.html">
      <div class="spinner-linear">
        <div class="line"></div>
      </div>
    </div>
    <!-- END Global quickview -->



    <!-- Scripts -->
    <script src="assets/js/core.min.js"></script>
    <script src="assets/js/app.min.js"></script>
    <script src="assets/js/script.js"></script>

  </body>

<!-- Mirrored from thetheme.io/theadmin/samples/invoicer/index.php by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 13 Jan 2025 09:38:52 GMT -->
</html>
