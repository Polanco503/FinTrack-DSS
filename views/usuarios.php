<?php
session_start();
// 2) CSRF: generar token si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../bd/Connections/conn.php';

$mensaje = '';
$old = [
    'usuario'    => '',
    'nombres'    => '',
    'apellidos'  => '',
    'email'      => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Solicitud inválida (CSRF).');
    }

    $u = trim($_POST['inputUsuario']    ?? '');
    $p = trim($_POST['inputContrasena'] ?? '');
    $n = trim($_POST['inputNombres']    ?? '');
    $a = trim($_POST['inputApellidos']  ?? '');
    $e = trim($_POST['inputEmail']      ?? '');

    $old = ['usuario'=>$u,'nombres'=>$n,'apellidos'=>$a,'email'=>$e];

    // Validaciones
    if (strlen($u) < 4 || strlen($u) > 32 || !preg_match('/^[A-Za-z0-9_]+$/', $u)) {
        $mensaje = '<div class="alert alert-warning">Usuario inválido (4–32 chars, alfanum/ _ ).</div>';
    } elseif (strlen($p) < 5) {
        $mensaje = '<div class="alert alert-warning">La contraseña debe tener al menos 5 caracteres.</div>';
    } elseif (strlen($n) < 1 || strlen($n) > 64) {
        $mensaje = '<div class="alert alert-warning">Nombres inválidos (1–64 chars).</div>';
    } elseif (strlen($a) < 1 || strlen($a) > 64) {
        $mensaje = '<div class="alert alert-warning">Apellidos inválidos (1–64 chars).</div>';
    } elseif (!filter_var($e, FILTER_VALIDATE_EMAIL) || strlen($e) > 128) {
        $mensaje = '<div class="alert alert-warning">Email inválido.</div>';
    } else {
        // Usuario existente
        $chk = $db->prepare("SELECT 1 FROM usuarios WHERE usuario = ?");
        $chk->bind_param("s", $u);
        $chk->execute();
        $res = $chk->get_result();
        $chk->close();

        if ($res->num_rows) {
            $mensaje = '<div class="alert alert-warning">El usuario ya existe.</div>';
        } else {
            // Email existente
            $chk = $db->prepare("SELECT 1 FROM usuarios WHERE email = ?");
            $chk->bind_param("s", $e);
            $chk->execute();
            $res = $chk->get_result();
            $chk->close();

            if ($res->num_rows) {
                $mensaje = '<div class="alert alert-warning">El email ya está registrado.</div>';
            } else {
                // Insertar usuario
                $hash = password_hash($p, PASSWORD_DEFAULT);
                $ins = $db->prepare("
                    INSERT INTO usuarios
                      (usuario, contrasena_hash, nombres, apellidos, email)
                    VALUES (?,?,?,?,?)
                ");
                $ins->bind_param("sssss", $u, $hash, $n, $a, $e);
                if ($ins->execute()) {
                    $mensaje = '<div class="alert alert-success">Usuario creado correctamente.</div>';
                    $old = ['usuario'=>'','nombres'=>'','apellidos'=>'','email'=>''];
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $mensaje = '<div class="alert alert-danger">Error al crear usuario: ' . htmlspecialchars($ins->error) . '</div>';
                }
                $ins->close();
            }
        }
    }
}

$TituloSeccion = "Registro de Usuario";
?>
<!doctype html>
<html lang="es" data-bs-theme="auto">
<head>
    <?php require_once __DIR__ . '/../basics/head.php'; ?>
</head>
<body>
    <?php require_once __DIR__ . '/../basics/menu.usuarios.php'; ?>

    <div class="container mt-4">
        <h2><?= htmlspecialchars($TituloSeccion, ENT_QUOTES, 'UTF-8') ?></h2>
        <?= $mensaje ?>
        <form method="POST" class="row g-2 mb-4" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="col-12 col-md-6">
                <input name="inputUsuario"
                       class="form-control"
                       placeholder="Usuario"
                       required
                       minlength="4" maxlength="32"
                       pattern="[A-Za-z0-9_]+"
                       value="<?= htmlspecialchars($old['usuario'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 col-md-6">
                <input name="inputContrasena"
                       type="password"
                       class="form-control"
                       placeholder="Contraseña"
                       required minlength="5">
            </div>
            <div class="col-12 col-md-6">
                <input name="inputNombres"
                       class="form-control"
                       placeholder="Nombres"
                       required maxlength="64"
                       value="<?= htmlspecialchars($old['nombres'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 col-md-6">
                <input name="inputApellidos"
                       class="form-control"
                       placeholder="Apellidos"
                       required maxlength="64"
                       value="<?= htmlspecialchars($old['apellidos'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <input name="inputEmail"
                       type="email"
                       class="form-control"
                       placeholder="Email"
                       required maxlength="128"
                       value="<?= htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12 text-center">
                <button class="btn btn-success mt-2">Registrarse</button>
            </div>
        </form>
    </div>
    <?php require_once __DIR__ . '/../basics/footer.php'; ?>
    <?php require_once __DIR__ . '/../basics/scripts.php'; ?>
</body>
</html>
