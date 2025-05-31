<?php
// modifier_produit.php
require_once __DIR__ . '/../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID produit invalide.');
}

$id = (int) $_GET['id'];

$errors = [];
$success = false;

$stmtF = $db->query("SELECT id, nom FROM utilisateurs WHERE role = 'fournisseur' ORDER BY nom");
$fournisseurs = $stmtF->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $date_production = $_POST['date_production'] ?? '';
    $date_peremption = $_POST['date_peremption'] ?? '';
    $fournisseur_id = isset($_POST['fournisseur_id']) ? (int) $_POST['fournisseur_id'] : null;
    $qte = isset($_POST['qte']) ? (int) $_POST['qte'] : null;  

    // Validation simple
    if ($nom === '') $errors[] = "Le nom est requis.";
    if ($type === '') $errors[] = "Le type est requis.";
    if ($date_production === '') $errors[] = "La date de production est requise.";
    if ($date_peremption === '') $errors[] = "La date de péremption est requise.";
    if (!$fournisseur_id) $errors[] = "Le fournisseur est requis.";
    if ($qte === null || $qte < 0) $errors[] = "La quantité est requise et doit être positive.";

    if (empty($errors)) {
        $sql = "UPDATE produits 
                SET nom = ?, type = ?, date_production = ?, date_peremption = ?, fournisseur_id = ?, qte = ?
                WHERE id = ?";
        $stmt = $db->prepare($sql);
        try {
            $stmt->execute([$nom, $type, $date_production, $date_peremption, $fournisseur_id, $qte, $id]);
            $success = true;
            header('Location: ../index.php?page=produits/liste_produits');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
} else {
    $stmt = $db->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produit) {
        die("Produit non trouvé.");
    }

    $nom = $produit['nom'];
    $type = $produit['type'];
    $date_production = $produit['date_production'];
    $date_peremption = $produit['date_peremption'];
    $fournisseur_id = $produit['fournisseur_id'];
    $qte = $produit['qte']; 
}
?>

<meta charset="utf-8" />
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Modifier le produit #<?= htmlspecialchars($id) ?></h6>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="produits/modifier_produit.php?id=<?= $id ?>" method="post">
            <div class="mb-3">
                <input type="text" name="nom" class="form-control" placeholder="Nom du produit" required
                    value="<?= htmlspecialchars($nom) ?>">
            </div>
            <div class="mb-3">
                <input type="text" name="type" class="form-control" placeholder="Type" required
                    value="<?= htmlspecialchars($type) ?>">
            </div>
            <div class="mb-3">
                <label for="date_production" class="form-label">Date de production</label>
                <input type="date" name="date_production" id="date_production" class="form-control" required
                    value="<?= htmlspecialchars($date_production) ?>">
            </div>
            <div class="mb-3">
                <label for="date_peremption" class="form-label">Date de péremption</label>
                <input type="date" name="date_peremption" id="date_peremption" class="form-control" required
                    value="<?= htmlspecialchars($date_peremption) ?>">
            </div>
            <div class="mb-3">
                <input type="number" name="qte" class="form-control" placeholder="Quantité" min="0" required
                    value="<?= htmlspecialchars($qte) ?>">
            </div>
            <div class="mb-3">
                <select name="fournisseur_id" class="form-select" required>
                    <option value="">-- Choisir un fournisseur --</option>
                    <?php foreach ($fournisseurs as $fournisseur): ?>
                        <option value="<?= $fournisseur['id'] ?>" <?= ($fournisseur['id'] == $fournisseur_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($fournisseur['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fas fa-save"></i> Enregistrer les modifications
            </button>
        </form>

        <a href="index.php?page=utilisateurs/formulaire_utilisateur"
            class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mt-2">
            <i class="fa-solid fa-user-plus fas fa-sm text-white-50"></i> Ajouter un nouveau fournisseur
        </a>
    </div>
</div>
