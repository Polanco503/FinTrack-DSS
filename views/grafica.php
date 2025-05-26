<?php
session_start();
if (empty($_SESSION['autenticado']) || $_SESSION['autenticado'] !== 'SI' || empty($_SESSION['usuario_id'])) {
    header("Location: /Catedra/auth/login.php");
    exit;
}
require_once __DIR__ . '/../bd/Connections/conn.php';
$usuario_id = $_SESSION['usuario_id'];

// SOLO movimientos cuya fecha ya llegó (hoy o antes)
$stmt = $db->prepare("
    SELECT 
        SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END) as total_ingresos,
        SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END) as total_gastos
    FROM finanzas
    WHERE usuario_id = ? AND DATE(fecha) <= CURDATE()
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($totalIngresos, $totalGastos);
$stmt->fetch();
$stmt->close();
?>


<!doctype html>
<html lang="es">
<head>
    <title>Gráfica de Ingresos vs Gastos</title>
    <?php require_once __DIR__ . '/../basics/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php require_once __DIR__ . '/../basics/menu.php'; ?>
    <div class="container mt-4">
        <h2>Comparación de Ingresos vs Gastos</h2>
        <canvas id="graficaFinanzas" width="400" height="200"></canvas>
    </div>
    <?php require_once __DIR__ . '/../basics/scripts.php'; ?>
    <script>
        // Pasa los datos de PHP a JS
        const totalIngresos = <?= json_encode($totalIngresos) ?>;
        const totalGastos = <?= json_encode($totalGastos) ?>;

        // Genera la gráfica con Chart.js
        const ctx = document.getElementById('graficaFinanzas').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ingresos', 'Gastos'],
                datasets: [{
                    label: 'Monto ($)',
                    data: [totalIngresos, totalGastos],
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.7)', // Verde Bootstrap
                        'rgba(220, 53, 69, 0.7)'  // Rojo Bootstrap
                    ],
                    borderColor: [
                        'rgba(25, 135, 84, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
    <?php require_once __DIR__ . '/../basics/footer.php'; ?>
</body>
</html>
