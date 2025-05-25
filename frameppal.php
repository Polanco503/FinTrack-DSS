<?php
require_once('bd/Connections/conn.php');

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== 'SI') {
    header("Location: index.php");
    exit;
}

$TituloSeccion = "FinTrack";
?>
<!doctype html>
<html lang="es">
<head>
    <?php require_once('head.php'); ?>
</head>
<body>
    <div class="container mt-4">
        <?php require_once('menu.php'); ?>
    </div>
    <?php require_once('scripts.php'); ?>
</body>
</html>