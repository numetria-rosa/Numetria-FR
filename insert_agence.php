<?php
require_once "files/db_connection.php"; // Include your existing DB connection file

// Retrieve POST data
$nom_agence = $_POST['nom_agence'];
$email = $_POST['email'];
$tel = $_POST['tel'];
$ville = $_POST['ville'];
$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$email_client = $_POST['email_client'];
$tel_client = $_POST['tel_client'];

// Prepare and execute the SQL statement
$sql = "INSERT INTO agence (nom_agence, email, tel, ville, nom, prenom, email_client, tel_client, etat)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";  // Set etat to 1 directly

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $nom_agence, $email, $tel, $ville, $nom, $prenom, $email_client, $tel_client);

if ($stmt->execute()) {
    echo "Données insérées avec succès!";
} else {
    echo "Erreur: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
