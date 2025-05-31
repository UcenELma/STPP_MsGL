<!-- liste_produits.php -->
<meta charset="utf-8" />

<?php
require_once (__DIR__ . './../../STPP_Database/config.php');


// Pagination
$products_per_page = 5;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $products_per_page;

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchTerm = '%' . $search . '%';

// Filtres
$low_stock = isset($_GET['low_stock']) && $_GET['low_stock'] == '1';
$near_expiry = isset($_GET['near_expiry']) && $_GET['near_expiry'] == '1';

// Tri par type
$sort_type = isset($_GET['sort_type']) ? $_GET['sort_type'] : 'asc'; // valeurs possibles : 'asc' ou 'desc'
$sort_type = strtolower($sort_type) === 'desc' ? 'DESC' : 'ASC';

// Construction WHERE
$whereClauses = [];
$params = [];
$whereClauses[] = "(p.nom LIKE :search OR p.type LIKE :search)";
$params[':search'] = $searchTerm;

if ($low_stock) {
    $whereClauses[] = "p.qte < 10";
}
if ($near_expiry) {
    $whereClauses[] = "p.date_peremption <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)";
}

$whereSQL = implode(' AND ', $whereClauses);

// Requête produits avec tri et pagination
$query = "
    SELECT p.*, u.nom AS fournisseur_nom 
    FROM produits p
    LEFT JOIN utilisateurs u ON p.fournisseur_id = u.id AND u.role = 'fournisseur'
    WHERE $whereSQL
    ORDER BY p.type $sort_type, p.nom
    LIMIT :limit OFFSET :offset
";

$stmt = $db->prepare($query);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $products_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre total produits (pour pagination)
$countQuery = "
    SELECT COUNT(*) 
    FROM produits p
    LEFT JOIN utilisateurs u ON p.fournisseur_id = u.id AND u.role = 'fournisseur'
    WHERE $whereSQL
";
$countStmt = $db->prepare($countQuery);
foreach ($params as $key => $val) {
    $countStmt->bindValue($key, $val, PDO::PARAM_STR);
}
$countStmt->execute();
$total_products = $countStmt->fetchColumn();
$total_pages = ceil($total_products / $products_per_page);

