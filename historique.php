<?php
session_start();
if (!isset($_SESSION['admin_connecte'])) { header('Location: login.php'); exit; }
require_once 'db.php';

// Jointure LEFT JOIN pour afficher le nom de l'outil même si l'UID suffit
// LIMIT 100 : on ne charge pas tout l'historique d'un coup pour ne pas surcharger
try {
    $hist = $pdo->query("
        SELECT h.id, h.uid_nfc, h.action, h.date_heure, o.nom_outil
        FROM historique h
        LEFT JOIN outils o ON h.uid_nfc = o.uid_nfc
        ORDER BY h.date_heure DESC
        LIMIT 100
    ")->fetchAll();
} catch (PDOException $e) { die(htmlspecialchars($e->getMessage())); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historique – SmartRack</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<?php require_once 'sidebar.php'; ?>

<main>
    <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:12px;">
        <div>
            <p style="font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--g400); margin-bottom:4px;">SmartRack</p>
            <h1>Historique</h1>
            <p class="page-sub">100 derniers mouvements</p>
        </div>
        <!-- Indicateur "En direct" -->
        <div class="card" style="display:flex; align-items:center; gap:7px; padding:8px 14px;">
            <span style="width:7px; height:7px; border-radius:50%; background:var(--gn); animation:lp 2s ease-in-out infinite; display:inline-block; flex-shrink:0;"></span>
            <span style="font-size:12px; font-weight:500; color:var(--gn);">En direct</span>
        </div>
    </div>

    <div class="card" style="overflow:hidden;">
        <div class="section-header">
            <p style="font-size:14px; font-weight:500;">Journal des événements</p>
            <span class="tag"><?= count($hist) ?> entrée<?= count($hist) > 1 ? 's' : '' ?></span>
        </div>
        <table>
            <thead>
                <tr><th>#</th><th>Date / Heure</th><th>Outil</th><th>UID NFC</th><th class="tc">Mouvement</th></tr>
            </thead>
            <tbody id="tb">
                <?php if (empty($hist)): ?>
                    <tr id="er"><td colspan="5" style="text-align:center; color:var(--g400); padding:36px;">Aucun mouvement enregistré.</td></tr>
                <?php else: foreach ($hist as $l): ?>
                    <tr data-id="<?= $l['id'] ?>">
                        <td style="color:var(--g400);"><?= (int)$l['id'] ?></td>
                        <td style="color:var(--g600); font-variant-numeric:tabular-nums;"><?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($l['date_heure']))) ?></td>
                        <td>
                            <?= $l['nom_outil']
                                ? '<span style="font-weight:500">' . htmlspecialchars($l['nom_outil']) . '</span>'
                                : '<span style="color:var(--g400); font-style:italic">Outil supprimé</span>' ?>
                        </td>
                        <td><code class="uid"><?= htmlspecialchars($l['uid_nfc']) ?></code></td>
                        <td style="text-align:center;">
                            <!-- Badge orange = retrait du rack, badge bleu = retour au rack -->
                            <span class="badge <?= $l['action'] === 'RETRAIT' ? 'badge-or' : 'badge-blue' ?>">
                                <span class="dot"></span><?= $l['action'] === 'RETRAIT' ? 'Retrait' : 'Retour' ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
(function () {
    const tb = document.getElementById('tb');
    // On retient l'ID du dernier événement connu pour détecter les nouveautés
    let lastId = tb.querySelector('tr[data-id]') ? +tb.querySelector('tr[data-id]').dataset.id : null;

    function esc(s) { const d = document.createElement('div'); d.appendChild(document.createTextNode(String(s ?? ''))); return d.innerHTML; }

    function buildRow(l, isNew) {
        const outil  = l.nom_outil
            ? `<span style="font-weight:500">${esc(l.nom_outil)}</span>`
            : `<span style="color:var(--g400); font-style:italic">Outil supprimé</span>`;
        const cls    = l.action === 'RETRAIT' ? 'badge-or' : 'badge-blue';
        const label  = l.action === 'RETRAIT' ? 'Retrait' : 'Retour';
        return `<tr class="${isNew ? 'row-new-h' : ''}" data-id="${l.id}">
            <td style="color:var(--g400)">${l.id}</td>
            <td style="color:var(--g600); font-variant-numeric:tabular-nums">${esc(l.date_formatee)}</td>
            <td>${outil}</td>
            <td><code class="uid">${esc(l.uid_nfc)}</code></td>
            <td style="text-align:center"><span class="badge ${cls}"><span class="dot"></span>${label}</span></td>
        </tr>`;
    }

    function rafraichir() {
        fetch('fetch_historique.php')
            .then(r => r.json())
            .then(d => {
                if (!d.succes || !d.historique.length) return;
                const topId = +d.historique[0].id;
                // On ne re-rend le tableau que si un nouvel événement est apparu
                if (topId !== lastId) {
                    lastId = topId;
                    tb.innerHTML = d.historique.map((l, i) => buildRow(l, i === 0)).join('');
                }
            })
            .catch(() => {});
    }

    setInterval(rafraichir, 1500);
})();
</script>
</body>
</html>