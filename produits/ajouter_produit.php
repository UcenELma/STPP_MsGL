<?php
// ajouter_produit.php
require_once __DIR__ . '/../config.php';

if (!isset($_POST['nom'], $_POST['type'], $_POST['qte'], $_POST['date_production'], $_POST['date_peremption'], $_POST['fournisseur_id'])) {
    die('Données manquantes.');
}

$nom = trim($_POST['nom']);
$type = trim($_POST['type']);
$qte = (int) $_POST['qte'];
$date_production = $_POST['date_production'];
$date_peremption = $_POST['date_peremption'];
$fournisseur_id = (int) $_POST['fournisseur_id'];

$stmt = $db->prepare("INSERT INTO produits (nom, type, qte, date_production, date_peremption, fournisseur_id) VALUES (?, ?, ?, ?, ?, ?)");

try {
    $stmt->execute([$nom, $type, $qte, $date_production, $date_peremption, $fournisseur_id]);
    header('Location: ../index.php?page=produits/liste_produits');
    exit;
} catch (PDOException $e) {
    echo "Erreur lors de l'insertion : " . $e->getMessage();
}
?>
