<!-- STPP_Backend/login_process.php -->
<?php
session_start();
// require_once 'config.php';
require_once(__DIR__ . '/../STPP_Database/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mot_de_passe_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        switch ($user['role']) {
            case 'admin':
                header("Location: ../STPP_Frontend/index.php");
                break;
            case 'fournisseur':
                header("Location: ../STPP_Frontend/fournisseur.php");
                break;
            case 'transporteur':
                header("Location: ../STPP_Frontend/transporteur.php");
                break;
            default:
                $_SESSION['error'] = "Rôle inconnu.";
                header("Location: ../STPP_Frontend/login.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Email ou mot de passe incorrect.";
        header("Location: ../STPP_Frontend/login.php");
        exit();
    }
}
?>
