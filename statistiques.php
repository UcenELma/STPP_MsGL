<!-- statistiques.php -->
<?php
require_once 'config.php';

// Quantité totale de tous les produits
$stmt = $db->query("SELECT SUM(qte) AS total_qte FROM produits");
$total_qte = $stmt->fetch(PDO::FETCH_ASSOC)['total_qte'] ?? 0;

// Nombre total de produits
$stmt = $db->query("SELECT COUNT(*) AS total_produits FROM produits");
$total_produits = $stmt->fetch(PDO::FETCH_ASSOC)['total_produits'] ?? 0;

// Nombre de produits avec qte < 10
$stmt = $db->query("SELECT COUNT(*) AS faibles FROM produits WHERE qte < 10");
$produits_faibles = $stmt->fetch(PDO::FETCH_ASSOC)['faibles'] ?? 0;

// Produits proches de la péremption (dans 7 jours)
$stmt = $db->query("
    SELECT COUNT(*) AS proches 
    FROM produits 
    WHERE DATEDIFF(date_peremption, CURDATE()) BETWEEN 0 AND 7
");
$produits_proches_peremption = $stmt->fetch(PDO::FETCH_ASSOC)['proches'] ?? 0;

// Produits déjà périmés (date_peremption < aujourd'hui)
$stmt = $db->query("
    SELECT COUNT(*) AS perimes 
    FROM produits 
    WHERE date_peremption < CURDATE()
");
$produits_perimes = $stmt->fetch(PDO::FETCH_ASSOC)['perimes'] ?? 0;

// Quantité de chaque produit
$stmt = $db->query("SELECT nom, qte FROM produits");
$quantite_par_produit = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la quantité totale de stock par entrepôt
$sql = "
    SELECT e.nom AS entrepot, SUM(se.qte) AS total_qte
    FROM stock_entrepot se
    JOIN entrepots e ON se.entrepot_id = e.id
    GROUP BY e.nom
    ORDER BY e.nom
";

$stmt = $db->prepare($sql);
$stmt->execute();
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparer les données pour JS (noms et quantités)
$labels = [];
$quantities = [];

foreach ($stocks as $stock) {
    $labels[] = $stock['entrepot'];
    $quantities[] = (int)$stock['total_qte'];
}

/* --- Récupération des filtres pour mouvements --- */
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
$mouvements = $stmt->fetchAll();

?>

<?php

// Total de produits
$stmt = $db->query("SELECT COUNT(*) FROM produits");
$total_produits = $stmt->fetchColumn();

// Produits faibles (ex: quantité <= seuil_min)
$stmt = $db->query("SELECT COUNT(*) FROM produits WHERE qte < 10");
$produits_faibles = $stmt->fetchColumn();

// Produits périmés (ex: date_expiration < aujourd'hui)
$stmt = $db->prepare("SELECT COUNT(*) FROM produits WHERE date_peremption < CURDATE()");
$stmt->execute();
$produits_perimes = $stmt->fetchColumn();

// Produits proches de péremption (ex: dans les 7 prochains jours)

$stmt = $db->prepare("SELECT COUNT(*) FROM produits WHERE date_peremption BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$stmt->execute();
$produits_proches_peremption = $stmt->fetchColumn();

// (Optionnel) Quantité totale
$sql = "
    SELECT e.nom AS entrepot, SUM(se.qte) AS total_qte
    FROM stock_entrepot se
    JOIN entrepots e ON se.entrepot_id = e.id
    GROUP BY e.nom
";

$stmt = $db->prepare($sql);
$stmt->execute();
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = array_column($stocks, 'entrepot');
$quantities = array_map('intval', array_column($stocks, 'total_qte'));

