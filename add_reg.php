<?php
require_once 'files/db_connection.php';

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['montant'], $data['commentaire'], $data['agenceId'])) {
    echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
    exit;
}

$montant = floatval($data['montant']);
$commentaire = htmlspecialchars($data['commentaire']);
$agenceId = intval($data['agenceId']);

// Validate inputs
if ($montant <= 0 || empty($commentaire) || $agenceId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

// Function to generate a unique reg_ref
function generateUniqueRegRef($conn) {
    do {
        // Generate a random 5-digit number
        $randomNumber = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
        $regRef = "REG_" . $randomNumber;

        // Check if reg_ref already exists
        $checkQuery = "SELECT COUNT(*) AS count FROM reglement WHERE reg_ref = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param('s', $regRef);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

    } while ($count > 0); // Keep generating if the reg_ref exists

    return $regRef;
}

// Generate unique reg_ref
$regRef = generateUniqueRegRef($conn);

// Insert the new reglement record with reg_ref and etat_payment set to 0
$query = "INSERT INTO reglement (agence, reg, comment, reg_on, currency, etat_reg, reg_ref, etat_payment) 
          VALUES (?, ?, ?, NOW(), 'EUR', 'non encaissé', ?, 0)";
$stmt = $conn->prepare($query);
$stmt->bind_param('idss', $agenceId, $montant, $commentaire, $regRef);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'insertion des données.']);
}

$stmt->close();
$conn->close();

?>
