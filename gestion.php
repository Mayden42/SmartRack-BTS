<?php
session_start();
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$message = '';
$type_message = '';

// --- AJOUT D'UN OUTIL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom_outil'], $_POST['uid_nfc'])) {
    $nom = trim($_POST['nom_outil']);
    $uid = trim(strtoupper($_POST['uid_nfc']));

    if (!empty($nom) && !empty($uid)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO outils (uid_nfc, nom_outil, etat) VALUES (?, ?, 1)");
            $stmt->execute([$uid, $nom]);
            $message = "L'outil '$nom' a été ajouté avec succès !";
            $type_message = "success";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Code d'erreur SQL pour "Doublon"
                $message = "Erreur : Le badge NFC '$uid' est déjà assigné.";
            } else {
                $message = "Erreur BDD : " . $e->getMessage();
            }
            $type_message = "error";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
        $type_message = "error";
    }
}

// --- SUPPRESSION D'UN OUTIL ---
if (isset($_GET['delete_id'])) {
    $id_to_delete = (int) $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM outils WHERE id = ?");
        $stmt->execute([$id_to_delete]);
        $message = "Outil supprimé de l'inventaire.";
        $type_message = "success";
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression.";
        $type_message = "error";
    }
}

// --- CHARGEMENT DE L'INVENTAIRE ---
try {
    $stmt = $pdo->query("SELECT * FROM outils ORDER BY nom_outil ASC");
    $outils = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de l'inventaire - SmartRack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-200 min-h-screen">

    <?php require_once 'sidebar.php'; ?>

    <main class="ml-64 p-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-white"><i class="fas fa-boxes text-cyan-400 mr-2"></i>Gestion de l'inventaire</h1>
            <p class="text-slate-400 mt-1">Ajouter ou supprimer des outils du rack connecté</p>
        </header>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg flex items-center gap-3 <?= $type_message === 'success' ? 'bg-green-500/10 border border-green-500/30 text-green-400' : 'bg-red-500/10 border border-red-500/30 text-red-400' ?>">
                <i class="fas <?= $type_message === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> text-xl"></i>
                <span class="font-medium"><?= htmlspecialchars($message) ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <section class="bg-slate-900 rounded-xl border border-slate-700 shadow-lg p-6">
                    <h2 class="text-xl font-bold text-white mb-6"><i class="fas fa-plus-circle text-cyan-400 mr-2"></i>Ajouter un outil</h2>
                    
                    <form method="POST" action="gestion.php" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Nom de l'outil</label>
                            <input type="text" name="nom_outil" placeholder="Ex: Perceuse Bosch" required class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-4 py-2.5 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">UID du badge NFC</label>
                            <div class="flex gap-2">
                                <input type="text" id="input_uid" name="uid_nfc" placeholder="Ex: 04697996D22A81" required class="flex-1 bg-slate-800 border border-slate-600 text-white rounded-lg px-4 py-2.5 focus:outline-none focus:border-cyan-500 uppercase">
                                
                                <button type="button" onclick="detecterBadge()" id="btn-detect" class="bg-slate-700 hover:bg-slate-600 text-cyan-400 px-4 py-2.5 rounded-lg border border-slate-600 transition-all shadow" title="Scanner un badge">
                                    <i class="fas fa-wifi"></i>
                                </button>
                            </div>
                            <p id="msg-detect" class="text-xs text-slate-500 mt-2 italic"></p>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-bold py-3 rounded-lg shadow-lg transition-all duration-200 mt-4">
                            <i class="fas fa-save mr-2"></i> Enregistrer
                        </button>
                    </form>
                </section>
            </div>

            <div class="lg:col-span-2">
                <section class="bg-slate-900 rounded-xl border border-slate-700 shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-slate-700">
                        <h2 class="text-xl font-bold text-white"><i class="fas fa-list text-cyan-400 mr-2"></i>Outils enregistrés</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-800 text-slate-300 uppercase text-xs tracking-wider">
                                <tr>
                                    <th class="px-6 py-4 text-left">Nom de l'outil</th>
                                    <th class="px-6 py-4 text-left">UID NFC</th>
                                    <th class="px-6 py-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <?php if (empty($outils)): ?>
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-slate-500"><i class="fas fa-info-circle mr-2"></i>L'inventaire est vide.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($outils as $outil): ?>
                                        <tr class="hover:bg-slate-800/50 transition-colors">
                                            <td class="px-6 py-4 text-white font-medium">
                                                <i class="fas fa-wrench text-slate-500 mr-2"></i><?= htmlspecialchars($outil['nom_outil']) ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <code class="bg-slate-800 px-2 py-1 rounded text-cyan-400 text-xs"><?= htmlspecialchars($outil['uid_nfc']) ?></code>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <a href="gestion.php?delete_id=<?= $outil['id'] ?>" 
                                                   onclick="return confirm('Voulez-vous vraiment supprimer « <?= addslashes(htmlspecialchars($outil['nom_outil'])) ?> » ?');"
                                                   class="text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 px-3 py-1.5 rounded transition-colors">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

        </div>
    </main>

    <script>
function detecterBadge() {
    const btn = document.getElementById('btn-detect');
    const input = document.getElementById('input_uid');
    const msg = document.getElementById('msg-detect');

    btn.classList.add('animate-pulse', 'bg-cyan-600', 'text-white');
    msg.textContent = "Scanner un badge inconnu...";

    const interval = setInterval(() => {
        // Le "?v=" avec un nombre aléatoire force Chrome à ignorer son cache
        fetch('get_last_scan.php?v=' + Math.random())
            .then(response => response.text())
            .then(uid => {
                let cleanUid = uid.trim();
                
                // On vérifie que c'est un vrai UID (pas du HTML, pas vide)
                if (cleanUid !== "" && !cleanUid.includes("<") && cleanUid.length > 4) {
                    input.value = cleanUid;
                    clearInterval(interval);
                    
                    btn.classList.remove('animate-pulse', 'bg-cyan-600');
                    btn.classList.add('bg-green-600');
                    msg.textContent = "✅ Badge détecté !";
                }
            });
    }, 1000);

    setTimeout(() => clearInterval(interval), 60000); // Stop après 1 min
}
</script>
</body>
</html>