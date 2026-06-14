<?php
require_once 'db.php';

// Indique à l'ESP32 que la réponse est au format JSON (Très important pour ton projet CIEL)
header('Content-Type: application/json');

$uid = isset($_GET['uid']) ? trim($_GET['uid']) : null;

if ($uid) {
    try {
        // 1. AJOUT DE LA COLONNE "emplacement" DANS LA REQUÊTE SELECT
        $stmt = $pdo->prepare("SELECT id, etat, nom_outil, emplacement FROM outils WHERE uid_nfc = ?");
        $stmt->execute([$uid]);
        $outil = $stmt->fetch();

        if ($outil) {
            $nouvel_etat = ($outil['etat'] == 1) ? 0 : 1;
            $action_texte = ($nouvel_etat == 0) ? "RETRAIT" : "RETOUR";

            $update = $pdo->prepare("UPDATE outils SET etat = ? WHERE uid_nfc = ?");
            $update->execute([$nouvel_etat, $uid]);

            $insert = $pdo->prepare("INSERT INTO historique (uid_nfc, action) VALUES (?, ?)");
            $insert->execute([$uid, $action_texte]);

            // 2. ENVOI DE LA RÉPONSE EN JSON (Contient l'action, le nom ET l'emplacement)
            echo json_encode([
                "status" => "SUCCES",
                "action" => $action_texte,
                "nom_outil" => $outil['nom_outil'],
                "emplacement" => $outil['emplacement'] ? $outil['emplacement'] : "Non défini"
            ]);
            
        } else {
            // Badge inconnu : on sauvegarde l'UID pour la page Gestion
            file_put_contents(__DIR__ . '/dernier_scan.txt', $uid);
            error_log("Fichier écrit : " . __DIR__ . '/dernier_scan.txt' . " | UID: " . $uid);
            
            echo json_encode([
                "status" => "INCONNU",
                "message" => "UID enregistre pour la page Gestion"
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "status" => "ERREUR",
            "message" => "Erreur BDD : " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "status" => "ERREUR",
        "message" => "L'API attend le scan d'un badge..."
    ]);
}
?>