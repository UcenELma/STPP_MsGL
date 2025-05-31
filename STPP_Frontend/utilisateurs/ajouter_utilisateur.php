<!-- ajouter_utilisateur.php -->
<meta charset="utf-8"/>
<?php
// require_once './../config.php';
require_once (__DIR__ . './../../STPP_Database/config.php');

// Vérification basique des champs (à améliorer côté front et back)
if (!isset($_POST['nom'], $_POST['email'], $_POST['mot_de_passe'], $_POST['role'])) {
    die('Données manquantes.');
}

// Préparer les données
$nom = $_POST['nom'];
$email = $_POST['email'];
$mot_de_passe = $_POST['mot_de_passe'];
$role = $_POST['role'];

// Hasher le mot de passe (avec password_hash, recommandation PHP)
$mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

// Préparer la requête
$stmt = $db->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe_hash, role) VALUES (?, ?, ?, ?)");

try {
    $stmt->execute([$nom, $email, $mot_de_passe_hash, $role]);
    header('Location: ../index.php?page=utilisateurs/liste_utilisateurs');
    exit;
} catch (PDOException $e) {
    // Gestion d'erreur simple
    echo "Erreur lors de l'insertion : " . $e->getMessage();
}
?>
