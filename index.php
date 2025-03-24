<?php
session_start();

// Prevent caching to ensure session is not cached in the browser
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

require_once 'files/db_connection.php';

$error_message = "";
$success_message = "";

// If session already exists, destroy it and ask to log in again
if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the inputs are not empty
    if (!empty($email) && !empty($password)) {
        // Prepare a statement to prevent SQL injection
        $query = "SELECT * FROM invoiceuser WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Directly compare the password (plaintext)
            if ($password === $user['password']) {
                // Save user information in the session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];  // Store the username in session
                $_SESSION['job_title'] = $user['job_title'];

                // Success message to show in the alert
                echo "<script>
                    window.location.href = 'dashboard.php';  // Redirect to dashboard.php
                </script>";
                exit;
            } else {
                $error_message = "Mot de passe invalide.";
            }
        } else {
            $error_message = "Aucun utilisateur trouvé avec cet email.";
        }
    } else {
        $error_message = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Page de connexion</title>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="./style_login.css">
   
</head>

<body>
    <div class="screen-1">
        <!-- Add the logo image here -->
        <div style="text-align: center; margin-bottom: 20px;">
        <img src="assets/img/numetria3.png" alt="DMC Logo" style="max-width: 300px; margin-top:50px; margin-buttom:200px; margin-right:30px;">
        </div>

        <form method="POST" action="">
            <div class="email" style="margin-bottom: 20px;">
                <label for="email">Adresse email</label>
                <div class="sec-2">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input type="email" name="email" placeholder="Username@gmail.com" required />
                </div>
            </div>
            <div class="password" style="margin-bottom: 20px;">
                <label for="password">Mot de passe</label>
                <div class="sec-2">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input class="pas" id="password" type="password" name="password" placeholder="············" required />
                    <ion-icon id="toggle-icon" name="eye-off-outline" onclick="togglePassword()"></ion-icon>
                </div>
            </div>

            <button class="login" type="submit" style="margin-top: 20px; width: 100%; max-width: 300px;">
                Se connecter
            </button>
        </form>
    </div>

    <?php if (!empty($error_message)): ?>
        <script>
            alert('<?php echo $error_message; ?>');
        </script>
    <?php endif; ?>
    <script>
        function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggle-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.name = 'eye-outline'; // Change the icon to "eye"
    } else {
        passwordInput.type = 'password';
        toggleIcon.name = 'eye-off-outline'; // Change the icon back to "eye-off"
    }
}
    </script>
</body>
</html>
