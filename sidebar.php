<?php
// On récupère le nom de la page courante pour mettre le bon lien en surbrillance
$page = basename($_SERVER['PHP_SELF']);
?>

<style>
/* Import de la police Google Fonts — chargée une seule fois ici, utilisable partout */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400&display=swap');

/* Reset minimal pour éviter les marges par défaut du navigateur */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* Variables CSS globales — toute la charte graphique est centralisée ici.
   Changer --or change l'orange sur TOUTES les pages d'un coup. */
:root {
    --w:   #ffffff;
    --g50:  #f9f9f8;   /* fond de page */
    --g200: #e4e2de;   /* bordures */
    --g400: #9e9a93;   /* texte secondaire */
    --g600: #6b6760;   /* labels */
    --g900: #1a1917;   /* texte principal */
    --or:   #f97316;   /* orange — couleur d'accent principale */
    --orl:  #fff7ed;   /* orange clair (fonds de badges) */
    --orm:  #fed7aa;   /* orange mid (anneau de focus) */
    --ord:  #c2410c;   /* orange foncé (hover bouton) */
    --gn:   #16a34a;   /* vert (présent / succès) */
    --gnb:  #f0fdf4;
    --rd:   #dc2626;   /* rouge (erreur / déconnexion) */
    --rdb:  #fef2f2;
    --bl:   #2563eb;   /* bleu (retour outil) */
    --blb:  #eff6ff;
    --b:    1px solid var(--g200);  /* bordure standard réutilisable */
    --r:    10px;   /* rayon des cartes */
    --rs:   6px;    /* rayon des petits éléments (inputs, badges) */
}

body { font-family: 'Inter', sans-serif; background: var(--g50); color: var(--g900); min-height: 100vh; }
code { font-family: 'JetBrains Mono', monospace; }
h1   { font-size: 22px; font-weight: 600; }

/* Sidebar fixe sur toute la hauteur */
aside { position: fixed; top: 0; left: 0; height: 100%; width: 220px; background: var(--w); border-right: var(--b); display: flex; flex-direction: column; z-index: 50; }

/* Le contenu principal est décalé de la largeur de la sidebar */
main { margin-left: 220px; padding: 36px 40px; }

/* Liens de navigation */
.nav-link { display: flex; align-items: center; gap: 9px; padding: 8px 10px; border-radius: var(--rs); font-size: 13px; color: var(--g600); text-decoration: none; transition: .15s; }
.nav-link:hover { background: var(--g50); color: var(--g900); }
.nav-link.active { background: var(--orl); color: var(--or); font-weight: 500; } /* lien actif en orange */

/* Cartes blanches avec bordure fine */
.card { background: var(--w); border: var(--b); border-radius: var(--r); }

/* Tableau — styles partagés par index, historique et gestion */
table { width: 100%; border-collapse: collapse; }
th { padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: .08em; color: var(--g400); border-bottom: var(--b); }
th.tc { text-align: center; }
td { padding: 13px 24px; font-size: 13px; border-bottom: var(--b); }
tr:last-child td { border-bottom: none; }

/* Badges de statut (Présent, Absent, Retrait, Retour) */
.badge     { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 4px; font-size: 12px; font-weight: 500; }
.badge .dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.badge-green { background: var(--gnb); color: var(--gn); } .badge-green .dot { background: var(--gn); }
.badge-or    { background: var(--orl); color: var(--or); } .badge-or .dot    { background: var(--or); }
.badge-blue  { background: var(--blb); color: var(--bl); } .badge-blue .dot  { background: var(--bl); }

/* Affichage des UID NFC en police monospace */
.uid { font-family: 'JetBrains Mono', monospace; font-size: 11px; background: var(--g50); color: var(--or); padding: 3px 8px; border-radius: 4px; border: var(--b); }

/* Champs de formulaire */
label { display: block; font-size: 12px; font-weight: 500; color: var(--g600); margin-bottom: 5px; }
input[type=text], input[type=password] { width: 100%; background: var(--g50); border: var(--b); color: var(--g900); border-radius: var(--rs); padding: 9px 12px; font-size: 13px; font-family: 'Inter', sans-serif; transition: .15s; }
input:focus { outline: none; border-color: var(--or); box-shadow: 0 0 0 3px var(--orm); } /* focus orange */

