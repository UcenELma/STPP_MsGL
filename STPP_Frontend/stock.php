<?php
// require_once 'gestion_stock.php';
require_once __DIR__ . '/../STPP_Backend/gestion_stock.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <title>Gestion de Stock</title>
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
                                <tr>
                                    <td colspan="5" class="text-center">Aucun mouvement trouvé.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($mouvements as $m): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($m['produit']) ?></td>
                                        <td><?= $m['source'] ?? '<em>Entrée stock</em>' ?></td>
                                        <td><?= htmlspecialchars($m['destination']) ?></td>
                                        <td><?= (int) $m['qte'] ?></td>
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