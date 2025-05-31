<!-- STPP_Backend/logout.php -->
<?php
session_start();
session_destroy();
header("Location: ./../STPP_Frontend/login.php");
exit();
?>
