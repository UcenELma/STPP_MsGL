<?php
// supprimer_utilisateur.php

// require_once __DIR__ . '/../config.php';
require_once (__DIR__ . './../../STPP_Database/config.php');

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);

    echo "<script>
        alert('Suppression de l\'utilisateur effectuée avec succès.');
        window.location.href = '../index.php?page=utilisateurs/liste_utilisateurs';
    </script>";
    exit;
} else {
    echo "Aucun utilisateur spécifié pour suppression.";
    exit;
}
