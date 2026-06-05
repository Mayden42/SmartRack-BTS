<?php
session_start();
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

try {
    // Chargement initial (pour ne pas avoir d'écran vide au démarrage)
    $sql = "SELECT h.id, 
                   h.uid_nfc, 
                   h.action, 
                   h.date_heure,
                   o.nom_outil
            FROM historique h
            LEFT JOIN outils o ON h.uid_nfc = o.uid_nfc
            ORDER BY h.date_heure DESC
            LIMIT 100";
    $stmt = $pdo->query($sql);
    $historique = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur : " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique - SmartRack | BTS CIEL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Animation pour les nouvelles lignes dans l'historique */
        @keyframes flash-new {
            0% { background-color: rgba(6, 182, 212, 0.4); transform: translateX(-10px); }
            100% { background-color: transparent; transform: translateX(0); }
        }
        .row-new { animation: flash-new 0.8s ease-out forwards; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 min-h-screen">

    <?php require_once 'sidebar.php'; ?>

    <main class="ml-64 p-8">

        <header class="mb-8">
            <h1 class="text-3xl font-bold text-white">
                <i class="fas fa-history text-cyan-400 mr-2"></i>
                Historique des mouvements
            </h1>
            <p class="text-slate-400 mt-1">Traçabilité complète en temps réel</p>
        </header>

        <section class="bg-slate-900 rounded-xl border border-slate-700 shadow-lg overflow-hidden">
            <div class="p-6 border-b border-slate-700 flex items-center justify-between flex-wrap gap-2">
                <h2 class="text-xl font-bold text-white">
                    <i class="fas fa-stream text-cyan-400 mr-2"></i>
                    Journal des événements
                </h2>
                <span class="flex items-center gap-2">
                    <span class="animate-ping inline-block w-2.5 h-2.5 rounded-full bg-green-400"></span>
                    <span class="text-sm font-semibold text-green-400 uppercase tracking-wider">Live</span>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800 text-slate-300 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4 text-left">#ID</th>
                            <th class="px-6 py-4 text-left">Date / Heure</th>
                            <th class="px-6 py-4 text-left">Outil</th>
                            <th class="px-6 py-4 text-left">UID NFC</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="historique-tbody" class="divide-y divide-slate-800">
                        <?php if (empty($historique)): ?>
                            <tr id="empty-row">
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                    <i class="fas fa-info-circle mr-2"></i> Aucun mouvement enregistré.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historique as $ligne): ?>
                                <tr class="hover:bg-slate-800/50 transition-colors" data-id="<?= $ligne['id'] ?>">
                                    <td class="px-6 py-4 text-slate-400">#<?= htmlspecialchars($ligne['id']) ?></td>
                                    <td class="px-6 py-4 text-slate-300">
                                        <i class="far fa-clock text-slate-500 mr-2"></i>
                                        <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($ligne['date_heure']))) ?>
                                    </td>
                                    <td class="px-6 py-4 text-white font-medium">
                                        <?php if ($ligne['nom_outil']): ?>
                                            <i class="fas fa-wrench text-slate-500 mr-2"></i><?= htmlspecialchars($ligne['nom_outil']) ?>
                                        <?php else: ?>
                                            <span class="text-slate-500 italic"><i class="fas fa-question-circle mr-1"></i> Outil supprimé</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <code class="bg-slate-800 px-2 py-1 rounded text-cyan-400 text-xs"><?= htmlspecialchars($ligne['uid_nfc']) ?></code>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php if ($ligne['action'] == 'RETRAIT'): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-500/20 text-orange-400 border border-orange-500/30">
                                                <i class="fas fa-sign-out-alt mr-1"></i> Retrait
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                                <i class="fas fa-sign-in-alt mr-1"></i> Retour
                                            </span>
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
        const tbody = document.getElementById('historique-tbody');
        let dernierIdConnu = null;

        // On cherche le plus grand ID actuel dans le tableau pour savoir où on en est
        const premiereLigne = tbody.querySelector('tr[data-id]');
        if (premiereLigne) {
            dernierIdConnu = parseInt(premiereLigne.dataset.id);
        }

        function echap(str) {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(String(str ?? '')));
            return d.innerHTML;
        }

        function construireLigne(ligne, isNew) {
            let htmlOutil = ligne.nom_outil 
                ? `<i class="fas fa-wrench text-slate-500 mr-2"></i>${echap(ligne.nom_outil)}`
                : `<span class="text-slate-500 italic"><i class="fas fa-question-circle mr-1"></i> Outil supprimé</span>`;
                
            let htmlAction = ligne.action === 'RETRAIT'
                ? `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-500/20 text-orange-400 border border-orange-500/30"><i class="fas fa-sign-out-alt mr-1"></i> Retrait</span>`
                : `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-400 border border-blue-500/30"><i class="fas fa-sign-in-alt mr-1"></i> Retour</span>`;

            let animClass = isNew ? 'row-new' : '';

            return `
                <tr class="hover:bg-slate-800/50 transition-colors ${animClass}" data-id="${ligne.id}">
                    <td class="px-6 py-4 text-slate-400">#${ligne.id}</td>
                    <td class="px-6 py-4 text-slate-300"><i class="far fa-clock text-slate-500 mr-2"></i>${echap(ligne.date_formatee)}</td>
                    <td class="px-6 py-4 text-white font-medium">${htmlOutil}</td>
                    <td class="px-6 py-4"><code class="bg-slate-800 px-2 py-1 rounded text-cyan-400 text-xs">${echap(ligne.uid_nfc)}</code></td>
                    <td class="px-6 py-4 text-center">${htmlAction}</td>
                </tr>
            `;
        }

        function actualiserHistorique() {
            fetch('fetch_historique.php')
                .then(response => response.json())
                .then(data => {
                    if (data.succes && data.historique.length > 0) {
                        const topId = parseInt(data.historique[0].id);
                        
                        // Si l'ID le plus récent de la base est différent du nôtre, on met à jour
                        if (dernierIdConnu !== topId) {
                            dernierIdConnu = topId;
                            let htmlComplet = '';
                            
                            // On reconstruit tout le tableau
                            data.historique.forEach((ligne, index) => {
                                // On ajoute l'animation "nouveau" uniquement à la toute première ligne en haut
                                let estNouveau = (index === 0);
                                htmlComplet += construireLigne(ligne, estNouveau);
                            });

                            tbody.innerHTML = htmlComplet;
                        }
                    }
                })
                .catch(err => console.error("Erreur Live Update Historique:", err));
        }

        // On lance la vérification toutes les 1.5 secondes (1500 ms)
        setInterval(actualiserHistorique, 1500);
    })();
    </script>
</body>
</html>