<!-- liste_utilisateurs.php -->
<meta charset="utf-8" />
<?php
// require_once 'config.php';
require_once (__DIR__ . './../../STPP_Database/config.php'); 


// Nombre d'utilisateurs par page
$users_per_page = 5;

// Récupération et validation de la page courante pour la pagination (paramètre 'p')
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$current_page = max(1, $current_page);

$offset = ($current_page - 1) * $users_per_page;

// Terme de recherche sécurisé
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchTerm = '%' . $search . '%';

// Requête avec pagination et recherche sur nom et email
$query = "SELECT * FROM utilisateurs WHERE nom LIKE :search OR email LIKE :search ORDER BY nom LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);

// Bind des paramètres avec types corrects
$stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
$stmt->bindValue(':limit', $users_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre total pour pagination
$countStmt = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE nom LIKE :search OR email LIKE :search");
$countStmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
$countStmt->execute();
$total_users = $countStmt->fetchColumn();
$total_pages = ceil($total_users / $users_per_page);

// Compter utilisateurs par rôle
$role_count_stmt = $db->query("SELECT role, COUNT(*) as count FROM utilisateurs GROUP BY role");
$role_counts = $role_count_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- En-tête et bouton ajouter -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Utilisateurs</h1>
    <a href="index.php?page=utilisateurs/formulaire_utilisateur" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fa-solid fa-user-plus fas fa-sm text-white-50"></i> Ajouter un utilisateur
    </a>
</div>

<!-- Formulaire recherche -->
<form method="get" class="mb-4">
    <!-- Important : inclure page dans le formulaire pour ne pas perdre la vue -->
    <input type="hidden" name="page" value="utilisateurs/liste_utilisateurs" />
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Rechercher par nom ou email" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-primary" type="submit">Rechercher</button>
    </div>
</form>

<!-- Tableau utilisateurs -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Liste des utilisateurs</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($utilisateurs)): ?>
                        <tr><td colspan="5" class="text-center">Aucun utilisateur trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($utilisateurs as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['nom']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td>
                                    <a href="index.php?page=utilisateurs/modifier_utilisateur&id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="index.php?page=utilisateurs/supprimer_utilisateur&id=<?= $user['id'] ?>" onclick="return confirm('Êtes-vous sûr ?')" class="btn btn-sm btn-danger">
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
                            <a class="page-link" href="?page=utilisateurs/liste_utilisateurs&search=<?= urlencode($search) ?>&p=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <!-- Statistiques utilisateurs par rôle -->
        <h5 class="mt-4">Utilisateurs par rôle :</h5>
        <ul>
            <?php foreach ($role_counts as $role_count): ?>
                <li><?= htmlspecialchars($role_count['role']) ?> : <?= $role_count['count'] ?> utilisateur(s)</li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
