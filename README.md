# 🛠️ SmartRack - Gestionnaire d'Outils Connecté

![Version](https://img.shields.io/badge/Version-1.0-blue.svg)
![Statut](https://img.shields.io/badge/Statut-Terminé-success.svg)
![Technologies](https://img.shields.io/badge/Stack-PHP%20%7C%20C++%20%7C%20JS-orange.svg)

**SmartRack** est un système IoT (Internet des Objets) développé dans le cadre du projet **BTS CIEL**. Il permet de suivre en temps réel l'emprunt et le retour des outils dans un atelier grâce à la technologie RFID/NFC, avec un tableau de bord web dynamique.

---

## 📂 Structure du dépôt

Pour une meilleure lisibilité, le projet est divisé en deux parties :

- **`/Serveur_Web`** : Contient tout le code source de l'interface web (Backend PHP, Frontend JS/Tailwind) et le fichier d'export de la base de données (`gestion_rack.sql`).
- **`/ESP32_Code`** : Contient le code source en **C++** (fichier `.ino` pour Arduino IDE) à téléverser dans le microcontrôleur ESP32.

---

## ✨ Fonctionnalités

- **🛜 Scan en temps réel** : Les ouvriers badgent un outil sur le rack (ESP32) pour l'emprunter ou le rendre.
- **📊 Tableau de bord dynamique** : Interface web mise à jour en temps réel (Fetch API) sans rechargement de page.
- **📦 Gestion d'inventaire** : Panneau d'administration sécurisé pour ajouter (avec auto-détection de l'UID du badge par l'ESP32) ou supprimer des outils.
- **📜 Historique** : Traçabilité des actions (emprunts et retours).
- **📱 Responsive Design** : Interface web adaptée aux PC, tablettes et smartphones.

---

## 🛠️ Matériel Requis (Hardware)

- 1x Carte **ESP32** (NodeMCU-32S ou DOIT DevKit V1)
- 1x Module RFID/NFC **MFRC522**
- 1x Écran **LCD 16x2 avec module I2C**
- Des **Tags NFC (NTAG213/215)** *(Prévoir des tags "Anti-métal" pour les outils métalliques type clés plates).*

---

## 🚀 Installation & Configuration

### 1. Serveur Web & Base de données (XAMPP)
1. Installez et lancez [XAMPP](https://www.apachefriends.org/fr/index.html).
2. Activez les modules **Apache** et **MySQL**.
3. Ouvrez `http://localhost/phpmyadmin`.
4. Créez une base de données nommée `gestion_rack`.
5. Importez le fichier `.sql` fourni dans le dossier du projet pour créer les tables.
6. Placez tout le dossier web dans `C:\xampp\htdocs\rack`.

### 2. Configuration de l'ESP32
1. Ouvrez le fichier Arduino (`.ino`) dans l'Arduino IDE.
2. Modifiez les identifiants Wi-Fi avec ceux de votre réseau (Box ou Partage 4G) :
   ```cpp
   const char* ssid = "VOTRE_WIFI";
<<<<<<< HEAD
   const char* password = "VOTRE_MOT_DE_PASSE";
=======
   const char* password = "VOTRE_MOT_DE_PASSE";
>>>>>>> a0761d9dd14695c4b1416a44f03c9b068fd4fdaf
