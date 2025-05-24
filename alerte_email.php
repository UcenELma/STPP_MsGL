<?php
require 'vendor/autoload.php';
require 'config.php'; // connexion PDO

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer
$mail = new PHPMailer(true);

try {
    // SMTP Gmail (ou autre)
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'betagama181@gmail.com'; // remplace ici
    $mail->Password = ''; // mot de passe d'application Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Obtenir les emails admin
    $stmt = $db->query("SELECT email FROM utilisateurs WHERE role = 'admin'");
    $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Vérifie produits critiques
    $today = date('Y-m-d');
    $date_proche = date('Y-m-d', strtotime('+7 days'));

    $sql = "SELECT * FROM produits WHERE qte < 10 OR date_peremption <= :date_proche";
    $stmtProd = $db->prepare($sql);
    $stmtProd->execute(['date_proche' => $date_proche]);
    $produits = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

    if ($produits) {
        $message = "⚠️ Produits à surveiller :\n\n";
        foreach ($produits as $p) {
            $message .= "- {$p['nom']} | Qte: {$p['qte']} | Péremption: {$p['date_peremption']}\n";
        }

        // Envoi à chaque admin
        foreach ($admins as $email) {
            $mail->setFrom('ton.email@gmail.com', 'Système Traçabilité');
            $mail->addAddress($email);
            $mail->Subject = '⚠️ Alerte produits (stock/péremption)';
            $mail->Body = $message;
            $mail->send();
            $mail->clearAddresses();
        }

        echo "✔️ Alerte envoyée avec succès.";
    } else {
        echo "✅ Aucun produit critique aujourd'hui.";
    }

} catch (Exception $e) {
    echo "❌ Erreur : {$mail->ErrorInfo}";
}
?>
