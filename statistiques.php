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
?>
