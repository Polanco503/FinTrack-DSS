<?php
session_start();
if (isset($_SESSION['utenticado']) && $_SESSION['utenticado'] === 'SI') {
    header("Location: /Catedra/views/dashboard.php");
    exit;
}
require_once('../bd/Connections/conn.php');
$err = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['inputUsuario']);
    $password = trim($_POST['inputPassword']);

    $sql = "SELECT * FROM usuarios WHERE usuario = ? AND contrasena = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ss", $usuario, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $_SESSION['usuario'] = $row["usuario"];
        $_SESSION['email'] = $row["email"];
        $_SESSION['nombres'] = $row["nombres"];
        $_SESSION['apellidos'] = $row["apellidos"];
        $_SESSION['utenticado'] = "SI";
        header("Location: /Catedra/views/dashboard.php");
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
    <?php require_once('../basics/head.php'); ?>
</head>
<body>
<div class="d-flex align-items-center justify-content-center vh-100">
    <div class="shadow-lg p-4 col-sm-5 bg-white rounded">
        <div class="text-center border border-primary rounded bg-light">
            <?php if ($err) { ?>
                <div class="alert alert-danger" role="alert">
                    Usuario o contraseña inválida.
                </div>
            <?php } ?>
            <img src="../imgs/login.png" alt="ICONO" class="mb-3" style="max-width: 100px;">
            <h1>Inicio de Sesión</h1>
            <form method="POST" action="" autocomplete="off">
    <div class="mb-3">
        <label for="inputUsuario" class="form-label">Usuario:</label>
        <input type="text" class="form-control" id="inputUsuario" name="inputUsuario" required autofocus>
    </div>
    <div class="mb-3">
        <label for="inputPassword" class="form-label">Contraseña</label>
        <div class="input-group">
            <input type="password" id="inputPassword" name="inputPassword" class="form-control pwd" required>
            <button class="btn btn-outline-secondary reveal" type="button">
                <img id="abrircerrar" src="../imgs/open.png" alt="Mostrar/ocultar" style="width:20px;">
            </button>
        </div>
    </div>
    <div class="d-grid gap-2">
        <!-- Solo el botón de submit dentro del form -->
        <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
    </div>
</form>

<!-- auth/login.php (HTML) -->
<div class="d-grid gap-2 mt-3">
  <button type="button"
          class="btn btn-secondary"
          onclick="window.location.href='/Catedra/views/dashboard.php'">
    Ir al Dashboard
  </button>
  <button type="button"
          class="btn btn-link"
          onclick="window.location.href='/Catedra/views/usuarios.php'">
    Crear Usuario
  </button>
</div>


        </div>
    </div>
</div>
<?php require_once('../basics/scripts.php'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const revealBtn = document.querySelector('.reveal');
    const pwdField = document.querySelector('.pwd');
    const icon = document.getElementById('abrircerrar');
    if (revealBtn && pwdField && icon) {
        revealBtn.addEventListener('click', function () {
            const isPassword = pwdField.getAttribute('type') === 'password';
            pwdField.setAttribute('type', isPassword ? 'text' : 'password');
            icon.setAttribute('src', isPassword ? '../imgs/close.png' : '../imgs/open.png');
        });
    }
});
</script>
</body>
</html>
