<?php
// Inclure la configuration de la base de données
require_once 'config.php';

// Date actuelle et seuil de péremption
$aujourdhui = date('Y-m-d');
$date_proche = date('Y-m-d', strtotime('+7 days'));

// Rechercher les produits avec quantité faible ou date périmée/proche
$sql = "SELECT * FROM produits 
        WHERE qte < 10 OR date_peremption <= :date_proche";
$stmt = $db->prepare($sql);
$stmt->execute(['date_proche' => $date_proche]);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($produits) > 0) {
    // Récupérer les emails des admins
    $stmtAdmins = $db->query("SELECT email FROM utilisateurs WHERE role = 'admin'");
    $emailsAdmins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);

    // Générer le message d’alerte
    $message = "Bonjour,\n\nVoici les produits à surveiller :\n\n";

    foreach ($produits as $p) {
        $message .= "Produit : {$p['nom']}\n";
        $message .= "Quantité : {$p['qte']}\n";
        $message .= "Date péremption : {$p['date_peremption']}\n";
        $message .= "-------------------------\n";
    }

    $message .= "\nMerci de votre vigilance.\n— Système de Traçabilité";

    // Envoi de l’alerte à tous les admins
    foreach ($emailsAdmins as $email) {
        mail($email, "⚠️ Alerte Stock ou Péremption Produits", $message);
    }
}
?>
