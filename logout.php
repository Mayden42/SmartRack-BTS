<?php
session_start();
session_unset(); // Vide les variables
session_destroy(); // Détruit la session
header('Location: login.php'); // Renvoie au login
exit;
?>