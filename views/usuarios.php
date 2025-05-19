<?php
// usuarios.php
session_start();
if (!isset($_SESSION['utenticado']) || $_SESSION['utenticado'] !== 'SI') {
    header("Location: ../index.php");
    exit;
}

// cargamos la conexión; __DIR__ siempre apunta a .../Catedra/views
require_once __DIR__ . '/../bd/Connections/conn.php';

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['inputUsuario']);
    $p = trim($_POST['inputContrasena']);
    $n = trim($_POST['inputNombres']);
    $a = trim($_POST['inputApellidos']);
    $e = trim($_POST['inputEmail']);
    // Verificar existencia
    $chk = $db->prepare("SELECT 1 FROM usuarios WHERE usuario=?");
    $chk->bind_param("s", $u);
    $chk->execute();
    $res = $chk->get_result();
    if ($res->num_rows) {
        $mensaje = '<div class="alert alert-warning">Usuario ya existe.</div>';
    } else {
        $ins = $db->prepare("INSERT INTO usuarios (usuario,contrasena,nombres,apellidos,email) VALUES (?,?,?,?,?)");
        $ins->bind_param("sssss",$u,$p,$n,$a,$e);
        $ins->execute();
        $mensaje = '<div class="alert alert-success">Usuario creado.</div>';
        $ins->close();
    }
    $chk->close();
}

$TituloSeccion = "Gestión de Usuarios";
?>
<!doctype html>
<html lang="es">
<head>
    <?php require_once __DIR__ . '/../basics/head.php'; ?>
</head>
<body>
    <div class="container mt-4">
        <h2>Usuarios</h2>
        <?php echo $mensaje; ?>
        <form method="POST" class="row g-2 mb-4">
            <div class="col"><input name="inputUsuario" class="form-control" placeholder="Usuario" required></div>
            <div class="col"><input name="inputContrasena" class="form-control" placeholder="Contraseña" required></div>
            <div class="col"><input name="inputNombres" class="form-control" placeholder="Nombres" required></div>
            <div class="col"><input name="inputApellidos" class="form-control" placeholder="Apellidos" required></div>
            <div class="col"><input name="inputEmail" type="email" class="form-control" placeholder="Email" required></div>
            <div class="col-auto"><button class="btn btn-success">Agregar</button></div>
        </form>
        <table class="table table-striped">
            <thead><tr><th>#</th><th>Usuario</th><th>Nombre</th><th>Apellido</th><th>Email</th></tr></thead>
            <tbody>
                <?php
                $rs = $db->query("SELECT usuario, nombres, apellidos, email FROM usuarios ORDER BY usuario ASC");
                while ($r = $rs->fetch_assoc()) {
                    echo "<tr>
                    <td>{$r['usuario']}</td>
                    <td>{$r['nombres']}</td>
                    <td>{$r['apellidos']}</td>
                    <td>{$r['email']}</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php require_once __DIR__ . '/../basics/scripts.php'; ?>
</body>
</html>
