<?php
require_once 'db.php';

$uid = isset($_GET['uid']) ? trim($_GET['uid']) : null;

if ($uid) {
    try {
        $stmt = $pdo->prepare("SELECT id, etat, nom_outil FROM outils WHERE uid_nfc = ?");
        $stmt->execute([$uid]);
        $outil = $stmt->fetch();

        if ($outil) {
            $nouvel_etat = ($outil['etat'] == 1) ? 0 : 1;
            $action_texte = ($nouvel_etat == 0) ? "RETRAIT" : "RETOUR";

            $update = $pdo->prepare("UPDATE outils SET etat = ? WHERE uid_nfc = ?");
            $update->execute([$nouvel_etat, $uid]);

            $insert = $pdo->prepare("INSERT INTO historique (uid_nfc, action) VALUES (?, ?)");
            $insert->execute([$uid, $action_texte]);

            echo "SUCCES : " . $outil['nom_outil'];
        } else {
            // --- C'EST CETTE PARTIE QUI FAIT LA MAGIE DE LA DÉTECTION ---
            // Si le badge n'est pas dans la base de données, on le sauvegarde dans un fichier texte temporaire
            file_put_contents(__DIR__ . '/dernier_scan.txt', $uid);
            error_log("Fichier écrit : " . __DIR__ . '/dernier_scan.txt' . " | UID: " . $uid);
            echo "INCONNU : UID enregistre pour la page Gestion";
        }
    } catch (PDOException $e) {
        echo "Erreur BDD : " . $e->getMessage();
    }
} else {
    echo "L'API attend le scan d'un badge...";
}
?>