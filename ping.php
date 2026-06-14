<?php
// Enregistre l'heure (Timestamp) exacte du dernier signe de vie de l'ESP32
file_put_contents(__DIR__ . '/esp_last_ping.txt', time());
echo "PONG";
?>