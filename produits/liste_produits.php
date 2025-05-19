<!-- liste_produits.php -->
<meta charset="utf-8" />
<?php
require_once 'config.php';

// Nombre de produits par page
$products_per_page = 5;

// Récupération et validation de la page courante pour la pagination (paramètre 'p')
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $products_per_page;

// Terme de recherche sécurisé (sur nom ou type du produit)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchTerm = '%' . $search . '%';

// Requête avec pagination et recherche (jointure avec utilisateurs pour fournisseur)
$query = "
    SELECT p.*, u.nom AS fournisseur_nom 
    FROM produits p
    LEFT JOIN utilisateurs u ON p.fournisseur_id = u.id AND u.role = 'fournisseur'
    WHERE p.nom LIKE :search OR p.type LIKE :search
    ORDER BY p.nom
    LIMIT :limit OFFSET :offset
";

$stmt = $db->prepare($query);
$stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
$stmt->bindValue(':limit', $products_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre total de produits pour pagination
$countStmt = $db->prepare("
    SELECT COUNT(*) 
    FROM produits p
    LEFT JOIN utilisateurs u ON p.fournisseur_id = u.id AND u.role = 'fournisseur'
    WHERE p.nom LIKE :search OR p.type LIKE :search
");
$countStmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
$countStmt->execute();
$total_products = $countStmt->fetchColumn();
$total_pages = ceil($total_products / $products_per_page);
?>

<!-- En-tête et bouton ajouter -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Produits</h1>
    <a href="index.php?page=produits/formulaire_produit" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fa-solid fa-plus fas fa-sm text-white-50"></i> Ajouter un produit
    </a>
</div>

<!-- Formulaire recherche -->
<form method="get" class="mb-4">
    <input type="hidden" name="page" value="produits/liste_produits" />
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Rechercher par nom ou type" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-primary" type="submit">Rechercher</button>
    </div>
</form>

<!-- Tableau produits -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Liste des produits</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Date production</th>
                        <th>Date péremption</th>
                        <th>Fournisseur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($produits)): ?>
                        <tr><td colspan="7" class="text-center">Aucun produit trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($produits as $produit): ?>
                            <tr>
                                <td><?= $produit['id'] ?></td>
                                <td><?= htmlspecialchars($produit['nom']) ?></td>
                                <td><?= htmlspecialchars($produit['type']) ?></td>
                                <td><?= htmlspecialchars($produit['date_production']) ?></td>
                                <td><?= htmlspecialchars($produit['date_peremption']) ?></td>
                                <td><?= htmlspecialchars($produit['fournisseur_nom'] ?? 'Non assigné') ?></td>
                                <td>
                                    <a href="index.php?page=produits/modifier_produit&id=<?= $produit['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="index.php?page=produits/supprimer_produit&id=<?= $produit['id'] ?>" onclick="return confirm('Êtes-vous sûr ?')" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center mt-4">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i === $current_page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=produits/liste_produits&search=<?= urlencode($search) ?>&p=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
