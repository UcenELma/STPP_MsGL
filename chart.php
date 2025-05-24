<?php
include 'statistiques.php';

// Récupérer quantité par produit pour le Bar Chart
$stmt = $db->query("SELECT nom, qte FROM produits");
$quantite_par_produit = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Dashboard Header -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
    </a>
</div>

<!-- Stat Cards -->
<div class="row">
    <!-- Quantité Totale -->
    <!-- <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Quantité Totale non conservés</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($total_qte) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-cubes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Nombre de Produits -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Nombre de Produits</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($total_produits) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Produits Faibles -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Produits Faibles</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($produits_faibles) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Produits Périmés -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Produits Périmés</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($produits_perimes) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Produits Proches Péremption -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Proches Péremption</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= htmlspecialchars($produits_proches_peremption) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Graphiques -->
<div class="row">
    <!-- Bar Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4" style="height: 400px;">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Quantité de produits non conservés</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Options :</div>
                        <a class="dropdown-item" href="#">Actualiser</a>
                        <a class="dropdown-item" href="#">Télécharger</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Paramètres</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area" style="position: relative; height: 300px;">
                    <canvas id="qteBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Stock des produits conservés par entrepôt</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <a href="index.php?page=gestion_stock" class="dropdown-item">Gérer le stock</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="myPieChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <?php foreach ($labels as $index => $label): ?>
                        <span class="mr-2">
                            <i class="fas fa-circle"
                                style="color: <?= ['#4e73df', '#1cc88a', '#36b9cc'][$index % 3] ?>"></i>
                            <?= htmlspecialchars($label) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN (une seule fois) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Passer les données PHP vers JS -->
<script>
    const productLabels = <?= json_encode(array_column($quantite_par_produit, 'nom')) ?>;
    const productQuantities = <?= json_encode(array_map('intval', array_column($quantite_par_produit, 'qte'))) ?>;

    const pieLabels = <?= json_encode($labels) ?>;
    const pieData = <?= json_encode($quantities) ?>;
</script>

<!-- Bar Chart -->
<script>
    const ctxBar = document.getElementById('qteBarChart').getContext('2d');
    const qteBarChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: productLabels,
            datasets: [{
                label: 'Quantité',
                data: productQuantities,
                backgroundColor: 'rgba(237, 11, 199, 0.59)',
                borderColor: 'rgb(255, 255, 255)',
                borderWidth: 1,
                borderRadius: 4,
                maxBarThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantité'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Produits'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        autoSkip: false
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Quantité de chaque produit'
                },
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true
                }
            }
        }
    });
</script>

<!-- Pie Chart -->
<script>
    const ctxPie = document.getElementById('myPieChart').getContext('2d');
    const myPieChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: pieLabels,
            datasets: [{
                data: pieData,
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
                hoverBorderColor: "rgba(234, 236, 244, 1)"
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    caretPadding: 10,
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 20,
                        padding: 15
                    }
                },
                title: {
                    display: true,
                    text: 'Répartition du stock par entrepôt'
                }
            }
        }
    });
</script>