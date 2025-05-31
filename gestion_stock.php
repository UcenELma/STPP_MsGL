<!-- gestion_stock.php -->
<?php
require 'config.php';

/* --- Fonctions --- */

function ajouterEntrepot(PDO $pdo, string $nom) {
    $stmt = $pdo->prepare("INSERT INTO entrepots (nom) VALUES (:nom)");
    $stmt->execute(['nom' => $nom]);
}

function stockerProduit(PDO $pdo, int $produit_id, int $entrepot_id, int $qte) {
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

function deplacerProduit(PDO $pdo, int $produit_id, int $source_entrepot_id, int $dest_entrepot_id, int $qte) {
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
            (int)$_POST['produit_id'],
            (int)$_POST['entrepot_id'],
            (int)$_POST['qte']
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
            (int)$_POST['produit_id'],
            (int)$_POST['source_entrepot_id'],
            (int)$_POST['dest_entrepot_id'],
            (int)$_POST['qte']
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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion de Stock</title>



    <!-- FontAwesome & jQuery (optionnel selon vos besoins) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div>
    <h1 class="h3 mb-4 text-gray-800">Gestion de Stock</h1>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Ajouter un entrepôt -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ajouter un nouvel entrepôt</h6>
        </div>
        <div class="card-body">
            <form method="post" class="form-inline">
                <label for="entrepot_nom" class="mr-2">Nom de l'entrepôt :</label>
                <input type="text" id="entrepot_nom" name="entrepot_nom" class="form-control mr-2" required>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- Stocker un produit -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">Stocker un produit (Entrée stock)</h6>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-4">
                    <label for="produit_id">Produit :</label>
                    <select name="produit_id" id="produit_id" class="form-control" required>
                        <option value="">-- Choisir un produit --</option>
                        <?php foreach ($produits as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['nom']) ?> (Dispo: <?= $p['qte'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="entrepot_id">Entrepôt :</label>
                    <select name="entrepot_id" id="entrepot_id" class="form-control" required>
                        <option value="">-- Choisir un entrepôt --</option>
                        <?php foreach ($entrepots as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="qte">Quantité :</label>
                    <input type="number" min="1" name="qte" id="qte" class="form-control" required>
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-success w-100">Stocker</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Déplacement entre entrepôts -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-warning">Déplacer un produit entre deux entrepôts</h6>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-3">
                    <label for="produit_id_depl">Produit :</label>
                    <select name="produit_id" id="produit_id_depl" class="form-control" required>
                        <option value="">-- Choisir un produit --</option>
                        <?php foreach ($produits as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="source_entrepot_id">Entrepôt source :</label>
                    <select name="source_entrepot_id" id="source_entrepot_id" class="form-control" required>
                        <option value="">-- Choisir un entrepôt --</option>
                        <?php foreach ($entrepots as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dest_entrepot_id">Entrepôt destination :</label>
                    <select name="dest_entrepot_id" id="dest_entrepot_id" class="form-control" required>
                        <option value="">-- Choisir un entrepôt --</option>
                        <?php foreach ($entrepots as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="qte_depl">Quantité :</label>
                    <input type="number" min="1" name="qte" id="qte_depl" class="form-control" required>
                </div>
                <div class="col-md-1 align-self-end">
                    <button type="submit" class="btn btn-warning">Déplacer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Historique -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-info">Historique des mouvements</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="filtre_produit">Filtrer par produit :</label>
                    <select name="produit" id="filtre_produit" class="form-control">
                        <option value="">-- Tous les produits --</option>
                        <?php foreach ($produits as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($filtreProduit == $p['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filtre_entrepot">Filtrer par entrepôt :</label>
                    <select name="entrepot" id="filtre_entrepot" class="form-control">
                        <option value="">-- Tous les entrepôts --</option>
                        <?php foreach ($entrepots as $e): ?>
                            <option value="<?= $e['id'] ?>" <?= ($filtreEntrepot == $e['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 align-self-end">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="index.php?page=gestion_stock" class="btn btn-secondary">Réinitialiser</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Produit</th>
                            <th>Entrepôt source</th>
                            <th>Entrepôt destination</th>
                            <th>Quantité</th>
                            <th>Date du mouvement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($mouvements) === 0): ?>
                            <tr><td colspan="5" class="text-center">Aucun mouvement trouvé.</td></tr>
                        <?php else: ?>
                            <?php foreach ($mouvements as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['produit']) ?></td>
                                    <td><?= $m['source'] ?? '<em>Entrée stock</em>' ?></td>
                                    <td><?= htmlspecialchars($m['destination']) ?></td>
                                    <td><?= (int)$m['qte'] ?></td>
                                    <td><?= htmlspecialchars($m['date_mouvement']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</body>
</html>

