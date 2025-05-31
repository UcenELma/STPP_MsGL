<?php
// navbar.php

require_once __DIR__ . '/../STPP_Backend/navbar_backend.php';
?>

<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <!-- Bouton Sidebar -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Barre de recherche -->
    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
        <div class="input-group">
            <input type="text" class="form-control bg-light border-0 small" placeholder="Rechercher..." aria-label="Rechercher" aria-describedby="basic-addon2">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form>

    <!-- Navbar droite -->
    <ul class="navbar-nav ml-auto">

        <!-- Alertes -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <?php if ($totalAlertsCount > 0): ?>
                    <span class="badge badge-danger badge-counter"><?= $totalAlertsCount ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">Centre des alertes</h6>

                <?php foreach ($lowStockAlerts as $alert): ?>
                    <a class="dropdown-item d-flex align-items-center"
                       href="index.php?page=produits/liste_produits&low_stock=1">
                        <div class="mr-3">
                            <div class="icon-circle bg-warning">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500"><?= date('j F Y') ?></div>
                            <span class="font-weight-bold">
                                Produit "<strong><?= htmlspecialchars($alert['nom']) ?></strong>" en stock faible (<?= (int)$alert['qte'] ?> restant).
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>

                <?php foreach ($nearExpiryAlerts as $alert): ?>
                    <a class="dropdown-item d-flex align-items-center"
                       href="index.php?page=produits/liste_produits&near_expiry=1">
                        <div class="mr-3">
                            <div class="icon-circle bg-danger">
                                <i class="fas fa-exclamation-circle text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500"><?= date('j F Y', strtotime($alert['date_peremption'])) ?></div>
                            Produit "<strong><?= htmlspecialchars($alert['nom']) ?></strong>" proche de la date de péremption !
                        </div>
                    </a>
                <?php endforeach; ?>

                <?php if ($totalAlertsCount === 0): ?>
                    <div class="dropdown-item text-center small text-gray-500">Aucune nouvelle alerte</div>
                <?php endif; ?>

                <a class="dropdown-item text-center small text-gray-500" href="index.php?page=produits/liste_produits">Voir toutes les alertes</a>
            </div>
        </li>

        <!-- Messages -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <span class="badge badge-danger badge-counter">7</span>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">Centre de messages</h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="img/undraw_profile_1.svg" alt="...">
                        <div class="status-indicator bg-success"></div>
                    </div>
                    <div class="font-weight-bold">
                        <div class="text-truncate">Salut ! Peux-tu m'aider avec un problème que j'ai rencontré ?</div>
                        <div class="small text-gray-500">Emily Fowler · il y a 58 minutes</div>
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Voir plus de messages</a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Utilisateur -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                    <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Utilisateur' ?>
                </span>
                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Déconnexion
                </a>
            </div>
        </li>
    </ul>
</nav>

<!-- Modal de déconnexion -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Prêt à partir ?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Sélectionnez « Déconnexion » ci-dessous si vous êtes prêt à mettre fin à votre session actuelle.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Annuler</button>
                <a class="btn btn-danger" href="./../STPP_Backend/logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</div>
