<!-- navbar_forniseur.php -->
 
<?php
session_start(); // Pour accéder à $_SESSION['username']
// Exemple : $_SESSION['username'] = 'Hocine'; (à définir après la connexion de l'utilisateur)
?>

<!-- Bootstrap CSS (à inclure dans le <head> ou avant ce fichier) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

<nav class="navbar navbar-expand-lg navbar-primary bg-primary mb-4">
  <div class="container-fluid">
  <a class="navbar-brand" href="#" style="color: white;">STPP By Master GL</a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto">
        <!-- Lien Accueil
        <li class="nav-item">
          <a class="nav-link" href="index.php">Accueil</a>
        </li> -->

        <!-- Profil utilisateur -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
            <img src="img/undraw_profile.svg" alt="Profil" width="30" height="30" class="rounded-circle me-2">
            <span class="text-white"><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Utilisateur' ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="profil.php"><i class="bi bi-person"></i> Profil</a></li>
            <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear"></i> Paramètres</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Bootstrap JS (à inclure juste avant </body>) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Icons Bootstrap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
