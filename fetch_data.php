<?php
// On prévient le navigateur qu'on lui envoie du JSON (et pas une page web)
header('Content-Type: application/json; charset=utf-8');

// On appelle la connexion à la base de données
require_once 'db.php';

try {
    // 1. On compte les statistiques (Total, Présents, Absents)
    $stmt_stats = $pdo->query("SELECT COUNT(*) as total, SUM(etat = 1) as presents, SUM(etat = 0) as absents FROM outils");
    $stats = $stmt_stats->fetch();

    // 2. On récupère la liste complète des outils
    $stmt_outils = $pdo->query("SELECT id, uid_nfc, nom_outil, etat FROM outils ORDER BY nom_outil ASC");
    $outils = $stmt_outils->fetchAll();

    // 3. On nettoie la liste pour s'assurer que les chiffres soient bien des nombres entiers
    $liste_outils = [];
    foreach ($outils as $outil) {
        $liste_outils[] = [
            'id'        => (int) $outil['id'],
            'uid_nfc'   => $outil['uid_nfc'],
            'nom_outil' => $outil['nom_outil'],
            'etat'      => (int) $outil['etat']
        ];
    }

    // 4. On assemble notre réponse et on l'envoie en JSON
    echo json_encode([
        'succes'    => true,
        'timestamp' => date('d/m/Y H:i:s'),
        'stats'     => [
            'total'    => (int) ($stats['total'] ?? 0),
            'presents' => (int) ($stats['presents'] ?? 0),
            'absents'  => (int) ($stats['absents'] ?? 0),
        ],
        'outils'    => $liste_outils
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // S'il y a un plantage SQL, on renvoie une erreur propre
    http_response_code(500);
    echo json_encode([
        'succes' => false, 
        'erreur' => 'Erreur de lecture de la base de données.'
    ]);
}
?>