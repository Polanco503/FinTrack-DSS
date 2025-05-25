<?php
require_once('bd/Connections/conn.php'); // aquí ya se hace session_start()

// ✅ Validación de sesión, mantenla
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
        <!-- Página vacía sin contenido -->
    </div>
    <?php require_once('scripts.php'); ?>
</body>
</html>
