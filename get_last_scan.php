<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Content-Type: text/plain");

$fichier = __DIR__ . '/dernier_scan.txt'; // ← CORRECTION ICI

if (file_exists($fichier)) {
    $uid = trim(file_get_contents($fichier));
    if (!empty($uid) && strlen($uid) > 4) {
        echo $uid;
        file_put_contents($fichier, "");
    } else {
        echo "";
    }
} else {
    echo "";
}
?>