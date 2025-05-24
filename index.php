<?php
// Start session and check login
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<?php include 'head.php'; ?>

<body id="page-top">
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'navbar.php'; ?>

                <div class="container-fluid">
                    <?php
                    // Get requested page or default
                    $page = isset($_GET['page']) ? $_GET['page'] : 'chart';

                    // Allowed pages list to avoid unauthorized file inclusion
                    $allowed_pages = [
                        'chart',
                        'utilisateurs/liste_utilisateurs',
                        'utilisateurs/formulaire_utilisateur',
                        'utilisateurs/supprimer_utilisateur',
                        'utilisateurs/modifier_utilisateur',
                        'produits/liste_produits',
                        'produits/formulaire_produit',
                        'produits/supprimer_produit',
                        'produits/modifier_produit',
                        'gestion_stock',
                    ];

                    if (in_array($page, $allowed_pages)) {
                        include $page . '.php';
                    } else {
                        echo "<p>Page non trouvée.</p>";
                    }
                    ?>
                </div>
            </div>

            <?php include 'footer.php'; ?>
        </div>
    </div>
</body>

</html>
