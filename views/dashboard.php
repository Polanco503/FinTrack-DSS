<?php
session_start();
if (empty($_SESSION['autenticado']) || $_SESSION['autenticado'] !== 'SI') {
    header("Location: /Catedra/auth/login.php");
    exit;
}

require_once __DIR__ . '/../bd/Connections/conn.php';
$TituloSeccion = "Panel Principal";
?>
<!doctype html>
<html lang="es">
<head>
    <?php require_once __DIR__ . '/../basics/head.php'; ?>
</head>
<body>
    <?php require_once __DIR__ . '/../basics/menu.php'; ?>

    <div class="container mt-4">
        <h2><?php echo $TituloSeccion; ?></h2>
        <!-- Aquí va el contenido principal de tu dashboard -->
        <div class="row g-4">
            <!-- Ejemplo de tarjetas -->
            <div class="col-md-4">
                <div class="card text-bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Módulo A</h5>
                        <p class="card-text">Acceso rápido a A.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Módulo B</h5>
                        <p class="card-text">Acceso rápido a B.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../basics/scripts.php'; ?>
</body>
</html>