// Alerte quantité faible (indépendante du filtre quantité et date)
$alertStmtLowQte = $db->prepare("
    SELECT COUNT(*) 
    FROM produits 
    WHERE qte < 10
    AND (nom LIKE :search OR type LIKE :search)
");
$alertStmtLowQte->bindValue(':search', $searchTerm, PDO::PARAM_STR);
$alertStmtLowQte->execute();
$count_low_qte = $alertStmtLowQte->fetchColumn();

// Alerte produits proche ou dépassant date péremption
$alertStmtNearExpiry = $db->prepare("
    SELECT COUNT(*) 
    FROM produits 
    WHERE date_peremption <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)
    AND (nom LIKE :search OR type LIKE :search)
");
$alertStmtNearExpiry->bindValue(':search', $searchTerm, PDO::PARAM_STR);
$alertStmtNearExpiry->execute();
$count_near_expiry = $alertStmtNearExpiry->fetchColumn();

?>

<!-- En-tête et bouton ajouter -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Produits</h1>
    <a href="index.php?page=produits/formulaire_produit" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fa-solid fa-plus fas fa-sm text-white-50"></i> Ajouter un produit
    </a>
</div>

<!-- Alertes globales -->
<?php if ($count_low_qte > 0): ?>
    <div class="alert alert-warning">
        <strong>Attention :</strong> Il y a <?= $count_low_qte ?> produit(s) avec une quantité inférieure à 10.
    </div>
<?php endif; ?>

<?php if ($count_near_expiry > 0): ?>
    <div class="alert alert-danger">
        <strong>Attention :</strong> Il y a <?= $count_near_expiry ?> produit(s) avec une date de péremption proche ou dépassée.
    </div>
<?php endif; ?>

<!-- Formulaire recherche + filtres + tri -->
<form method="get" class="mb-4">
    <input type="hidden" name="page" value="produits/liste_produits" />
    <div class="row g-2 d-sm-flex align-items-center justify-content-between mb-4">
        <div class="col-sm-5">
            <input type="text" name="search" class="form-control" placeholder="Rechercher par nom ou type" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-auto form-check">
            <input class="form-check-input" type="checkbox" name="low_stock" id="low_stock" value="1" <?= $low_stock ? 'checked' : '' ?> onchange="this.form.submit()">
            <label class="form-check-label" for="low_stock">
                Quantité < 10 seulement
            </label>
        </div>
        <div class="col-auto form-check">
            <input class="form-check-input" type="checkbox" name="near_expiry" id="near_expiry" value="1" <?= $near_expiry ? 'checked' : '' ?> onchange="this.form.submit()">
            <label class="form-check-label" for="near_expiry">
                Date péremption proche/dépassée
            </label>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" type="submit">Rechercher</button>
        </div>
    </div>
</form>

<!-- Tableau produits -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Liste des produits</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Quantité</th>
                        <th>Date production</th>
                        <th>Date péremption</th>
                        <th>Fournisseur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($produits)): ?>
                        <tr><td colspan="8" class="text-center">Aucun produit trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($produits as $produit): ?>
                            <tr>
                                <td><?= $produit['id'] ?></td>
                                <td><?= htmlspecialchars($produit['nom']) ?></td>
                                <td><?= htmlspecialchars($produit['type']) ?></td>
                                <td>
                                    <?= htmlspecialchars($produit['qte']) ?>
                                    <?php if ($produit['qte'] < 10): ?>
                                        <span class="badge badge-warning ms-2">⚠️ Quantité faible</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($produit['date_production']) ?></td>
                                <td>
                                    <?= htmlspecialchars($produit['date_peremption']) ?>
                                    <?php
                                    $dateNow = new DateTime();
                                    $datePeremption = DateTime::createFromFormat('Y-m-d', $produit['date_peremption']);
                                    if ($datePeremption) {
                                        $diff = $dateNow->diff($datePeremption);
                                        $daysDiff = (int)$diff->format('%r%a');
                                        if ($daysDiff < 0) {
                                            echo ' <span class="badge badge-danger ms-2"> ❌ Périmé </span>';
                                        } elseif ($daysDiff <= 15) {
                                            echo ' <span class="badge badge-warning ms-2"> ⚠️ Proche péremption </span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($produit['fournisseur_nom'] ?? 'N/A') ?></td>
                                <td>
                                    <a href="index.php?page=produits/modifier_produit&id=<?= $produit['id'] ?>"
                                       class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i> Modifier
                                    </a>
                                    <a href="index.php?page=produits/supprimer_produit&id=<?= $produit['id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                                        <i class="fa fa-trash"></i> Supprimer
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
            <nav aria-label="Pagination produits">
                <ul class="pagination justify-content-center">
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=produits/liste_produits&p=<?= $current_page - 1 ?>&search=<?= urlencode($search) ?>&low_stock=<?= $low_stock ? 1 : 0 ?>&near_expiry=<?= $near_expiry ? 1 : 0 ?>&sort_type=<?= $sort_type ?>" aria-label="Page précédente">&laquo;</a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i === $current_page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=produits/liste_produits&p=<?= $i ?>&search=<?= urlencode($search) ?>&low_stock=<?= $low_stock ? 1 : 0 ?>&near_expiry=<?= $near_expiry ? 1 : 0 ?>&sort_type=<?= $sort_type ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=produits/liste_produits&p=<?= $current_page + 1 ?>&search=<?= urlencode($search) ?>&low_stock=<?= $low_stock ? 1 : 0 ?>&near_expiry=<?= $near_expiry ? 1 : 0 ?>&sort_type=<?= $sort_type ?>" aria-label="Page suivante">&raquo;</a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
