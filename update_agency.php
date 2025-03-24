<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $login = $_POST['login'];
    $tel = $_POST['tel'];
    $fax = $_POST['fax'];
    $adresse = $_POST['adresse'];
    $code_postal = $_POST['code_postal'];
    $code_pays = $_POST['code_pays'];
    $ville = $_POST['ville'];
    $reg_commerce = $_POST['reg_commerce'];
    $num_fiscal = $_POST['num_fiscal'];
    $code_iata = $_POST['code_iata'];
    $num_licence = $_POST['num_licence'];
    $etat = $_POST['etat'];
    $markup = $_POST['markup'];

    $query = "UPDATE agence 
              SET nom_agence = ?, email = ?, login = ?, tel = ?, fax = ?, 
                  adresse = ?, code_postal = ?, code_pays = ?, ville = ?, 
                  reg_commerce = ?, num_fiscal = ?, code_iata = ?, num_licence = ?, 
                  etat = ?, markup = ? 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        'ssssssssssssssss',
        $nom, $email, $login, $tel, $fax, 
        $adresse, $code_postal, $code_pays, $ville, 
        $reg_commerce, $num_fiscal, $code_iata, $num_licence, 
        $etat, $markup, $id
    );

    if ($stmt->execute()) {
        echo "Agency updated successfully.";
    } else {
        echo "Error updating agency: " . $stmt->error;
    }
}
