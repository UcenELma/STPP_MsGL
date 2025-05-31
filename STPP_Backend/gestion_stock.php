<!-- STPP_Backend/gestion_stock.php -->
<?php
require_once(__DIR__ . '/../STPP_Database/config.php');


function ajouterEntrepot(PDO $pdo, string $nom)
{
    $stmt = $pdo->prepare("INSERT INTO entrepots (nom) VALUES (:nom)");
    $stmt->execute(['nom' => $nom]);
}

function stockerProduit(PDO $pdo, int $produit_id, int $entrepot_id, int $qte)
{
    // Vérifier la quantité disponible dans produits
    $stmt = $pdo->prepare("SELECT qte FROM produits WHERE id = :produit_id");
    $stmt->execute(['produit_id' => $produit_id]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produit) {
        return "Produit non trouvé.";
    }

    if ($produit['qte'] < $qte) {
        return "Quantité demandée ($qte) dépasse la quantité disponible ({$produit['qte']}).";
    }

    // Vérifier s'il y a déjà du stock pour ce produit dans cet entrepôt
    $stmt = $pdo->prepare("SELECT * FROM stock_entrepot WHERE produit_id = :produit_id AND entrepot_id = :entrepot_id");
    $stmt->execute(['produit_id' => $produit_id, 'entrepot_id' => $entrepot_id]);
    $stock = $stmt->fetch();

    if ($stock) {
        $stmt = $pdo->prepare("UPDATE stock_entrepot SET qte = qte + :qte WHERE produit_id = :produit_id AND entrepot_id = :entrepot_id");
    } else {
        $stmt = $pdo->prepare("INSERT INTO stock_entrepot (produit_id, entrepot_id, qte) VALUES (:produit_id, :entrepot_id, :qte)");
    }
    $stmt->execute(['produit_id' => $produit_id, 'entrepot_id' => $entrepot_id, 'qte' => $qte]);

    // Mettre à jour la quantité disponible dans produits
    $stmt = $pdo->prepare("UPDATE produits SET qte = qte - :qte WHERE id = :produit_id");
    $stmt->execute(['qte' => $qte, 'produit_id' => $produit_id]);

    // Enregistrer le mouvement (source_entrepot_id = NULL car c'est une entrée depuis "zone de réception")
    $stmt = $pdo->prepare("INSERT INTO mouvements_produits 
        (produit_id, source_entrepot_id, destination_entrepot_id, qte)
        VALUES (:produit_id, NULL, :destination_entrepot_id, :qte)");
    $stmt->execute([
        'produit_id' => $produit_id,
        'destination_entrepot_id' => $entrepot_id,
        'qte' => $qte
    ]);

    return true;
}

function deplacerProduit(PDO $pdo, int $produit_id, int $source_entrepot_id, int $dest_entrepot_id, int $qte)
{
    if ($source_entrepot_id === $dest_entrepot_id) {
        return "L'entrepôt source et destination doivent être différents.";
    }

    // Vérifier la quantité disponible dans l'entrepôt source
    $stmt = $pdo->prepare("SELECT qte FROM stock_entrepot WHERE produit_id = :produit_id AND entrepot_id = :entrepot_id");
    $stmt->execute(['produit_id' => $produit_id, 'entrepot_id' => $source_entrepot_id]);
    $stockSource = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stockSource || $stockSource['qte'] < $qte) {
        return "Quantité insuffisante dans l'entrepôt source.";
    }

    // Déduire la quantité dans l'entrepôt source
    $stmt = $pdo->prepare("UPDATE stock_entrepot SET qte = qte - :qte WHERE produit_id = :produit_id AND entrepot_id = :entrepot_id");
    $stmt->execute(['qte' => $qte, 'produit_id' => $produit_id, 'entrepot_id' => $source_entrepot_id]);

    // Supprimer la ligne si quantité devient zéro
    $stmt = $pdo->prepare("DELETE FROM stock_entrepot WHERE produit_id = :produit_id AND entrepot_id = :entrepot_id AND qte <= 0");
    $stmt->execute(['produit_id' => $produit_id, 'entrepot_id' => $source_entrepot_id]);

    // Ajouter la quantité dans l'entrepôt destination
    $stmt = $pdo->prepare("SELECT qte FROM stock_entrepot WHERE produit_id = :produit_id AND entrepot_id = :entrepot_id");
    $stmt->execute(['produit_id' => $produit_id, 'entrepot_id' => $dest_entrepot_id]);
    $stockDest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stockDest) {
        $stmt = $pdo->prepare("UPDATE stock_entrepot SET qte = qte + :qte WHERE produit_id = :produit_id AND entrepot_id = :entrepot_id");
    } else {
        $stmt = $pdo->prepare("INSERT INTO stock_entrepot (produit_id, entrepot_id, qte) VALUES (:produit_id, :entrepot_id, :qte)");
    }
    $stmt->execute(['produit_id' => $produit_id, 'entrepot_id' => $dest_entrepot_id, 'qte' => $qte]);

    // Enregistrer le mouvement avec source et destination
    $stmt = $pdo->prepare("INSERT INTO mouvements_produits 
        (produit_id, source_entrepot_id, destination_entrepot_id, qte)
        VALUES (:produit_id, :source_entrepot_id, :destination_entrepot_id, :qte)");
    $stmt->execute([
        'produit_id' => $produit_id,
        'source_entrepot_id' => $source_entrepot_id,
        'destination_entrepot_id' => $dest_entrepot_id,
        'qte' => $qte
    ]);

    return true;
}

/* --- Gestion POST --- */
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['entrepot_nom'])) {
        ajouterEntrepot($pdo, trim($_POST['entrepot_nom']));
        $message = "Entrepôt ajouté avec succès.";
    } elseif (isset($_POST['produit_id'], $_POST['entrepot_id'], $_POST['qte']) && !isset($_POST['source_entrepot_id'])) {
        // Stocker produit (entrée stock)
        $result = stockerProduit(
            $pdo,
            (int) $_POST['produit_id'],
            (int) $_POST['entrepot_id'],
            (int) $_POST['qte']
        );
        if ($result === true) {
            $message = "Produit stocké avec succès.";
        } else {
            $message = $result;  // Message d'erreur retourné
        }
    } elseif (isset($_POST['produit_id'], $_POST['source_entrepot_id'], $_POST['dest_entrepot_id'], $_POST['qte'])) {
        // Déplacer produit entre entrepôts
        $result = deplacerProduit(
            $pdo,
            (int) $_POST['produit_id'],
            (int) $_POST['source_entrepot_id'],
            (int) $_POST['dest_entrepot_id'],
            (int) $_POST['qte']
        );
        if ($result === true) {
            $message = "Produit déplacé avec succès.";
        } else {
            $message = $result;
        }
    }
}

/* --- Récupération des données --- */
$produits = $pdo->query("SELECT id, nom, qte FROM produits ORDER BY nom")->fetchAll();
$entrepots = $pdo->query("SELECT id, nom FROM entrepots ORDER BY nom")->fetchAll();

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