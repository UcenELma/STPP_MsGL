<?php
// ajouter_produit.php
require_once __DIR__ . '/../config.php';


// Vérifier que tous les champs sont présents
if (!isset($_POST['nom'], $_POST['type'], $_POST['date_production'], $_POST['date_peremption'], $_POST['fournisseur_id'])) {
    die('Données manquantes.');
}

// Récupérer et sécuriser les données
$nom = trim($_POST['nom']);
$type = trim($_POST['type']);
$date_production = $_POST['date_production'];
$date_peremption = $_POST['date_peremption'];
$fournisseur_id = (int) $_POST['fournisseur_id'];

// Insertion dans la table produits
$stmt = $db->prepare("INSERT INTO produits (nom, type, date_production, date_peremption, fournisseur_id) VALUES (?, ?, ?, ?, ?)");

try {
    $stmt->execute([$nom, $type, $date_production, $date_peremption, $fournisseur_id]);
    // Redirection vers la liste des produits
    header('Location: ../index.php?page=produits/liste_produits');
    exit;
} catch (PDOException $e) {
    echo "Erreur lors de l'insertion : " . $e->getMessage();
}
