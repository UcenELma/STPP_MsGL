<!-- STPP_Backend/navbar_backend.php -->
<?php

require_once(__DIR__ . '/../STPP_Database/config.php');

// Récupération des alertes

// Produits en faible stock (qte < 10)
$alertLowStockStmt = $db->prepare("
    SELECT nom, qte 
    FROM produits 
    WHERE qte < 10
    ORDER BY qte ASC
    LIMIT 5
");
$alertLowStockStmt->execute();
$lowStockAlerts = $alertLowStockStmt->fetchAll(PDO::FETCH_ASSOC);

// Produits proches de la date de péremption (dans 15 jours ou moins)
$alertNearExpiryStmt = $db->prepare("
    SELECT nom, date_peremption 
    FROM produits 
    WHERE date_peremption <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)
    ORDER BY date_peremption ASC
    LIMIT 5
");
$alertNearExpiryStmt->execute();
$nearExpiryAlerts = $alertNearExpiryStmt->fetchAll(PDO::FETCH_ASSOC);

// Total alertes pour badge
$totalAlertsCount = count($lowStockAlerts) + count($nearExpiryAlerts);
