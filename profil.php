<?php
session_start();
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$notif_type = '';
$notif_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancien_mdp = $_POST['ancien_mdp'] ?? '';
    $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
    $confirmer_mdp = $_POST['confirmer_mdp'] ?? '';
    $identifiant = $_SESSION['admin_id']; // Récupéré lors du login

    if (empty($ancien_mdp) || empty($nouveau_mdp) || empty($confirmer_mdp)) {
        $notif_type = 'erreur';
        $notif_message = "Tous les champs sont obligatoires.";
    } elseif ($nouveau_mdp !== $confirmer_mdp) {
        $notif_type = 'erreur';
        $notif_message = "Les nouveaux mots de passe ne correspondent pas.";
    } elseif (strlen($nouveau_mdp) < 8) {
        $notif_type = 'erreur';
        $notif_message = "Le nouveau mot de passe doit faire au moins 8 caractères.";
    } else {
        // On vérifie l'ancien mot de passe en BDD
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE identifiant = ?");
        $stmt->execute([$identifiant]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($ancien_mdp, $admin['mot_de_passe'])) {
            // Tout est bon, on hache le nouveau mot de passe et on met à jour
            $nouveau_hache = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE identifiant = ?");
            $update->execute([$nouveau_hache, $identifiant]);

            $notif_type = 'succes';
            $notif_message = "Mot de passe modifié avec succès !";
        } else {
            $notif_type = 'erreur';
            $notif_message = "L'ancien mot de passe est incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - SmartRack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-200 min-h-screen">

    <?php require_once 'sidebar.php'; ?>

    <main class="ml-64 p-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-white">
                <i class="fas fa-user-shield text-cyan-400 mr-2"></i>
                Sécurité du compte
            </h1>
            <p class="text-slate-400 mt-1">Gestion de l'authentification et du mot de passe</p>
        </header>

        <?php if (!empty($notif_message)): ?>
            <?php if ($notif_type === 'succes'): ?>
                <div class="mb-6 flex items-center gap-3 bg-green-500/10 border border-green-500/40 text-green-300 px-5 py-4 rounded-xl">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                    <p class="font-semibold"><?= htmlspecialchars($notif_message) ?></p>
                </div>
            <?php else: ?>
                <div class="mb-6 flex items-center gap-3 bg-red-500/10 border border-red-500/40 text-red-300 px-5 py-4 rounded-xl">
                    <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                    <p class="font-semibold"><?= htmlspecialchars($notif_message) ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <section class="bg-slate-900 rounded-xl border border-slate-700 shadow-lg p-6 max-w-lg">
            <h2 class="text-xl font-bold text-white mb-6">Modifier le mot de passe</h2>
            
            <form method="POST" action="profil.php" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Ancien mot de passe</label>
                    <input type="password" name="ancien_mdp" class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-4 py-2.5 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Nouveau mot de passe (min. 8 caractères)</label>
                    <input type="password" name="nouveau_mdp" class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-4 py-2.5 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="confirmer_mdp" class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-4 py-2.5 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-bold py-3 rounded-lg shadow-lg transition-all duration-200 mt-4 flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Mettre à jour la sécurité
                </button>
            </form>
        </section>
    </main>
</body>
</html