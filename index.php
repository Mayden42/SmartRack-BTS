<?php

session_start();
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

try {
    $stmt_stats = $pdo->query("SELECT COUNT(*) AS total, SUM(etat = 1) AS presents, SUM(etat = 0) AS absents FROM outils");
    $stats = $stmt_stats->fetch();
    $total_outils    = (int)($stats['total']    ?? 0);
    $outils_presents = (int)($stats['presents'] ?? 0);
    $outils_absents  = (int)($stats['absents']  ?? 0);

    $stmt_outils = $pdo->query("SELECT id, uid_nfc, nom_outil, etat FROM outils ORDER BY nom_outil ASC");
    $liste_outils = $stmt_outils->fetchAll();
} catch (PDOException $e) {
    die("Erreur chargement initial : " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – SmartRack | BTS CIEL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .live-value { transition: opacity 0.25s ease, transform 0.25s ease; }
        .live-value.updating { opacity: 0.25; transform: scale(0.92); }
        @keyframes live-pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
        .live-dot { animation: live-pulse 1.5s ease-in-out infinite; }
        @keyframes row-flash { 0% { background-color: rgba(6, 182, 212, 0.20); } 100% { background-color: transparent; } }
        .row-changed { animation: row-flash 1.4s ease-out forwards; }
        @keyframes row-appear { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        .row-new { animation: row-appear 0.4s ease-out forwards; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 min-h-screen">

    <?php require_once 'sidebar.php'; ?>

    <main class="ml-64 p-8">
        <header class="mb-8 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white"><i class="fas fa-tachometer-alt text-cyan-400 mr-2"></i>Tableau de bord</h1>
                <p class="text-slate-400 mt-1">Supervision en temps réel du rack connecté</p>
            </div>
            <div class="bg-slate-800 px-4 py-2 rounded-lg border border-slate-700 flex items-center gap-3">
                <span class="flex items-center gap-2">
                    <span id="live-dot" class="live-dot inline-block w-2.5 h-2.5 rounded-full bg-green-400"></span>
                    <span id="live-label" class="text-xs font-semibold text-green-400 uppercase tracking-wider">Live</span>
                </span>
                <span class="text-slate-600">|</span>
                <span>
                    <i class="fas fa-clock text-cyan-400 mr-1"></i>
                    <span id="header-time" class="text-slate-300 text-sm"><?= date('d/m/Y H:i:s') ?></span>
                </span>
            </div>
        </header>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-6 border border-slate-700 shadow-lg hover:border-cyan-500 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-sm uppercase tracking-wider">Total outils</p>
                        <p id="stat-total" class="live-value text-4xl font-bold text-white mt-2"><?= $total_outils ?></p>
                    </div>
                    <div class="bg-cyan-500/20 p-4 rounded-full"><i class="fas fa-toolbox text-cyan-400 text-2xl"></i></div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-6 border border-slate-700 shadow-lg hover:border-green-500 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-sm uppercase tracking-wider">Dans le rack</p>
                        <p id="stat-presents" class="live-value text-4xl font-bold text-green-400 mt-2"><?= $outils_presents ?></p>
                    </div>
                    <div class="bg-green-500/20 p-4 rounded-full"><i class="fas fa-check-circle text-green-400 text-2xl"></i></div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-6 border border-slate-700 shadow-lg hover:border-red-500 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-sm uppercase tracking-wider">Empruntés</p>
                        <p id="stat-absents" class="live-value text-4xl font-bold text-red-400 mt-2"><?= $outils_absents ?></p>
                    </div>
                    <div class="bg-red-500/20 p-4 rounded-full"><i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i></div>
                </div>
            </div>
        </section>

        <section class="bg-slate-900 rounded-xl border border-slate-700 shadow-lg overflow-hidden">
            <div class="p-6 border-b border-slate-700 flex items-center justify-between">
                <h2 class="text-xl font-bold text-white"><i class="fas fa-list-ul text-cyan-400 mr-2"></i>État détaillé des outils</h2>
                <span id="update-counter" class="text-xs text-slate-500 bg-slate-800 px-3 py-1 rounded-full">Initialisation...</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800 text-slate-300 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4 text-left">ID</th>
                            <th class="px-6 py-4 text-left">UID NFC</th>
                            <th class="px-6 py-4 text-left">Nom de l'outil</th>
                            <th class="px-6 py-4 text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody id="outils-tbody" class="divide-y divide-slate-800">
                        <?php if (empty($liste_outils)): ?>
                            <tr id="empty-row">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500"><i class="fas fa-info-circle mr-2"></i>Aucun outil enregistré.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($liste_outils as $outil): ?>
                                <tr class="hover:bg-slate-800/50 transition-colors" data-id="<?= (int)$outil['id'] ?>" data-etat="<?= (int)$outil['etat'] ?>">
                                    <td class="px-6 py-4 text-slate-400">#<?= htmlspecialchars($outil['id']) ?></td>
                                    <td class="px-6 py-4"><code class="bg-slate-800 px-2 py-1 rounded text-cyan-400 text-sm"><?= htmlspecialchars($outil['uid_nfc']) ?></code></td>
                                    <td class="px-6 py-4 text-white font-medium"><i class="fas fa-wrench text-slate-500 mr-2"></i><?= htmlspecialchars($outil['nom_outil']) ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($outil['etat'] == 1): ?>
                                            <span class="badge inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400 border border-green-500/30"><i class="fas fa-check-circle mr-1"></i> Présent</span>
                                        <?php else: ?>
                                            <span class="badge inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400 border border-red-500/30"><i class="fas fa-times-circle mr-1"></i> Absent</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
    (function () {
        const elTotal = document.getElementById('stat-total'), elPresents = document.getElementById('stat-presents'),
              elAbsents = document.getElementById('stat-absents'), elTbody = document.getElementById('outils-tbody'),
              elCounter = document.getElementById('update-counter'), elTime = document.getElementById('header-time'),
              elDot = document.getElementById('live-dot'), elLabel = document.getElementById('live-label');
        let cycleCount = 0;
        const etats = new Map();

        document.querySelectorAll('#outils-tbody tr[data-id]').forEach(tr => {
            etats.set(parseInt(tr.dataset.id), parseInt(tr.dataset.etat));
        });

        function echap(str) {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(String(str ?? '')));
            return d.innerHTML;
        }

        function flashWidget(el) {
            el.classList.add('updating');
            setTimeout(() => el.classList.remove('updating'), 200);
        }

        function badgeHTML(etat) {
            return etat === 1 
                ? `<span class="badge inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400 border border-green-500/30"><i class="fas fa-check-circle mr-1"></i> Présent</span>`
                : `<span class="badge inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-400 border border-red-500/30"><i class="fas fa-times-circle mr-1"></i> Absent</span>`;
        }

        function ligneHTML(outil, isNew) {
            return `<tr class="hover:bg-slate-800/50 transition-colors ${isNew ? 'row-new' : ''}" data-id="${outil.id}" data-etat="${outil.etat}">
                <td class="px-6 py-4 text-slate-400">#${outil.id}</td>
                <td class="px-6 py-4"><code class="bg-slate-800 px-2 py-1 rounded text-cyan-400 text-sm">${echap(outil.uid_nfc)}</code></td>
                <td class="px-6 py-4 text-white font-medium"><i class="fas fa-wrench text-slate-500 mr-2"></i>${echap(outil.nom_outil)}</td>
                <td class="px-6 py-4 text-center">${badgeHTML(outil.etat)}</td>
            </tr>`;
        }

        function setStatutLive(ok) {
            if (ok) {
                elDot.className = 'live-dot inline-block w-2.5 h-2.5 rounded-full bg-green-400';
                elLabel.className = 'text-xs font-semibold text-green-400 uppercase tracking-wider';
                elLabel.textContent = 'Live';
            } else {
                elDot.className = 'inline-block w-2.5 h-2.5 rounded-full bg-red-400';
                elLabel.className = 'text-xs font-semibold text-red-400 uppercase tracking-wider';
                elLabel.textContent = 'Hors ligne';
            }
        }

        function majStats(stats) {
            if (parseInt(elTotal.textContent) !== stats.total) { flashWidget(elTotal); elTotal.textContent = stats.total; }
            if (parseInt(elPresents.textContent) !== stats.presents) { flashWidget(elPresents); elPresents.textContent = stats.presents; }
            if (parseInt(elAbsents.textContent) !== stats.absents) { flashWidget(elAbsents); elAbsents.textContent = stats.absents; }
        }

        function majTableau(outils) {
            const nouvellesData = new Map();
            outils.forEach(o => nouvellesData.set(o.id, o));

            elTbody.querySelectorAll('tr[data-id]').forEach(tr => {
                const id = parseInt(tr.dataset.id);
                if (!nouvellesData.has(id)) { tr.remove(); etats.delete(id); }
            });

            if (outils.length > 0) {
                const emptyRow = document.getElementById('empty-row');
                if (emptyRow) emptyRow.remove();
            }

            outils.forEach(outil => {
                const trExistant = elTbody.querySelector(`tr[data-id="${outil.id}"]`);
                if (!trExistant) {
                    elTbody.insertAdjacentHTML('beforeend', ligneHTML(outil, true));
                    etats.set(outil.id, outil.etat);
                } else {
                    const ancienEtat = etats.get(outil.id);
                    if (ancienEtat !== undefined && ancienEtat !== outil.etat) {
                        trExistant.querySelector('td:last-child').innerHTML = badgeHTML(outil.etat);
                        trExistant.dataset.etat = outil.etat;
                        trExistant.classList.remove('row-changed');
                        void trExistant.offsetWidth; 
                        trExistant.classList.add('row-changed');
                        etats.set(outil.id, outil.etat);
                    }
                }
            });

            if (outils.length === 0 && !document.getElementById('empty-row')) {
                elTbody.innerHTML = `<tr id="empty-row"><td colspan="4" class="px-6 py-8 text-center text-slate-500"><i class="fas fa-info-circle mr-2"></i>Aucun outil enregistré.</td></tr>`;
            }
        }

        function rafraichir() {
            fetch('fetch_data.php')
                .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(data => {
                    if (!data.succes) throw new Error(data.erreur);
                    setStatutLive(true);
                    elTime.textContent = data.timestamp;
                    majStats(data.stats);
                    majTableau(data.outils);
                    cycleCount++;
                    elCounter.textContent = 'Mise à jour #' + cycleCount + ' — ' + data.timestamp;
                })
                .catch(err => {
                    setStatutLive(false);
                    elCounter.textContent = 'Reconnexion en cours...';
                });
        }

        rafraichir();
        setInterval(rafraichir, 1500);
    })();
    </script>
</body>
</html>