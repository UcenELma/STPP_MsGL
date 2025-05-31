<?php
require_once(__DIR__ . '/../STPP_Database/config.php');

// Quantité totale de tous les produits
$stmt = $pdo->query("SELECT SUM(qte) AS total_qte FROM produits");
$total_qte = $stmt->fetch(PDO::FETCH_ASSOC)['total_qte'] ?? 0;

// Nombre total de produits
$stmt = $pdo->query("SELECT COUNT(*) AS total_produits FROM produits");
$total_produits = $stmt->fetch(PDO::FETCH_ASSOC)['total_produits'] ?? 0;

// Nombre de produits avec qte < 10
$stmt = $pdo->query("SELECT COUNT(*) AS faibles FROM produits WHERE qte < 10");
$produits_faibles = $stmt->fetch(PDO::FETCH_ASSOC)['faibles'] ?? 0;

// Produits proches de la péremption (dans 7 jours)
$stmt = $pdo->query("
    SELECT COUNT(*) AS proches 
    FROM produits 
    WHERE DATEDIFF(date_peremption, CURDATE()) BETWEEN 0 AND 7
");
$produits_proches_peremption = $stmt->fetch(PDO::FETCH_ASSOC)['proches'] ?? 0;

// Produits déjà périmés
$stmt = $pdo->query("
    SELECT COUNT(*) AS perimes 
    FROM produits 
    WHERE date_peremption < CURDATE()
");
$produits_perimes = $stmt->fetch(PDO::FETCH_ASSOC)['perimes'] ?? 0;

// Quantité totale de stock par entrepôt
$sql = "
    SELECT e.nom AS entrepot, SUM(se.qte) AS total_qte
    FROM stock_entrepot se
    JOIN entrepots e ON se.entrepot_id = e.id
    GROUP BY e.nom
    ORDER BY e.nom
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$quantities = [];
foreach ($stocks as $stock) {
    $labels[] = $stock['entrepot'];
    $quantities[] = (int)$stock['total_qte'];
}

// Récupération des filtres GET
$filtreProduit = $_GET['produit'] ?? '';
$filtreEntrepot = $_GET['entrepot'] ?? '';

$sql = "SELECT 
            p.nom AS produit,
            e1.nom AS source,
            e2.nom AS destination,
            m.qte,
            m.date_mouvement
        FROM mouvements_produits m
        JOIN produits p ON m.produit_id = p.id
        LEFT JOIN entrepots e1 ON m.source_entrepot_id = e1.id
        JOIN entrepots e2 ON m.destination_entrepot_id = e2.id
        WHERE 1=1 ";

$params = [];

if ($filtreProduit !== '') {
    $sql .= " AND p.id = :produit ";
    $params[':produit'] = $filtreProduit;
}

if ($filtreEntrepot !== '') {
    $sql .= " AND (m.source_entrepot_id = :entrepot OR m.destination_entrepot_id = :entrepot) ";
    $params[':entrepot'] = $filtreEntrepot;
}

$sql .= " ORDER BY m.date_mouvement DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mouvements = $stmt->fetchAll(PDO::FETCH_ASSOC);
