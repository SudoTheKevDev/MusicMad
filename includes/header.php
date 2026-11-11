<?php
// ============================================================
//  RutaPc – Header HTML
//  includes/header.php
// ============================================================
$pageTitle = $pageTitle ?? 'MusicMad';
// APP_URL ya fue definido en database.php (cargado vía auth.php)
$baseUrl = defined('APP_URL') ? APP_URL : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RutaPc – Sistema de gestión integral">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | RutaPc</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">

    <!-- Progressive Web App -->
    <link rel="manifest" href="<?= $baseUrl ?>/manifest.json">
    <meta name="theme-color" content="#3a2416">
    <link rel="apple-touch-icon" href="<?= $baseUrl ?>/img/icon-192.png">
    <script src="<?= $baseUrl ?>/js/pwa.js" defer></script>

<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%233a2416'/><text y='22' x='2' font-size='20' font-family='Georgia' font-weight='bold' fill='%23d2a679'>M</text></svg>">