<meta charset="utf-8" />
<?php
// require_once './../config.php';
// require_once __DIR__ . '/../config.php';
require_once (__DIR__ . './../../STPP_Database/config.php');

// Vérifier si on a un ID en GET (affichage formulaire) ou POST (traitement formulaire)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Traitement de la mise à jour
    if (!isset($_POST['id'], $_POST['nom'], $_POST['email'], $_POST['role'])) {
        die('Données manquantes.');
    }

    $id = (int)$_POST['id'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Vérifier si un nouveau mot de passe a été saisi (optionnel)
    if (!empty($_POST['mot_de_passe'])) {
        $mot_de_passe_hash = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
        $sql = "UPDATE utilisateurs SET nom = ?, email = ?, role = ?, mot_de_passe_hash = ? WHERE id = ?";
        $params = [$nom, $email, $role, $mot_de_passe_hash, $id];
    } else {
        $sql = "UPDATE utilisateurs SET nom = ?, email = ?, role = ? WHERE id = ?";
        $params = [$nom, $email, $role, $id];
    }

    $stmt = $db->prepare($sql);
    try {
        $stmt->execute($params);
        header('Location: ../index.php?page=utilisateurs/liste_utilisateurs');
        exit;
    } catch (PDOException $e) {
        echo "Erreur lors de la mise à jour : " . $e->getMessage();
    }
} else {
    // Affichage du formulaire
    if (!isset($_GET['id'])) {
        die('ID utilisateur manquant.');
    }

    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('Utilisateur non trouvé.');
    }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Modifier un utilisateur</h6>
        </div>
        <div class="card-body">
            <form action="utilisateurs/modifier_utilisateur.php" method="post">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <div class="mb-3">
                    <input type="text" name="nom" class="form-control" placeholder="Nom" required value="<?= htmlspecialchars($user['nom']) ?>">
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required value="<?= htmlspecialchars($user['email']) ?>">
                </div>
                <div class="mb-3">
                    <input type="password" name="mot_de_passe" class="form-control" placeholder="Nouveau mot de passe (laisser vide pour ne pas changer)">
                </div>
                <div class="mb-3">
                    <select name="role" class="form-select" required>
                        <option value="">-- Choisir un rôle --</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="fournisseur" <?= $user['role'] === 'fournisseur' ? 'selected' : '' ?>>Fournisseur</option>
                        <option value="transporteur" <?= $user['role'] === 'transporteur' ? 'selected' : '' ?>>Transporteur</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Modifier
                </button>
            </form>
        </div>
    </div>

<?php
} // fin else GET
?>
