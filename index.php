<?php
// index.php
session_start();
require_once __DIR__ . '/bd/Connections/conn.php';

$err = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['inputUsuario']);
    $password = trim($_POST['inputPassword']);

    $sql  = "SELECT * FROM usuarios WHERE usuario = ? AND contrasena = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ss", $usuario, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Login OK → guardamos sesión y vamos al dashboard
        $_SESSION['usuario']    = $row["usuario"];
        $_SESSION['email']      = $row["email"];
        $_SESSION['nombres']    = $row["nombres"];
        $_SESSION['apellidos']  = $row["apellidos"];
        $_SESSION['utenticado'] = "SI";

        header("Location: views/dashboard.php");
        exit;
    } else {
        $err = true;
    }
    $stmt->close();
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
                    Usuario o contraseña inválida.
                </div>
            <?php endif; ?>

            <img src="imgs/login.png" alt="ICONO" class="mb-3" style="max-width:80px;">
            <h1>Inicio de Sesión</h1>

            <!-- Formulario de login -->
            <form method="POST" action="" autocomplete="off">
                <div class="mb-3">
                    <label for="inputUsuario" class="form-label">Usuario</label>
                    <input type="text" id="inputUsuario" name="inputUsuario" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="inputPassword" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input type="password" id="inputPassword" name="inputPassword" class="form-control pwd" required>
                        <button class="btn btn-outline-secondary reveal" type="button">
                            <img id="abrircerrar" src="imgs/open.png" alt="Ver/ocultar" style="width:20px;">
                        </button>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
            </form>

            <!-- Botones de navegación fuera del form -->
            <div class="d-grid gap-2 mt-3">
                <button type="button"
                        class="btn btn-link"
                        onclick="location.href='views/usuarios.php'">
                    Crear / Ver Usuarios
                </button>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/basics/scripts.php'; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const revealBtn = document.querySelector('.reveal');
        const pwdField   = document.querySelector('.pwd');
        const icon       = document.getElementById('abrircerrar');
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
