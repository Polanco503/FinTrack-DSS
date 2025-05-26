<?php
session_start();
if (empty($_SESSION['autenticado']) || $_SESSION['autenticado'] !== 'SI' || empty($_SESSION['usuario_id'])) {
    header("Location: /Catedra/auth/login.php");
    exit;
}

require_once __DIR__ . '/../bd/Connections/conn.php';
$TituloSeccion = "Panel Principal";

$mensaje = '';
$usuario_id = $_SESSION['usuario_id'];

// Defaults para formulario
$form_tipo = $_POST['tipo'] ?? 'ingreso';
$form_monto = $_POST['monto'] ?? '';
$form_descripcion = $_POST['descripcion'] ?? '';
$form_fecha = $_POST['fecha'] ?? date('Y-m-d');

// Si se mandó el formulario...
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = $form_tipo;
    $monto = floatval($form_monto);
    $descripcion = trim($form_descripcion);
    $fecha = $form_fecha;
    $hora_actual = date('H:i:s');
    $fecha_completa = $fecha . ' ' . $hora_actual;

    // Validaciones
    $hoy = date('Y-m-d');
    $maxFuture = date('Y-m-d', strtotime('+2 years'));
    $valida = true;

    if (!in_array($tipo, ['ingreso', 'gasto'])) {
        $mensaje = 'Tipo inválido.';
        $valida = false;
    } elseif (!is_numeric($monto) || $monto <= 0) {
        $mensaje = 'Monto inválido.';
        $valida = false;
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !strtotime($fecha)) {
        $mensaje = 'Fecha inválida.';
        $valida = false;
    } elseif ($fecha < date('Y-m-d', strtotime('-1 year'))) {
        $mensaje = 'No puedes ingresar movimientos de hace más de un año.';
        $valida = false;
    } elseif ($fecha > $maxFuture) {
        $mensaje = 'No puedes planear movimientos a más de 2 años en el futuro.';
        $valida = false;
    }

    if ($valida) {
        $stmt = $db->prepare("INSERT INTO finanzas (usuario_id, tipo, monto, descripcion, fecha) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Error en prepare: " . $db->error);
        }
        $stmt->bind_param("isdss", $usuario_id, $tipo, $monto, $descripcion, $fecha_completa);
        if (!$stmt->execute()) {
            die('Error al insertar: ' . $stmt->error);
        }
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF'] . "?exito=1");
        exit;
    }
}

// Consulta de movimientos del usuario logueado
$totalIngresos = 0;
$totalGastos = 0;
$balance = 0;

// Totales
$hoy = date('Y-m-d');
$stmt = $db->prepare("
    SELECT 
        SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END) as total_ingresos,
        SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END) as total_gastos
    FROM finanzas
    WHERE usuario_id = ? AND DATE(fecha) <= ?
");
$stmt->bind_param("is", $usuario_id, $hoy);

if (!$stmt) {
    die("Error en prepare: " . $db->error);
}
$stmt->bind_param("is", $usuario_id, $hoy);
$stmt->execute();
$stmt->bind_result($totalIngresos, $totalGastos);
$stmt->fetch();
$stmt->close();

$balance = $totalIngresos - $totalGastos;

if ($balance > 0) {
    $alerta_balance = "Tienes un superávit de $" . number_format($balance,2) . " 💰";
} elseif ($balance == 0) {
    $alerta_balance = "Estás en equilibrio ⚖";
} else {
    $alerta_balance = "Tienes un déficit de $" . number_format(abs($balance),2) . " 💸";
}

// Movimientos individuales
$movimientos = [];
$stmt = $db->prepare("SELECT id, tipo, monto, descripcion, fecha FROM finanzas WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 15");
if (!$stmt) {
    die("Error en prepare: " . $db->error);
}
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $movimientos[] = $row;
}
$stmt->close();
?>
<!doctype html>
<html lang="es" data-bs-theme="auto">
<head>
    <?php require_once __DIR__ . '/../basics/head.php'; ?>
</head>
<body>
    <?php require_once __DIR__ . '/../basics/menu.php'; ?>

    <div class="container mt-4">
        <h2><?php echo $TituloSeccion; ?></h2>

        <!-- Calculadora Financiera -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>Calculadora Financiera</h4>
            </div>
            <div class="card-body">
                <?php if ($mensaje): ?>
                    <div class="alert alert-<?= ($mensaje === 'Movimiento guardado correctamente.') ? 'success' : 'warning' ?>">
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo:</label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="ingreso" <?= $form_tipo === 'ingreso' ? 'selected' : '' ?>>Ingreso</option>
                            <option value="gasto" <?= $form_tipo === 'gasto' ? 'selected' : '' ?>>Gasto</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="monto" class="form-label">Monto ($):</label>
                        <input type="number" step="0.01" min="0.01" name="monto" id="monto" class="form-control" required value="<?= htmlspecialchars($form_monto) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción (opcional):</label>
                        <input type="text" name="descripcion" id="descripcion" class="form-control" value="<?= htmlspecialchars($form_descripcion) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="fecha" class="form-label">Fecha:</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" value="<?= htmlspecialchars($form_fecha) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-success mt-2">Agregar</button>
                </form>

                <hr>

                <h5>Resumen:</h5>
                <ul>
                    <li>Total Ingresos: $<?= number_format($totalIngresos, 2) ?></li>
                    <li>Total Gastos: $<?= number_format($totalGastos, 2) ?></li>
                    <li><strong>Balance: $<?= number_format($balance, 2) ?></strong></li>
                </ul>
                <div class="alert alert-info"><?= $alerta_balance ?></div>

                <hr>
                <h5>Movimientos recientes:</h5>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Monto</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimientos as $mov): ?>
                            <?php
                            $fecha_mov = substr($mov['fecha'], 0, 10); // Si tu campo tiene hora, cortamos solo la fecha
                            $hoy = date('Y-m-d');
                            $badge = '';
                            $row_class = '';
                            if ($fecha_mov > $hoy) {
                                // Futuro: Planificado, rojo
                                $badge = '<span class="badge bg-danger">Planificado</span>';
                                $row_class = 'table-danger';
                            } elseif ($fecha_mov === $hoy) {
                                // Hoy: Procesado, verde
                                $badge = '<span class="badge bg-success">Procesado</span>';
                                $row_class = 'table-success';
                            } else { // Pasado: Procesado, amarillo
                                $badge = '<span class="badge bg-warning text-dark">Procesado</span>';
                                $row_class = 'table-warning';

                            }

                                $maxDesc = 20;
                                $desc = htmlspecialchars($mov['descripcion']);
                                $shortDesc = mb_strlen($desc) > $maxDesc ? mb_substr($desc, 0, $maxDesc) . '…' : $desc;

                            ?>
                            <tr class="<?= $row_class ?>">
                                <td><?= ucfirst($mov['tipo']) ?></td>
                                <td>$<?= number_format($mov['monto'], 2) ?></td>
                                <td><?= $shortDesc ?></td>
                                <td><?= $mov['fecha'] ?> <?= $badge ?></td>
                                <td>
                                    <a href="editar.php?id=<?= $mov['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="borrar.php?id=<?= $mov['id'] ?>" class="btn btn-sm btn-outline-danger" title="Borrar" onclick="return confirm('¿Estás seguro de eliminar este movimiento?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../basics/footer.php'; ?>
    <?php require_once __DIR__ . '/../basics/scripts.php'; ?>
</body>
</html>
