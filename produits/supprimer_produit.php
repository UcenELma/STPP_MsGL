<?php
ob_start();

require_once __DIR__ . '/../config.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $db->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->execute([$id]);

    // Affiche un script JS qui alerte et redirige après l'alerte
    echo "<script>
        alert('Suppression effectuée avec succès.');
        window.location.href = '../index.php?page=produits/liste_produits';
    </script>";
    exit;
} else {
    echo "Aucun produit spécifié pour suppression.";
    exit;
}

ob_end_flush();
