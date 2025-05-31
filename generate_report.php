<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require('fpdf/fpdf.php');

// Classe PDF étendue pour header/footer si besoin
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'Rapport de Stock - Systeme de Traçabilité',0,1,'C');
        $this->SetFont('Arial','I',10);
        $this->Cell(0,7,'Date du rapport: '.date('d/m/Y H:i:s'),0,1,'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// 1. Statistiques globales
// Quantité totale
$stmt = $db->query("SELECT SUM(qte) AS total_qte FROM produits");
$total_qte = $stmt->fetchColumn() ?: 0;

// Nombre total produits
$stmt = $db->query("SELECT COUNT(*) FROM produits");
$total_produits = $stmt->fetchColumn() ?: 0;

// Produits faibles (qte < 10)
$stmt = $db->query("SELECT COUNT(*) FROM produits WHERE qte < 10");
$faibles = $stmt->fetchColumn() ?: 0;

// Produits périmés
$stmt = $db->prepare("SELECT COUNT(*) FROM produits WHERE date_peremption < CURDATE()");
$stmt->execute();
$perimes = $stmt->fetchColumn() ?: 0;

// Produits proches péremption (dans 7 jours)
$stmt = $db->prepare("SELECT COUNT(*) FROM produits WHERE date_peremption BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$stmt->execute();
$proches = $stmt->fetchColumn() ?: 0;

$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Statistiques générales",0,1);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,"Quantité totale de tous les produits : $total_qte",0,1);
$pdf->Cell(0,8,"Nombre total de produits : $total_produits",0,1);
$pdf->Cell(0,8,"Produits faibles (quantité < 10) : $faibles",0,1);
$pdf->Cell(0,8,"Produits périmés : $perimes",0,1);
$pdf->Cell(0,8,"Produits proches de péremption (7 jours) : $proches",0,1);

$pdf->Ln(5);

// 2. Quantité par produit (tableau simple)
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Quantité par produit",0,1);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(90,8,"Produit",1,0,'C');
$pdf->Cell(30,8,"Quantité",1,1,'C');

$pdf->SetFont('Arial','',12);
$stmt = $db->query("SELECT nom, qte FROM produits ORDER BY nom");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $pdf->Cell(90,8,utf8_decode($row['nom']),1,0);
    $pdf->Cell(30,8,$row['qte'],1,1,'C');
}

$pdf->Ln(5);

// 3. Stock par entrepôt
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Stock par entrepôt",0,1);

$sql = "
    SELECT e.nom AS entrepot, SUM(se.qte) AS total_qte
    FROM stock_entrepot se
    JOIN entrepots e ON se.entrepot_id = e.id
    GROUP BY e.nom
    ORDER BY e.nom
";
$stmt = $db->prepare($sql);
$stmt->execute();
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(90,8,"Entrepôt",1,0,'C');
$pdf->Cell(30,8,"Quantité",1,1,'C');
$pdf->SetFont('Arial','',12);
foreach ($stocks as $stock) {
    $pdf->Cell(90,8,utf8_decode($stock['entrepot']),1,0);
    $pdf->Cell(30,8,$stock['total_qte'],1,1,'C');
}

$pdf->Ln(5);

// 4. Derniers mouvements (limité à 10)
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Derniers mouvements de produits (10 derniers)",0,1);

$sql = "
    SELECT p.nom AS produit,
           e1.nom AS source,
           e2.nom AS destination,
           m.qte,
           m.date_mouvement
    FROM mouvements_produits m
    JOIN produits p ON m.produit_id = p.id
    LEFT JOIN entrepots e1 ON m.source_entrepot_id = e1.id
    JOIN entrepots e2 ON m.destination_entrepot_id = e2.id
    ORDER BY m.date_mouvement DESC
    LIMIT 10
";
$stmt = $db->prepare($sql);
$stmt->execute();
$mouvements = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(40,8,"Produit",1,0,'C');
$pdf->Cell(35,8,"Source",1,0,'C');
$pdf->Cell(35,8,"Destination",1,0,'C');
$pdf->Cell(20,8,"Quantité",1,0,'C');
$pdf->Cell(40,8,"Date Mouvement",1,1,'C');

$pdf->SetFont('Arial','',10);
foreach ($mouvements as $m) {
    $pdf->Cell(40,8,utf8_decode($m['produit']),1,0);
    $pdf->Cell(35,8,utf8_decode($m['source'] ?? 'N/A'),1,0);
    $pdf->Cell(35,8,utf8_decode($m['destination'] ?? 'N/A'),1,0);
    $pdf->Cell(20,8,$m['qte'],1,0,'C');
    $pdf->Cell(40,8,date('d/m/Y', strtotime($m['date_mouvement'])),1,1);
}

// Nettoyer le buffer
ob_end_clean();

// Générer le PDF et forcer le téléchargement
$pdf->Output('D', 'rapport_stock_'.date('Ymd_His').'.pdf');
exit;
