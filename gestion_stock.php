<?php
require 'config.php';

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

    return true;
}

// Gestion des messages d'erreur ou succès
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['entrepot_nom'])) {
        ajouterEntrepot($pdo, $_POST['entrepot_nom']);
        $message = "Entrepôt ajouté avec succès.";
    } elseif (isset($_POST['produit_id'], $_POST['entrepot_id'], $_POST['qte'])) {
        $result = stockerProduit($pdo, (int)$_POST['produit_id'], (int)$_POST['entrepot_id'], (int)$_POST['qte']);
        if ($result === true) {
            $message = "Produit stocké avec succès.";
        } else {
            $message = $result;  // Message d'erreur retourné
        }
    }
}

// Récupération des produits et entrepôts
$produits = $pdo->query("SELECT id, nom, qte FROM produits")->fetchAll();
$entrepots = $pdo->query("SELECT id, nom FROM entrepots")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion de Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body">

    <h1 class="mb-4">Gestion de Stock</h1>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Formulaire ajout entrepôt -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ajouter un entrepôt</h6>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <input type="text" class="form-control" name="entrepot_nom" placeholder="Nom de l'entrepôt" required>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    Ajouter
                </button>
            </form>
        </div>
    </div>

    <!-- Formulaire stocker produit -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Stocker un produit</h6>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <select class="form-select" name="produit_id" required>
                        <option value="">-- Choisir un produit --</option>
                        <?php foreach ($produits as $produit): ?>
                            <option value="<?= $produit['id'] ?>">
                                <?= htmlspecialchars($produit['nom']) ?> (Disponible: <?= $produit['qte'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <select class="form-select" name="entrepot_id" required>
                        <option value="">-- Choisir un entrepôt --</option>
                        <?php foreach ($entrepots as $entrepot): ?>
                            <option value="<?= $entrepot['id'] ?>"><?= htmlspecialchars($entrepot['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <input type="number" class="form-control" name="qte" min="1" placeholder="Quantité à stocker" required>
                </div>
                <button type="submit" class="btn btn-success btn-sm">Stocker</button>
            </form>
        </div>
    </div>

    <!-- Liste des stocks -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Liste des stocks</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Entrepôt</th>
                        <th>Quantité</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT p.nom AS produit, e.nom AS entrepot, s.qte 
                            FROM stock_entrepot s
                            JOIN produits p ON s.produit_id = p.id
                            JOIN entrepots e ON s.entrepot_id = e.id
                            ORDER BY p.nom, e.nom";
                    $stmt = $pdo->query($sql);
                    $stocks = $stmt->fetchAll();

                    foreach ($stocks as $stock):
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($stock['produit']) ?></td>
                            <td><?= htmlspecialchars($stock['entrepot']) ?></td>
                            <td><?= $stock['qte'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
