<?php
require_once 'files/db_connection.php';

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['montant-garantie'], $data['commentaire-garantie'], $data['agenceId'])) {
    echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
    exit;
}

$montant = floatval($data['montant-garantie']);
$commentaire = htmlspecialchars($data['commentaire-garantie']);
$agenceId = intval($data['agenceId']);

// Validate inputs
if ($montant <= 0 || empty($commentaire) || $agenceId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

// Insert the new garantie record
$query = "INSERT INTO garantie (agence, gar, gar_on, comment, superworker, etat_gar) 
          VALUES (?, ?, NOW(), ?, 'SuperWorkerName', 'consomé')";
$stmt = $conn->prepare($query);
$stmt->bind_param('ids', $agenceId, $montant, $commentaire);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'insertion des données.']);
}

$stmt->close();
$conn->close();
?>
