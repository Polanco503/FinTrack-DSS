<?php
session_start();
if (!empty($_SESSION['autenticado']) && $_SESSION['autenticado'] === 'SI') {
    header("Location: /Catedra/views/dashboard.php");
    exit;
}

// Mensaje de error (opcional, enviado por login.php vía querystring)
$errMsg = $_GET['error'] ?? '';
$usuario = $_GET['usuario'] ?? '';
// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <title>Inicio de Sesión - Catedra</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require_once __DIR__ . '/basics/head.php'; ?>
    
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <button type="button"
    class="btn btn-outline-secondary position-absolute"
    id="btnTema"
    title="Cambiar tema"
    style="top: 2rem; right: 2rem; z-index: 99;">
    <span id="iconTema" class="bi bi-moon"></span>
</button>
    <div class="shadow-lg p-4 col-sm-5 bg-body rounded">
        <div class="text-center border rounded p-3 bg-body-secondary">
            <?php if ($errMsg): ?>
                <div class="alert alert-danger" role="alert" aria-live="polite">
                    <?= htmlspecialchars($errMsg, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <img src="imgs/login.png" alt="ICONO" class="mb-3" style="max-width:100px;">
            <h1>Inicio de Sesión</h1>

            <form method="POST" action="/Catedra/auth/login.php" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="mb-3 text-start">
                    <label for="inputUsuario" class="form-label">Usuario</label>
                    <input
                        type="text"
                        id="inputUsuario"
                        name="inputUsuario"
                        class="form-control"
                        required
                        autofocus
                        value="<?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>
                <div class="mb-3 text-start">
                    <label for="inputPassword" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input
                            type="password"
                            id="inputPassword"
                            name="inputPassword"
                            class="form-control pwd"
                            required
                            minlength="5"
                            maxlength="128"
                        >
                        <button class="btn btn-outline-secondary reveal" type="button" aria-label="Mostrar u ocultar contraseña">
                            <img id="abrircerrar" src="imgs/open.png" alt="" style="width:20px;">
                        </button>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
            </form>

            <div class="d-grid gap-2 mt-3">
                <a href="views/usuarios.php" class="btn btn-link">
                    Crear cuenta nueva
                </a>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const revealBtn = document.querySelector('.reveal'),
              pwdField  = document.querySelector('.pwd'),
              icon      = document.getElementById('abrircerrar');
        if (revealBtn && pwdField && icon) {
            revealBtn.addEventListener('click', function () {
                const isPwd = pwdField.type === 'password';
                pwdField.type = isPwd ? 'text' : 'password';
                icon.src = isPwd ? 'imgs/close.png' : 'imgs/open.png';
            });
        }
    });
    </script>
    <script src="/Catedra/js/script.js"></script>
</body>
</html>
