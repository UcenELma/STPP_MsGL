<!-- formulaire_utilisateur.php -->
<meta charset="utf-8"/>
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Ajouter un utilisateur</h6>
    </div>
    <div class="card-body">
        <form action="utilisateurs/ajouter_utilisateur.php" method="post">
            <div class="mb-3">
                <input type="text" name="nom" class="form-control" placeholder="Nom" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="mot_de_passe" class="form-control" placeholder="Mot de passe" required>
            </div>
            <div class="mb-3">
                <select name="role" class="form-select" required>
                    <option value="">-- Choisir un rôle --</option>
                    <option value="admin">Admin</option>
                    <option value="fournisseur">Fournisseur</option>
                    <option value="transporteur">Transporteur</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fas fa-user-plus"></i> Ajouter
            </button>
        </form>
    </div>
</div>
