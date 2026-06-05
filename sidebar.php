<?php
/**
 * ============================================================
 * FICHIER : sidebar.php
 * RÔLE    : Menu de navigation latéral commun à toutes les pages
 * ============================================================
 *
 * PRINCIPE DRY :
 * Ce fichier est inclus dans index.php, historique.php et gestion.php
 * pour éviter de dupliquer le code HTML du menu.
 *
 * ASTUCE : On détecte la page courante avec basename() pour
 * mettre en surbrillance le lien actif (UX professionnelle).
 */

// Récupère le nom du fichier PHP en cours d'exécution (ex: "index.php")
$page_active = basename($_SERVER['PHP_SELF']);
?>

<!-- ===================== SIDEBAR (Menu Latéral) ===================== -->
<aside class="fixed top-0 left-0 h-full w-64 bg-slate-900 border-r border-slate-700 shadow-2xl z-50 flex flex-col">

    <!-- LOGO / HEADER de l'application -->
    <div class="p-6 border-b border-slate-700">
        <div class="flex items-center space-x-3">
            <!-- Icône stylisée du rack -->
            <div class="bg-gradient-to-br from-cyan-500 to-blue-600 p-3 rounded-lg shadow-lg">
                <i class="fas fa-toolbox text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-white font-bold text-lg leading-tight">SmartRack</h1>
                <p class="text-cyan-400 text-xs">Confort & Réseaux</p>
            </div>
        </div>
    </div>

    <!-- LIENS DE NAVIGATION -->
    <nav class="flex-1 p-4 space-y-2">

        <!-- Lien Dashboard -->
        <a href="index.php" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 
                  <?= ($page_active == 'index.php') 
                      ? 'bg-cyan-500/20 text-cyan-400 border-l-4 border-cyan-400' 
                      : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
            <i class="fas fa-tachometer-alt w-5"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Lien Historique -->
        <a href="historique.php" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 
                  <?= ($page_active == 'historique.php') 
                      ? 'bg-cyan-500/20 text-cyan-400 border-l-4 border-cyan-400' 
                      : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
            <i class="fas fa-history w-5"></i>
            <span class="font-medium">Historique</span>
        </a>

        <!-- Lien Gestion -->
        <a href="gestion.php" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 
                  <?= ($page_active == 'gestion.php') 
                      ? 'bg-cyan-500/20 text-cyan-400 border-l-4 border-cyan-400' 
                      : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
            <i class="fas fa-cogs w-5"></i>
            <span class="font-medium">Gestion</span>
        </a>

    </nav>

    <!-- FOOTER de la sidebar : Statut système -->
    <div class="p-4 border-t border-slate-700">
        <div class="bg-slate-800 rounded-lg p-3">
            <div class="flex items-center space-x-2">
                <!-- LED verte clignotante = système opérationnel -->
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-xs text-slate-300">ESP32 Connecté</span>
            </div>
            <p class="text-xs text-slate-500 mt-2">BTS CIEL - E6</p>
        </div>
    </div>
    <a href="logout.php" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all duration-200 mt-8">
            <i class="fas fa-sign-out-alt w-5"></i>
            <span class="font-medium">Déconnexion</span>
        </a>
    <a href="profil.php" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 
                  <?= ($page_active == 'profil.php') 
                      ? 'bg-cyan-500/20 text-cyan-400 border-l-4 border-cyan-400' 
                      : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?> mt-4 border-t border-slate-800 pt-6">
            <i class="fas fa-user-shield w-5"></i>
            <span class="font-medium">Sécurité</span>
        </a>    

</aside>