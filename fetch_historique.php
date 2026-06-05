<?php
// On force le format JSON pour que le JavaScript puisse le lire facilement
header('Content-Type: application/json; charset=utf-8');

// On utilise ta connexion sécurisée
require_once 'db.php';

try {
    // On va chercher les 100 derniers événements avec le nom de l'outil
    $sql = "SELECT h.id, 
                   h.uid_nfc, 
                   h.action, 
                   DATE_FORMAT(h.date_heure, '%d/%m/%Y %H:%i:%s') AS date_formatee,
                   o.nom_outil
            FROM historique h
            LEFT JOIN outils o ON h.uid_nfc = o.uid_nfc
            ORDER BY h.date_heure DESC
            LIMIT 100";

    $stmt = $pdo->query($sql);
    $historique = $stmt->fetchAll();

    // On renvoie le tout en JSON
    echo json_encode([
        'succes' => true, 
        'historique' => $historique
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['succes' => false, 'erreur' => 'Erreur de base de données.']);
}
?>