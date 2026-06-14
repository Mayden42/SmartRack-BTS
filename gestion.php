<?php
session_start();
if (!isset($_SESSION['admin_connecte'])) { header('Location: login.php'); exit; }
require_once 'db.php';

$msg = ''; $type = '';

// --- Ajout d'un outil (formulaire POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom_outil'], $_POST['uid_nfc'], $_POST['emplacement'])) {
    $nom = trim($_POST['nom_outil']);
    $uid = strtoupper(trim($_POST['uid_nfc']));    // UID toujours en majuscules
    $emp = trim($_POST['emplacement']);

    if ($nom && $uid && $emp) {
        try {
            // Requête préparée pour éviter les injections SQL
            $pdo->prepare("INSERT INTO outils (uid_nfc, nom_outil, emplacement, etat) VALUES (?, ?, ?, 1)")
                ->execute([$uid, $nom, $emp]);
            $msg = "Outil « $nom » ajouté avec succès.";
            $type = 'ok';
        } catch (PDOException $e) {
            // Code 23000 = violation d'unicité → l'UID est déjà utilisé
            $msg  = $e->getCode() == 23000 ? "Le badge « $uid » est déjà assigné." : "Erreur BDD : " . $e->getMessage();
            $type = 'err';
        }
    } else {
        $msg = 'Tous les champs sont obligatoires.'; $type = 'err';
    }
}

// --- Suppression d'un outil (lien GET avec delete_id) ---
if (isset($_GET['delete_id'])) {
    try {
        $pdo->prepare("DELETE FROM outils WHERE id = ?")->execute([(int)$_GET['delete_id']]);
        $msg = 'Outil supprimé.'; $type = 'ok';
    } catch (PDOException $e) { $msg = 'Erreur suppression.'; $type = 'err'; }
}

// Chargement de l'inventaire complet pour affichage
try {
    $outils = $pdo->query("SELECT * FROM outils ORDER BY nom_outil")->fetchAll();
} catch (PDOException $e) { die($e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventaire – SmartRack</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<?php require_once 'sidebar.php'; ?>

<main>
    <div style="margin-bottom:28px;">
        <p style="font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--g400); margin-bottom:4px;">SmartRack</p>
        <h1>Inventaire</h1>
        <p class="page-sub">Ajouter ou retirer des outils du rack</p>
    </div>

    <!-- Notification de retour (succès ou erreur) -->
    <?php if ($msg): ?>
        <div class="notif <?= $type === 'ok' ? 'notif-ok' : 'notif-err' ?>" style="max-width:640px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                <?= $type === 'ok' ? '<path d="M5 13l4 4L19 7"/>' : '<path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' ?>
            </svg>
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:260px 1fr; gap:20px; align-items:start;">

        <!-- Formulaire d'ajout -->
        <div class="card" style="padding:22px;">
            <p style="font-size:14px; font-weight:500; margin-bottom:16px;">Ajouter un outil</p>
            <form method="POST" style="display:flex; flex-direction:column; gap:13px;">
                <div>
                    <label>Nom de l'outil</label>
                    <input type="text" name="nom_outil" placeholder="Perceuse Bosch" required>
                </div>
                <div>
                    <label>Emplacement</label>
                    <input type="text" name="emplacement" placeholder="Tiroir A2" required>
                </div>
                <div>
                    <label>UID Badge NFC</label>
                    <div style="display:flex; gap:6px;">
                        <!-- Champ UID en monospace + orange pour le distinguer visuellement -->
                        <input type="text" id="uid" name="uid_nfc" placeholder="04697996D22A81" required
                               style="text-transform:uppercase; color:var(--or); font-family:'JetBrains Mono',monospace; font-size:12px;">
                        <!-- Bouton scan : lance la détection du prochain badge NFC inconnu -->
                        <button type="button" onclick="scan()"
                                style="padding:9px 12px; background:var(--g50); border:var(--b); border-radius:var(--rs); cursor:pointer; color:var(--g600); transition:.15s;"
                                onmouseover="this.style.borderColor='var(--or)'; this.style.color='var(--or)';"
                                onmouseout="this.style.borderColor=''; this.style.color='var(--g600)';">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                            </svg>
                        </button>
                    </div>
                    <p id="smsg" style="font-size:11px; color:var(--g400); margin-top:4px; min-height:14px;"></p>
                </div>
                <button type="submit" class="btn">Enregistrer</button>
            </form>
        </div>

        <!-- Tableau de l'inventaire existant -->
        <div class="card" style="overflow:hidden;">
            <div class="section-header">
                <p style="font-size:14px; font-weight:500;">Outils enregistrés</p>
                <span class="tag"><?= count($outils) ?> outil<?= count($outils) > 1 ? 's' : '' ?></span>
            </div>
            <table>
                <thead>
                    <tr><th>Nom</th><th>Emplacement</th><th>UID NFC</th><th class="tc">Action</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($outils)): ?>
                        <tr><td colspan="4" style="text-align:center; color:var(--g400); padding:36px;">Inventaire vide.</td></tr>
                    <?php else: foreach ($outils as $o): ?>
                        <tr>
                            <td style="font-weight:500;"><?= htmlspecialchars($o['nom_outil']) ?></td>
                            <td style="color:var(--g600);"><?= htmlspecialchars($o['emplacement'] ?? '—') ?></td>
                            <td><code class="uid"><?= htmlspecialchars($o['uid_nfc']) ?></code></td>
                            <td style="text-align:center;">
                                <!-- Lien de suppression avec confirmation JS avant d'exécuter -->
                                <a href="?delete_id=<?= $o['id'] ?>"
                                   onclick="return confirm('Supprimer « <?= addslashes(htmlspecialchars($o['nom_outil'])) ?> » ?');"
                                   style="font-size:12px; color:var(--g400); text-decoration:none; padding:4px 10px; border-radius:4px; transition:.15s;"
                                   onmouseover="this.style.color='var(--rd)'; this.style.background='var(--rdb)';"
                                   onmouseout="this.style.color='var(--g400)'; this.style.background='';">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
// Détection de badge : interroge get_last_scan.php en boucle jusqu'à trouver un UID inconnu
function scan() {
    const inp = document.getElementById('uid');
    const msg = document.getElementById('smsg');
    msg.textContent = 'En attente d\'un badge…';
    msg.style.color = 'var(--g400)';

    const iv = setInterval(() => {
        fetch('get_last_scan.php?v=' + Math.random())
            .then(r => r.text())
            .then(uid => {
                uid = uid.trim();
                if (uid && !uid.includes('<') && uid.length > 4) {
                    inp.value = uid;
                    clearInterval(iv);
                    msg.textContent = 'Badge détecté.';
                    msg.style.color = 'var(--gn)';
                }
            });
    }, 1000);

    setTimeout(() => clearInterval(iv), 60000); // timeout sécurité : arrêt après 1 min
}
</script>
</body>
</html>