/* Bouton principal */
.btn { width: 100%; background: var(--or); color: #fff; font-size: 13px; font-weight: 500; font-family: 'Inter', sans-serif; padding: 10px; border: none; border-radius: var(--rs); cursor: pointer; transition: .15s; margin-top: 6px; }
.btn:hover { background: var(--ord); }

/* Notifications de succès / erreur */
.notif     { display: flex; align-items: center; gap: 8px; border-radius: var(--rs); padding: 10px 14px; font-size: 13px; margin-bottom: 20px; }
.notif-ok  { background: var(--gnb); border: 1px solid #bbf7d0; color: var(--gn); }
.notif-err { background: var(--rdb); border: 1px solid #fecaca; color: var(--rd); }

/* En-têtes de section dans les cartes */
.section-header { padding: 16px 24px; border-bottom: var(--b); display: flex; align-items: center; justify-content: space-between; }
.page-sub { font-size: 13px; color: var(--g400); margin-top: 4px; }
.tag { font-size: 11px; color: var(--g400); background: var(--g50); padding: 4px 10px; border-radius: var(--rs); border: var(--b); }

/* Animations */
.ldot { animation: lp 2s ease-in-out infinite; }
@keyframes lp  { 0%,100% { opacity:1 } 50% { opacity:.25 } }        /* clignotement "live" */
@keyframes rf  { 0% { background: var(--orl) } 100% { background: transparent } }
.row-changed   { animation: rf 1.4s ease-out forwards; }              /* flash quand un statut change */
@keyframes ra  { from { opacity:0; transform:translateY(-4px) } to { opacity:1; transform:translateY(0) } }
.row-new       { animation: ra .3s ease-out forwards; }               /* apparition d'une nouvelle ligne */
@keyframes ri  { from { opacity:0; transform:translateX(-6px) } to { opacity:1; transform:translateX(0) } }
.row-new-h     { animation: ri .4s ease-out forwards; }               /* idem pour l'historique */
.val { transition: opacity .18s, transform .18s; }
.val.flash { opacity: .25; transform: scale(.93); }                   /* flash des compteurs */
</style>

<!-- Sidebar : navigation commune à toutes les pages -->
<aside>
    <!-- Logo SmartRack -->
    <div style="padding:18px 20px 14px; border-bottom:var(--b); display:flex; align-items:center; gap:10px;">
        <div style="width:30px; height:30px; border-radius:var(--rs); background:var(--or); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <svg width="14" height="14" fill="white" viewBox="0 0 24 24"><path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z"/></svg>
        </div>
        <div>
            <p style="font-size:14px; font-weight:600; color:var(--g900);">SmartRack</p>
            <p style="font-size:11px; color:var(--g400);">BTS CIEL</p>
        </div>
    </div>

    <!-- Liens de navigation — générés en boucle pour éviter la répétition -->
    <nav style="flex:1; padding:10px 8px; display:flex; flex-direction:column; gap:2px;">
        <?php
        $links = [
            ['index.php',      'Tableau de bord', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['historique.php', 'Historique',       'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['gestion.php',    'Inventaire',       'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['profil.php',     'Sécurité',         'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
        ];
        foreach ($links as [$href, $label, $icon]):
            $active = ($page === $href) ? 'active' : '';
        ?>
        <a href="<?= $href ?>" class="nav-link <?= $active ?>">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="<?= $icon ?>"/>
            </svg>
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- Indicateur de connexion ESP32 — mis à jour par JS toutes les 3s -->
    <div style="padding:8px 8px 4px;">
        <div style="display:flex; align-items:center; gap:8px; padding:9px 12px; background:var(--g50); border-radius:var(--rs); border:var(--b);">
            <span id="esp-dot" style="width:7px; height:7px; border-radius:50%; background:var(--g400); flex-shrink:0; display:inline-block;"></span>
            <span id="esp-text" style="font-size:12px; color:var(--g400);">Vérification…</span>
        </div>
    </div>

    <!-- Bouton de déconnexion -->
    <div style="padding:6px 8px 14px;">
        <a href="logout.php" class="nav-link"
           onmouseover="this.style.color='var(--rd)'; this.style.background='var(--rdb)';"
           onmouseout="this.style.color=''; this.style.background='';">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Déconnexion
        </a>
    </div>
</aside>

<script>
// Vérifie toutes les 3s si l'ESP32 a envoyé un ping récent (via etat_esp.php)
function checkESP() {
    fetch('etat_esp.php?v=' + Math.random())
        .then(r => r.json())
        .then(d => {
            const dot  = document.getElementById('esp-dot');
            const text = document.getElementById('esp-text');
            if (d.status === 'online') {
                dot.style.background  = 'var(--gn)';
                text.textContent      = 'ESP32 connecté';
                text.style.color      = 'var(--gn)';
            } else {
                dot.style.background  = 'var(--rd)';
                text.textContent      = 'ESP32 déconnecté';
                text.style.color      = 'var(--rd)';
            }
        })
        .catch(() => {});
}
setInterval(checkESP, 3000);
checkESP();
</script>