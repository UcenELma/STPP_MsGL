<!-- formulaire_produit.php -->
<meta charset="utf-8" />
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Ajouter un produit</h6>
    </div>
    <div class="card-body">
        <form action="produits/ajouter_produit.php" method="post">
            <div class="mb-3">
                <input type="text" name="nom" class="form-control" placeholder="Nom du produit" required>
            </div>
            <div class="mb-3">
                <input type="text" name="type" class="form-control" placeholder="Type" required>
            </div>
            <div class="mb-3">
                <label for="qte" class="form-label">Quantité en stock</label>
                <input type="number" name="qte" id="qte" class="form-control" min="0" required>
            </div>
            <div class="mb-3">
                <label for="date_production" class="form-label">Date de production</label>
                <input type="date" name="date_production" id="date_production" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="date_peremption" class="form-label">Date de péremption</label>
                <input type="date" name="date_peremption" id="date_peremption" class="form-control" required>
            </div>
            <div class="mb-3">
                <select name="fournisseur_id" class="form-select" required>
                    <option value="">-- Choisir un fournisseur --</option>
                    <?php
                    require_once __DIR__ . '/../config.php';

                    // Récupérer les fournisseurs
                    $stmt = $db->query("SELECT id, nom FROM utilisateurs WHERE role = 'fournisseur' ORDER BY nom");
                    $fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($fournisseurs as $fournisseur) {
                        echo '<option value="' . htmlspecialchars($fournisseur['id']) . '">' . htmlspecialchars($fournisseur['nom']) . '</option>';
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fa-solid fas fa-plus"></i> Ajouter le produit
                </button>

            </div>
            <a href="index.php?page=utilisateurs/formulaire_utilisateur"
                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fa-solid fa-user-plus fas fa-sm text-white-50"></i> Ajouter un nouveau fournisseur
            </a>
        </form>
    </div>
</div>

