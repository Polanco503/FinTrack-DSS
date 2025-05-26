<?php
session_start();
if (empty($_SESSION['autenticado']) || $_SESSION['autenticado'] !== 'SI' || empty($_SESSION['usuario_id'])) {
    header("Location: /Catedra/auth/login.php");
    exit;
}

require_once __DIR__ . '/../bd/Connections/conn.php';

$mensaje = '';
$usuario_id = $_SESSION['usuario_id'];

// Obtener el movimiento por ID
$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID inválido.");
}

$stmt = $db->prepare("SELECT * FROM finanzas WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $id, $usuario_id);
$stmt->execute();
$mov = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mov) {
    die("Movimiento no encontrado o no autorizado.");
}

// Si se envió el formulario para actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $monto = floatval($_POST['monto']);
    $descripcion = trim($_POST['descripcion']);
    $fecha = $_POST['fecha'];

    // Aquí agregas las validaciones:
    $hoy = date('Y-m-d');
    $maxFuture = date('Y-m-d', strtotime('+2 years'));
    $minPast = date('Y-m-d', strtotime('-1 year'));
    $error = '';

    if (!in_array($tipo, ['ingreso', 'gasto'])) {
        $error = "Tipo inválido.";
    } elseif ($monto <= 0) {
        $error = "Monto inválido.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !strtotime($fecha)) {
        $error = "Fecha inválida.";
    } elseif ($fecha < $minPast) {
        $error = "No puedes ingresar movimientos de hace más de un año.";
    } elseif ($fecha > $maxFuture) {
        $error = "No puedes planear movimientos a más de 2 años en el futuro.";
    }

    if ($error) {
        // Muestra el error arriba del formulario, como prefieras
        $mensaje = '<div class="alert alert-warning">' . htmlspecialchars($error) . '</div>';
    } else {
        $stmt = $db->prepare("UPDATE finanzas SET tipo=?, monto=?, descripcion=?, fecha=? WHERE id=? AND usuario_id=?");
        $stmt->bind_param("sdssii", $tipo, $monto, $descripcion, $fecha, $id, $usuario_id);
        $stmt->execute();
        $stmt->close();

        header("Location: dashboard.php?editado=1");
        exit;
    }
}

?>
<!doctype html>
<html lang="es">
<head>
    <title>Editar movimiento</title>
    <?php require_once __DIR__ . '/../basics/head.php'; ?>
</head>
<body>
    <?php require_once __DIR__ . '/../basics/menu.php'; ?>
    <div class="container mt-4">
        <h3>Editar movimiento</h3>
       <?php if (!empty($mensaje)) echo $mensaje; ?>
        <form method="post">
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo:</label>
                <select name="tipo" id="tipo" class="form-select" required>
                    <option value="ingreso" <?= $mov['tipo'] == 'ingreso' ? 'selected' : '' ?>>Ingreso</option>
                    <option value="gasto" <?= $mov['tipo'] == 'gasto' ? 'selected' : '' ?>>Gasto</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="monto" class="form-label">Monto:</label>
                <input type="number" step="0.01" name="monto" id="monto" class="form-control" value="<?= htmlspecialchars($mov['monto']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción:</label>
                <input type="text" name="descripcion" id="descripcion" class="form-control" value="<?= htmlspecialchars($mov['descripcion']) ?>">
            </div>
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha:</label>
                <input type="date" name="fecha" id="fecha" class="form-control" value="<?= substr($mov['fecha'],0,10) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    <?php require_once __DIR__ . '/../basics/footer.php'; ?>
    <?php require_once __DIR__ . '/../basics/scripts.php'; ?>
</body>
</html>
