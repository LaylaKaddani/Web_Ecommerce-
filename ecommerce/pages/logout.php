<?php
// Deconnexion : on detruit la session et on redirige

session_start();
session_unset();
session_destroy();
header("Location: accueil.php");
exit;
?>
