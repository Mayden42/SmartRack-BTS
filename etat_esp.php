<?php
header('Content-Type: application/json');
$file = __DIR__ . '/esp_last_ping.txt';
$status = "offline"; // Par défaut, on le considère déconnecté

if (file_exists($file)) {
    $last_ping = (int)file_get_contents($file);
    $current_time = time();
    
    // Si l'ESP32 a fait signe de vie il y a moins de 10 secondes, il est ONLINE
    if (($current_time - $last_ping) <= 10) {
        $status = "online";
    }
}

echo json_encode(["status" => $status]);
?>