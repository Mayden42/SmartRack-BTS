<?php
session_start();
if (!isset($_SESSION['admin_connecte'])) { header('Location: login.php'); exit; }
require_once 'db.php';

$msg = ''; $type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old  = $_POST['ancien_mdp']    ?? '';
    $new  = $_POST['nouveau_mdp']   ?? '';
    $conf = $_POST['confirmer_mdp'] ?? '';

    // Validations avant toute requête BDD
    if (!$old || !$new || !$conf) {
        $msg = 'Tous les champs sont obligatoires.'; $type = 'err';
    } elseif ($new !== $conf) {
        $msg = 'Les mots de passe ne correspondent pas.'; $type = 'err';
    } elseif (strlen($new) < 8) {
        $msg = 'Minimum 8 caractères requis.'; $type = 'err';
    } else {
        // Vérification de l'ancien mot de passe hashé en BDD (password_verify = bcrypt)
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE identifiant = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $a = $stmt->fetch();

        if ($a && password_verify($old, $a['mot_de_passe'])) {
            // Hachage du nouveau mot de passe avant stockage — jamais en clair
            $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE identifiant = ?")
                ->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
            $msg = 'Mot de passe modifié avec succès.'; $type = 'ok';
        } else {
            $msg = "L'ancien mot de passe est incorrect."; $type = 'err';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sécurité – SmartRack</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<?php require_once 'sidebar.php'; ?>

<main>
    <div style="margin-bottom:28px;">
        <p style="font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--g400); margin-bottom:4px;">SmartRack</p>
        <h1>Sécurité</h1>
        <p class="page-sub">Gestion du mot de passe administrateur</p>
    </div>

    <!-- Notification après soumission du formulaire -->
    <?php if ($msg): ?>
        <div class="notif <?= $type === 'ok' ? 'notif-ok' : 'notif-err' ?>" style="max-width:400px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                <?= $type === 'ok' ? '<path d="M5 13l4 4L19 7"/>' : '<path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' ?>
            </svg>
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="card" style="padding:26px; max-width:400px;">
        <p style="font-size:14px; font-weight:500; margin-bottom:4px;">Modifier le mot de passe</p>
        <p style="font-size:12px; color:var(--g400); margin-bottom:22px;">Minimum 8 caractères. Haché en bcrypt avant stockage.</p>

        <form method="POST" style="display:flex; flex-direction:column; gap:14px;">
            <div>
                <label>Ancien mot de passe</label>
                <input type="password" name="ancien_mdp" autocomplete="current-password">
            </div>
            <!-- Séparateur visuel entre ancien et nouveau mot de passe -->
            <hr style="border:none; border-top:var(--b);">
            <div>
                <label>Nouveau mot de passe</label>
                <input type="password" name="nouveau_mdp" autocomplete="new-password">
            </div>
            <div>
                <label>Confirmer</label>
                <input type="password" name="confirmer_mdp" autocomplete="new-password">
            </div>
            <button type="submit" class="btn">Mettre à jour</button>
        </form>
    </div>
</main>
</body>
</html>