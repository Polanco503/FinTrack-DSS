<?php
// index.php
session_start();
require_once __DIR__ . '/bd/Connections/conn.php';

// Si ya está autenticado, vamos al dashboard
if (!empty($_SESSION['utenticado']) && $_SESSION['utenticado'] === 'SI') {
    header("Location: /Catedra/views/dashboard.php");
    exit;
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$err    = false;
$errMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Verificar token CSRF
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(400);
        die('Solicitud inválida (CSRF).');
    }

    // 2) Sanitizar y validar entradas
    $usuario  = trim($_POST['inputUsuario']  ?? '');
    $password = trim($_POST['inputPassword'] ?? '');

    if (strlen($usuario) < 4 || strlen($usuario) > 32 ||
        !preg_match('/^[A-Za-z0-9_]+$/', $usuario)) {
        $err    = true;
        $errMsg = 'Usuario inválido. 4–32 caracteres alfanuméricos o “_”.';
    }
    elseif (strlen($password) < 5 || strlen($password) > 128) {
        $err    = true;
        $errMsg = 'La contraseña debe tener entre 5 y 128 caracteres.';
    }

    // 3) Si pasa validaciones, intentamos el login
    if (! $err) {
        $sql  = "SELECT id, contrasena_hash, usuario, email, nombres, apellidos
                 FROM usuarios
                 WHERE usuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row && password_verify($password, $row['contrasena_hash'])) {
            // Éxito: creamos sesión segura
            $_SESSION['usuario']    = $row['usuario'];
            $_SESSION['email']      = $row['email']    ?? '';
            $_SESSION['nombres']    = $row['nombres']  ?? '';
            $_SESSION['apellidos']  = $row['apellidos']?? '';
            $_SESSION['utenticado'] = "SI";

            // Regenerar ID de sesión y limpiar CSRF
            session_regenerate_id(true);
            unset($_SESSION['csrf_token']);

            header("Location: /Catedra/views/dashboard.php");
            exit;
        } else {
            $err    = true;
            $errMsg = 'Usuario o contraseña incorrecta.';
        }
        $stmt->close();
    }
}

$TituloSeccion = "Inicio de Sesión";
?>
<!doctype html>
<html lang="es">
<head>
    <?php require_once __DIR__ . '/basics/head.php'; ?>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="shadow-lg p-4 col-sm-5 bg-white rounded">
        <div class="text-center border border-primary rounded bg-light p-3">
            <?php if ($err): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($errMsg, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <img src="imgs/login.png" alt="ICONO" class="mb-3" style="max-width:80px;">
            <h1>Inicio de Sesión</h1>

            <form method="POST" action="" autocomplete="off">
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
                        value="<?= isset($usuario) ? htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') : '' ?>"
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
                        <button class="btn btn-outline-secondary reveal" type="button">
                            <img id="abrircerrar" src="imgs/open.png" alt="Mostrar/ocultar" style="width:20px;">
                        </button>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
            </form>

            <div class="d-grid gap-2 mt-3">
                <button
                    type="button"
                    class="btn btn-link"
                    onclick="location.href='views/usuarios.php'"
                >
                    Crear / Ver Usuarios
                </button>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/basics/scripts.php'; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const revealBtn = document.querySelector('.reveal'),
              pwdField  = document.querySelector('.pwd'),
              icon      = document.getElementById('abrircerrar');
        if (revealBtn && pwdField && icon) {
            revealBtn.addEventListener('click', function () {
                const isPwd = pwdField.getAttribute('type') === 'password';
                pwdField.setAttribute('type', isPwd ? 'text' : 'password');
                icon.setAttribute('src', isPwd ? 'imgs/close.png' : 'imgs/open.png');
            });
        }
    });
    </script>
</body>
</html>
