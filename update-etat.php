<?php
// Check if the transaction_id and new_etat are set in the POST request
if (isset($_POST['transaction_id']) && isset($_POST['new_etat'])) {
    $transaction_id = $_POST['transaction_id'];
    $new_etat = $_POST['new_etat'];

    // Include database connection
    include 'files/db_connection.php';

    // Update the etat_reg in the database
    $update_query = "UPDATE reglement SET etat_reg = ? WHERE pid = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $new_etat, $transaction_id);

    if ($update_stmt->execute()) {
        echo "Success";
    } else {
        echo "Error updating etat_reg.";
    }
} else {
    echo "Missing parameters.";
}
?>
