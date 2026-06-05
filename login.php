<?php
session_start();
require_once 'db.php';

// Si l'utilisateur est déjà connecté, on l'envoie sur le dashboard
if (isset($_SESSION['admin_connecte'])) {
    header('Location: index.php');
    exit;
}

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['identifiant'] ?? '');
    $pass = $_POST['mdp'] ?? '';

    if (!empty($user) && !empty($pass)) {
        // On cherche l'utilisateur en BDD
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE identifiant = ?");
        $stmt->execute([$user]);
        $admin = $stmt->fetch();

        // On vérifie si l'utilisateur existe ET si le mot de passe correspond au hachage
        if ($admin && password_verify($pass, $admin['mot_de_passe'])) {
            // Tout est bon, on ouvre la session !
            $_SESSION['admin_connecte'] = true;
            $_SESSION['admin_id'] = $admin['identifiant'];
            header('Location: index.php');
            exit;
        } else {
            $erreur = "Identifiant ou mot de passe incorrect.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SmartRack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-200 min-h-screen flex items-center justify-center">

    <div class="bg-slate-900 p-8 rounded-xl border border-slate-700 shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <div class="bg-gradient-to-br from-cyan-500 to-blue-600 w-16 h-16 rounded-xl mx-auto flex items-center justify-center shadow-lg mb-4">
                <i class="fas fa-lock text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">SmartRack Admin</h1>
            <p class="text-slate-400 text-sm mt-1">Veuillez vous identifier</p>
        </div>

        <?php if ($erreur): ?>
            <div class="bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm text-center">
                <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Identifiant</label>
                <input type="text" name="identifiant" autocomplete="off" class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-4 py-2.5 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Mot de passe</label>
                <input type="password" name="mdp" class="w-full bg-slate-800 border border-slate-600 text-white rounded-lg px-4 py-2.5 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-bold py-3 rounded-lg shadow-lg transition-all duration-200 mt-4">
                Se connecter
            </button>
        </form>
    </div>

</body>
</html>