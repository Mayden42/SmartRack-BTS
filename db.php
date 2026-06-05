<?php
// 1. Identifiants de connexion à XAMPP
$db_host    = 'localhost';
$db_name    = 'gestion_rack';
$db_user    = 'root';
$db_pass    = '';
$db_charset = 'utf8mb4';

// 2. Chaîne de connexion et options de sécurité
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Déclenche une alerte si une requête SQL a un bug
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Permet de lire les résultats sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Sécurité maximale contre les injections SQL
];

// 3. Tentative de connexion à la base de données
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Si MySQL est éteint ou mal configuré, on arrête le script et on affiche l'erreur
    die('Erreur de connexion à la base de données : ' . htmlspecialchars($e->getMessage()));
}
?>