<!-- config.php -->


<meta charset="utf-8"/>
<?php
$host = 'localhost';
$dbname = 'bd_gestion_traceabilite';
$user = 'root';
$pass = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connexion réussite";
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>


<!-- products -->
<?php
$host = 'localhost';
$dbname = 'bd_gestion_traceabilite';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connexion réussite";
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

