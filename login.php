<?php
session_start();
require_once 'db.php';

// Si l'admin est déjà connecté, on le renvoie directement au dashboard
if (isset($_SESSION['admin_connecte'])) { header('Location: index.php'); exit; }

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['identifiant'] ?? '');
    $pass = $_POST['mdp'] ?? '';

    if ($user && $pass) {
        // Requête préparée — jamais de concaténation directe avec les données utilisateur
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE identifiant = ?");
        $stmt->execute([$user]);
        $a = $stmt->fetch();

        // password_verify compare le mdp saisi avec le hash bcrypt stocké en BDD
        if ($a && password_verify($pass, $a['mot_de_passe'])) {
            $_SESSION['admin_connecte'] = true;
            $_SESSION['admin_id']       = $a['identifiant'];
            header('Location: index.php');
            exit;
        } else {
            // Message volontairement vague pour ne pas indiquer si c'est l'identifiant ou le mdp qui est faux
            $err = 'Identifiant ou mot de passe incorrect.';
        }
    } else {
        $err = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion – SmartRack</title>
    <!-- Tailwind chargé via CDN — ok pour le projet, en prod on utiliserait un build local -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* La page de login est autonome — elle n'inclut pas sidebar.php,
           donc on redéfinit juste les variables et styles nécessaires ici */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --w:   #ffffff; --g50: #f9f9f8; --g200: #e4e2de;
            --g400: #9e9a93; --g600: #6b6760; --g900: #1a1917;
            --or: #f97316; --orm: #fed7aa; --ord: #c2410c;
            --rd: #dc2626; --rdb: #fef2f2; --b: 1px solid var(--g200); --rs: 6px;
        }
        body   { font-family: 'Inter', sans-serif; background: var(--g50); color: var(--g900); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        label  { display: block; font-size: 12px; font-weight: 500; color: var(--g600); margin-bottom: 5px; }
        input  { width: 100%; background: var(--g50); border: var(--b); color: var(--g900); border-radius: var(--rs); padding: 9px 12px; font-size: 13px; font-family: 'Inter', sans-serif; transition: .15s; }
        input:focus { outline: none; border-color: var(--or); box-shadow: 0 0 0 3px var(--orm); }
        .btn   { width: 100%; background: var(--or); color: #fff; font-size: 13px; font-weight: 500; font-family: 'Inter', sans-serif; padding: 10px; border: none; border-radius: var(--rs); cursor: pointer; transition: .15s; margin-top: 6px; }
        .btn:hover { background: var(--ord); }
    </style>
</head>
<body>
    <div style="background:var(--w); border:var(--b); border-radius:10px; padding:34px 30px; width:100%; max-width:360px;">

        <!-- Logo — simple carré orange avec icône bouclier -->
        <div style="width:30px; height:30px; border-radius:var(--rs); background:var(--or); display:flex; align-items:center; justify-content:center; margin-bottom:18px;">
            <svg width="14" height="14" fill="white" viewBox="0 0 24 24">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
            </svg>
        </div>

        <p style="font-size:20px; font-weight:600; margin-bottom:4px;">SmartRack</p>
        <p style="font-size:13px; color:var(--g400); margin-bottom:24px;">Connectez-vous pour accéder au dashboard.</p>

        <!-- Erreur d'authentification -->
        <?php if ($err): ?>
            <div style="display:flex; align-items:center; gap:8px; background:var(--rdb); border:1px solid #fecaca; border-radius:var(--rs); padding:10px 14px; font-size:13px; color:var(--rd); margin-bottom:18px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="display:flex; flex-direction:column; gap:13px;">
            <div>
                <label for="id">Identifiant</label>
                <input type="text" id="id" name="identifiant" autocomplete="off" spellcheck="false">
            </div>
            <div>
                <label for="pw">Mot de passe</label>
                <input type="password" id="pw" name="mdp" autocomplete="current-password">
            </div>
            <button type="submit" class="btn">Se connecter</button>
        </form>

        <p style="margin-top:18px; font-size:11px; color:var(--g400); text-align:center;">BTS CIEL – E6</p>
    </div>
</body>
</html>